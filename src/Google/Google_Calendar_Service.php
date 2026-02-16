<?php
/**
 * Google Calendar Service
 *
 * @package CartQuoteWooCommerce\Google
 * @author Jerel Yoshida
 * @since 1.0.0
 */

namespace CartQuoteWooCommerce\Google;

use CartQuoteWooCommerce\Core\Debug_Logger;

class Google_Calendar_Service
{
    private $repository;

    private $logger;

    /**
     * OAuth scopes
     */
    const SCOPES = [
        'https://www.googleapis.com/auth/calendar.events',
        'https://www.googleapis.com/auth/calendar',
    ];

    /**
     * OAuth authorize URL
     */
    const AUTH_URL = 'https://accounts.google.com/o/oauth2/v2/auth';

    /**
     * OAuth token URL
     */
    const TOKEN_URL = 'https://oauth2.googleapis.com/token';

    /**
     * Calendar API base URL
     */
    const API_URL = 'https://www.googleapis.com/calendar/v3';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->repository = new \CartQuoteWooCommerce\Database\Quote_Repository();
        $this->logger = Debug_Logger::get_instance();
    }

    /**
     * Initialize service
     */
    public function init()
    {
        add_action('cart_quote_wc_refresh_google_token', [$this, 'refresh_token_cron']);
        add_action('cart_quote_auto_create_event', [$this, 'auto_create_event']);
    }

    /**
     * Check if Google Calendar is configured
     */
    public function is_configured()
    {
        $client_id = \CartQuoteWooCommerce\Admin\Settings::get_google_client_id();
        $client_secret = \CartQuoteWooCommerce\Admin\Settings::get_google_client_secret();
        return !empty($client_id) && !empty($client_secret);
    }

    /**
     * Check if connected to Google
     */
    public function is_connected()
    {
        return \CartQuoteWooCommerce\Admin\Settings::is_google_connected() 
            && !empty(\CartQuoteWooCommerce\Admin\Settings::get_google_access_token());
    }

    /**
     * Get OAuth authorization URL
     */
    public function get_auth_url()
    {
        $params = [
            'client_id' => \CartQuoteWooCommerce\Admin\Settings::get_google_client_id(),
            'redirect_uri' => $this->get_redirect_uri(),
            'response_type' => 'code',
            'scope' => implode(' ', self::SCOPES),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => wp_create_nonce('cart_quote_google_oauth'),
        ];

        return self::AUTH_URL . '?' . http_build_query($params);
    }

    /**
     * Get redirect URI for OAuth
     */
    public function get_redirect_uri()
    {
        return admin_url('admin-ajax.php?action=cart_quote_google_oauth_callback');
    }

    /**
     * Exchange authorization code for tokens
     */
    public function exchange_code($code)
    {
        $response = wp_remote_post(self::TOKEN_URL, [
            'body' => [
                'code' => $code,
                'client_id' => \CartQuoteWooCommerce\Admin\Settings::get_google_client_id(),
                'client_secret' => \CartQuoteWooCommerce\Admin\Settings::get_google_client_secret(),
                'redirect_uri' => $this->get_redirect_uri(),
                'grant_type' => 'authorization_code',
            ],
        ]);

        if (is_wp_error($response)) {
            $this->logger->error('OAuth code exchange failed: WP_Error', [
                'error_message' => $response->get_error_message(),
            ]);
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            $this->logger->error('OAuth code exchange failed: API error', [
                'error' => $body['error'],
                'error_description' => $body['error_description'] ?? 'Unknown error',
            ]);
            return false;
        }

        \CartQuoteWooCommerce\Admin\Settings::save_google_tokens($body);

        $this->logger->info('Google OAuth connected successfully');

        return $body;
    }

    /**
     * Refresh access token
     */
    public function refresh_access_token()
    {
        $refresh_token = \CartQuoteWooCommerce\Admin\Settings::get_google_refresh_token();
        
        if (empty($refresh_token)) {
            $this->logger->warning('Token refresh failed: no refresh token stored');
            return false;
        }

        $response = wp_remote_post(self::TOKEN_URL, [
            'body' => [
                'refresh_token' => $refresh_token,
                'client_id' => \CartQuoteWooCommerce\Admin\Settings::get_google_client_id(),
                'client_secret' => \CartQuoteWooCommerce\Admin\Settings::get_google_client_secret(),
                'grant_type' => 'refresh_token',
            ],
        ]);

        if (is_wp_error($response)) {
            $this->logger->error('Token refresh failed: WP_Error', [
                'error_message' => $response->get_error_message(),
            ]);
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (isset($body['error'])) {
            $this->logger->error('Token refresh failed: API error', [
                'error' => $body['error'],
                'error_description' => $body['error_description'] ?? 'Unknown error',
            ]);
            return false;
        }

        $tokens = [
            'access_token' => $body['access_token'],
            'expires_in' => $body['expires_in'],
        ];
        
        if (!empty($body['refresh_token'])) {
            $tokens['refresh_token'] = $body['refresh_token'];
        }

        \CartQuoteWooCommerce\Admin\Settings::save_google_tokens($tokens);

        $this->logger->info('Google access token refreshed successfully');

        return true;
    }

    /**
     * Cron callback for token refresh
     */
    public function refresh_token_cron()
    {
        if ($this->is_connected() && \CartQuoteWooCommerce\Admin\Settings::google_token_needs_refresh()) {
            $this->refresh_access_token();
        }
    }

    /**
     * Make authenticated API request
     */
    private function api_request($endpoint, $method = 'GET', $body = [])
    {
        if (\CartQuoteWooCommerce\Admin\Settings::google_token_needs_refresh()) {
            $this->refresh_access_token();
        }

        $access_token = \CartQuoteWooCommerce\Admin\Settings::get_google_access_token();
        
        if (empty($access_token)) {
            $this->logger->warning('API request failed: no access token available');
            return false;
        }

        $url = self::API_URL . $endpoint;
        
        $args = [
            'method' => $method,
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ],
        ];

        if (!empty($body) && in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $this->logger->error('Google API request failed: WP_Error', [
                'endpoint' => $endpoint,
                'method' => $method,
                'error_message' => $response->get_error_message(),
            ]);
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);

        if ($code >= 400) {
            $this->logger->error('Google Calendar API error', [
                'endpoint' => $endpoint,
                'method' => $method,
                'status_code' => $code,
                'error' => $response_body['error'] ?? 'Unknown error',
                'message' => $response_body['error']['message'] ?? 'No message',
            ]);
            return false;
        }

        return $response_body;
    }

    /**
     * Create a calendar event
     */
    public function create_event($quote)
    {
        if (!$this->is_connected()) {
            $this->logger->warning('Cannot create Google event: not connected');
            return false;
        }

        $calendar_id = \CartQuoteWooCommerce\Admin\Settings::get_google_calendar_id();
        $duration = \CartQuoteWooCommerce\Admin\Settings::get_meeting_duration();

        $date = isset($quote->preferred_date) ? $quote->preferred_date : date('Y-m-d');
        $time = isset($quote->preferred_time) ? $quote->preferred_time : '09:00';

        try {
            $start_datetime = new \DateTime($date . ' ' . $time, new \DateTimeZone(wp_timezone_string()));
            $end_datetime = clone $start_datetime;
            $end_datetime->add(new \DateInterval('PT' . $duration . 'M'));
        } catch (\Exception $e) {
            $this->logger->error('Failed to create DateTime for Google event', [
                'quote_id' => $quote->quote_id ?? 'unknown',
                'date' => $date,
                'time' => $time,
                'exception' => $e->getMessage(),
            ]);
            return false;
        }

        $event_data = [
            'summary' => sprintf(
                'Quote Meeting: %s (%s)',
                $quote->customer_name,
                $quote->quote_id
            ),
            'description' => $this->build_event_description($quote),
            'start' => [
                'dateTime' => $start_datetime->format('c'),
                'timeZone' => wp_timezone_string(),
            ],
            'end' => [
                'dateTime' => $end_datetime->format('c'),
                'timeZone' => wp_timezone_string(),
            ],
            'status' => 'tentative',
            'attendees' => [
                [
                    'email' => $quote->email,
                    'displayName' => $quote->customer_name,
                ],
            ],
        ];

        $response = $this->api_request(
            '/calendars/' . urlencode($calendar_id) . '/events',
            'POST',
            $event_data
        );

        if ($response && isset($response['id'])) {
            $this->repository->save_google_event($quote->id, $response['id']);
            
            $this->repository->log(
                $quote->quote_id,
                'google_event_created',
                'Google Calendar event created: ' . $response['id'],
                get_current_user_id()
            );

            $this->logger->info('Google Calendar event created', [
                'quote_id' => $quote->quote_id,
                'event_id' => $response['id'],
            ]);

            return $response;
        }

        $this->logger->error('Failed to create Google Calendar event', [
            'quote_id' => $quote->quote_id ?? 'unknown',
        ]);

        return false;
    }

    /**
     * Build event description
     */
    private function build_event_description($quote)
    {
        $description = [];
        $description[] = 'Quote ID: ' . $quote->quote_id;
        $description[] = 'Company: ' . $quote->company_name;
        $description[] = 'Phone: ' . $quote->phone;
        $description[] = '';
        $description[] = 'Contract Duration: ' . $quote->contract_duration;
        
        if (!empty($quote->cart_data) && is_array($quote->cart_data)) {
            $description[] = '';
            $description[] = 'Products/Services:';
            foreach ($quote->cart_data as $item) {
                $description[] = sprintf('- %s x %s', $item['product_name'], $item['quantity']);
            }
        }
        
        return implode("\n", $description);
    }

    /**
     * Handle create event AJAX
     */
    public function handle_create_event()
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if (!$id) {
            wp_send_json_error(['message' => __('Invalid quote ID.', 'cart-quote-woocommerce-email')]);
            return;
        }

        $quote = $this->repository->find($id);

        if (!$quote) {
            wp_send_json_error(['message' => __('Quote not found.', 'cart-quote-woocommerce-email')]);
            return;
        }

        if (!$this->is_connected()) {
            wp_send_json_error(['message' => __('Google Calendar is not connected.', 'cart-quote-woocommerce-email')]);
            return;
        }

        if ($quote->calendar_synced) {
            wp_send_json_error(['message' => __('Event already exists for this quote.', 'cart-quote-woocommerce-email')]);
            return;
        }

        $event = $this->create_event($quote);

        if ($event) {
            wp_send_json_success([
                'message' => __('Google Calendar event created successfully!', 'cart-quote-woocommerce-email'),
                'event_id' => $event['id'],
                'event_link' => isset($event['htmlLink']) ? $event['htmlLink'] : '',
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to create event.', 'cart-quote-woocommerce-email')]);
        }
    }

    /**
     * Handle create Google Meet AJAX
     */
    public function handle_create_meet()
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;

        if (!$id) {
            wp_send_json_error(['message' => __('Invalid quote ID.', 'cart-quote-woocommerce-email')]);
            return;
        }

        $quote = $this->repository->find($id);

        if (!$quote) {
            wp_send_json_error(['message' => __('Quote not found.', 'cart-quote-woocommerce-email')]);
            return;
        }

        if (!$this->is_connected()) {
            wp_send_json_error(['message' => __('Google Calendar is not connected.', 'cart-quote-woocommerce-email')]);
            return;
        }

        if (!\CartQuoteWooCommerce\Admin\Settings::is_google_meet_enabled()) {
            wp_send_json_error(['message' => __('Google Meet is not enabled in settings.', 'cart-quote-woocommerce-email')]);
            return;
        }

        if ($quote->calendar_synced) {
            wp_send_json_error(['message' => __('Event already exists for this quote.', 'cart-quote-woocommerce-email')]);
            return;
        }

        $event = $this->create_event_with_meet($quote);

        if ($event) {
            $meet_link = '';
            if (isset($event['conferenceData']['entryPoints'][0]['uri'])) {
                $meet_link = $event['conferenceData']['entryPoints'][0]['uri'];
            } elseif (isset($event['hangoutLink'])) {
                $meet_link = $event['hangoutLink'];
            }

            wp_send_json_success([
                'message' => __('Google Meet created successfully!', 'cart-quote-woocommerce-email'),
                'event_id' => $event['id'],
                'event_link' => isset($event['htmlLink']) ? $event['htmlLink'] : '',
                'meet_link' => $meet_link,
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to create Google Meet.', 'cart-quote-woocommerce-email')]);
        }
    }

    /**
     * Create a calendar event with Google Meet
     */
    public function create_event_with_meet($quote)
    {
        if (!$this->is_connected()) {
            $this->logger->warning('Cannot create Google Meet: not connected');
            return false;
        }

        $calendar_id = \CartQuoteWooCommerce\Admin\Settings::get_google_calendar_id();
        $duration = \CartQuoteWooCommerce\Admin\Settings::get_meeting_duration();

        $date = isset($quote->preferred_date) ? $quote->preferred_date : date('Y-m-d');
        $time = isset($quote->preferred_time) ? $quote->preferred_time : '09:00';

        try {
            $start_datetime = new \DateTime($date . ' ' . $time, new \DateTimeZone(wp_timezone_string()));
            $end_datetime = clone $start_datetime;
            $end_datetime->add(new \DateInterval('PT' . $duration . 'M'));
        } catch (\Exception $e) {
            $this->logger->error('Failed to create DateTime for Google Meet', [
                'quote_id' => $quote->quote_id ?? 'unknown',
                'date' => $date,
                'time' => $time,
                'exception' => $e->getMessage(),
            ]);
            return false;
        }

        $event_data = [
            'summary' => sprintf(
                'Quote Meeting: %s (%s)',
                $quote->customer_name,
                $quote->quote_id
            ),
            'description' => $this->build_event_description($quote),
            'start' => [
                'dateTime' => $start_datetime->format('c'),
                'timeZone' => wp_timezone_string(),
            ],
            'end' => [
                'dateTime' => $end_datetime->format('c'),
                'timeZone' => wp_timezone_string(),
            ],
            'status' => 'tentative',
            'attendees' => [
                [
                    'email' => $quote->email,
                    'displayName' => $quote->customer_name,
                ],
            ],
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => 'quote-' . $quote->quote_id . '-' . time(),
                    'conferenceSolutionKey' => [
                        'type' => 'hangoutsMeet',
                    ],
                ],
            ],
        ];

        $response = $this->api_request(
            '/calendars/' . urlencode($calendar_id) . '/events?conferenceDataVersion=1',
            'POST',
            $event_data
        );

        if ($response && isset($response['id'])) {
            $this->repository->save_google_event($quote->id, $response['id']);
            
            $this->repository->log(
                $quote->quote_id,
                'google_meet_created',
                'Google Meet created: ' . $response['id'],
                get_current_user_id()
            );

            $this->logger->info('Google Meet created', [
                'quote_id' => $quote->quote_id,
                'event_id' => $response['id'],
            ]);

            return $response;
        }

        $this->logger->error('Failed to create Google Meet', [
            'quote_id' => $quote->quote_id ?? 'unknown',
        ]);

        return false;
    }

    /**
     * Handle OAuth callback
     */
    public function handle_oauth_callback()
    {
        if (!isset($_GET['state']) || !wp_verify_nonce($_GET['state'], 'cart_quote_google_oauth')) {
            wp_die(__('Invalid OAuth state.', 'cart-quote-woocommerce-email'));
        }

        if (isset($_GET['error'])) {
            wp_redirect(admin_url('admin.php?page=cart-quote-google&error=' . urlencode($_GET['error'])));
            exit;
        }

        if (isset($_GET['code'])) {
            $result = $this->exchange_code(sanitize_text_field($_GET['code']));

            if ($result) {
                wp_redirect(admin_url('admin.php?page=cart-quote-google&connected=1'));
            } else {
                wp_redirect(admin_url('admin.php?page=cart-quote-google&error=token_exchange_failed'));
            }
            exit;
        }

        wp_redirect(admin_url('admin.php?page=cart-quote-google'));
        exit;
    }

    /**
     * Handle disconnect
     */
    public function handle_disconnect()
    {
        \CartQuoteWooCommerce\Admin\Settings::clear_google_tokens();
        
        wp_send_json_success([
            'message' => __('Google Calendar disconnected successfully.', 'cart-quote-woocommerce-email'),
        ]);
    }

    /**
     * Auto create event on status change
     */
    public function auto_create_event($quote)
    {
        if ($this->is_connected() && $quote->meeting_requested && !$quote->calendar_synced) {
            $this->create_event($quote);
        }
    }
}
