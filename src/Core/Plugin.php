<?php
/**
 * Main Plugin Class - Service Container
 *
 * Implements a service container pattern for dependency injection
 * and manages all plugin services.
 *
 * @package CartQuoteWooCommerce\Core
 * @author Jerel Yoshida
 * @since 1.0.0
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Core;

/**
 * Class Plugin
 *
 * Main plugin class that acts as a service container
 */
final class Plugin
{
    /**
     * Singleton instance
     *
     * @var Plugin|null
     */
    private static $instance = null;

    /**
     * Service container
     *
     * @var array
     */
    private $services = [];

    /**
     * Plugin version
     *
     * @var string
     */
    private $version = '1.0.42';

    /**
     * Get singleton instance
     *
     * @return Plugin
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor to prevent direct instantiation
     */
    private function __construct()
    {
        // Intentionally empty - singleton pattern
    }

    /**
     * Initialize the plugin
     *
     * @return void
     */
    public function init()
    {
        // Register services
        $this->register_services();

        // Initialize services
        $this->initialize_services();

        // Hook into WordPress
        $this->setup_hooks();
    }

    /**
     * Register all plugin services
     *
     * @return void
     */
    private function register_services()
    {
        $rate_limiter = \CartQuoteWooCommerce\Core\Rate_Limiter::get_instance();
        $rate_limiter->init();

        $this->services = [
            'logger' => Debug_Logger::get_instance(),
            'rate_limiter' => $rate_limiter,
            'settings' => new \CartQuoteWooCommerce\Admin\Settings(),
            'repository' => new \CartQuoteWooCommerce\Database\Quote_Repository(),
            'google_calendar' => new \CartQuoteWooCommerce\Google\Google_Calendar_Service(),
            'email_service' => new \CartQuoteWooCommerce\Emails\Email_Service(),
            'checkout_replacement' => new \CartQuoteWooCommerce\WooCommerce\Checkout_Replacement(),
            'admin_manager' => new \CartQuoteWooCommerce\Admin\Admin_Manager(),
            'frontend_manager' => new \CartQuoteWooCommerce\Frontend\Frontend_Manager(),
        ];
    }

    /**
     * Initialize all services
     *
     * @return void
     */
    private function initialize_services()
    {
        foreach ($this->services as $name => $service) {
            if (method_exists($service, 'init')) {
                $service->init();
            }
        }
    }

    /**
     * Setup WordPress hooks
     *
     * @return void
     */
    private function setup_hooks()
    {
        // Admin redirect after activation
        add_action('admin_init', [$this, 'activation_redirect']);

        // Enqueue admin scripts and styles
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // Enqueue frontend scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);

        // AJAX handlers
        add_action('wp_ajax_cart_quote_submit', [$this, 'handle_quote_submission']);
        add_action('wp_ajax_nopriv_cart_quote_submit', [$this, 'handle_quote_submission']);
        add_action('wp_ajax_cart_quote_update_cart', [$this, 'handle_cart_update']);
        add_action('wp_ajax_nopriv_cart_quote_update_cart', [$this, 'handle_cart_update']);
        add_action('wp_ajax_cart_quote_remove_item', [$this, 'handle_remove_item']);
        add_action('wp_ajax_nopriv_cart_quote_remove_item', [$this, 'handle_remove_item']);
        add_action('wp_ajax_cart_quote_get_cart', [$this, 'handle_get_cart']);
        add_action('wp_ajax_nopriv_cart_quote_get_cart', [$this, 'handle_get_cart']);

        // Admin AJAX handlers
        add_action('wp_ajax_cart_quote_admin_update_status', [$this, 'handle_admin_update_status']);
        add_action('wp_ajax_cart_quote_admin_create_event', [$this, 'handle_admin_create_event']);
        add_action('wp_ajax_cart_quote_admin_resend_email', [$this, 'handle_admin_resend_email']);
        add_action('wp_ajax_cart_quote_admin_save_notes', [$this, 'handle_admin_save_notes']);
        add_action('wp_ajax_cart_quote_admin_update_meeting', [$this, 'handle_admin_update_meeting']);
        add_action('wp_ajax_cart_quote_admin_create_meet', [$this, 'handle_admin_create_meet']);
        add_action('wp_ajax_cart_quote_admin_export_csv', [$this, 'handle_admin_export_csv']);
        add_action('wp_ajax_cart_quote_google_oauth_callback', [$this, 'handle_google_oauth_callback']);
        add_action('wp_ajax_cart_quote_google_disconnect', [$this, 'handle_google_disconnect']);

        // WooCommerce hooks for tier data
        add_filter('woocommerce_add_cart_item_data', [$this, 'add_tier_data_to_cart'], 10, 2);
        add_filter('woocommerce_get_cart_item_from_session', [$this, 'get_cart_item_from_session'], 10, 2);

        // Body class
        add_filter('body_class', [$this, 'add_body_class']);

        // Register health check
        \CartQuoteWooCommerce\Admin\Health_Check::register_health_check();
    }

    /**
     * Redirect to settings page after activation
     *
     * @return void
     */
    public function activation_redirect()
    {
        if (get_transient('cart_quote_wc_activation_redirect')) {
            delete_transient('cart_quote_wc_activation_redirect');
            
            if (!isset($_GET['activate-multi'])) {
                wp_safe_redirect(
                    admin_url('admin.php?page=cart-quote-manager&welcome=1')
                );
                exit;
            }
        }
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_admin_assets($hook)
    {
        // Only load on plugin pages
        if (strpos($hook, 'cart-quote') === false) {
            return;
        }

        // Enqueue admin CSS if file exists
        $admin_css = CART_QUOTE_WC_PLUGIN_DIR . 'assets/css/admin.css';
        if (file_exists($admin_css)) {
            wp_enqueue_style(
                'cart-quote-admin',
                CART_QUOTE_WC_PLUGIN_URL . 'assets/css/admin.css',
                [],
                $this->version
            );
        }

        // Enqueue admin JS if file exists
        $admin_js = CART_QUOTE_WC_PLUGIN_DIR . 'assets/js/admin.js';
        if (file_exists($admin_js)) {
            wp_enqueue_script(
                'cart-quote-admin',
                CART_QUOTE_WC_PLUGIN_URL . 'assets/js/admin.js',
                ['jquery', 'jquery-ui-datepicker'],
                $this->version,
                false
            );
        }

        wp_localize_script('cart-quote-admin', 'cartQuoteAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cart_quote_admin_nonce'),
            'debug' => (defined('WP_DEBUG') && WP_DEBUG),
            'i18n' => [
                'confirmDelete' => __('Are you sure you want to delete this quote?', 'cart-quote-woocommerce-email'),
                'confirmStatusChange' => __('Are you sure you want to change the status?', 'cart-quote-woocommerce-email'),
                'confirmSaveNotes' => __('Are you sure you want to save these notes?', 'cart-quote-woocommerce-email'),
                'confirmUpdateMeeting' => __('Are you sure you want to update the meeting date/time?', 'cart-quote-woocommerce-email'),
                'confirmCreateMeet' => __('Create a Google Meet meeting for this quote?', 'cart-quote-woocommerce-email'),
                'saving' => __('Saving...', 'cart-quote-woocommerce-email'),
                'saved' => __('Saved!', 'cart-quote-woocommerce-email'),
                'error' => __('An error occurred. Please try again.', 'cart-quote-woocommerce-email'),
                'creatingEvent' => __('Creating Google Calendar event...', 'cart-quote-woocommerce-email'),
                'eventCreated' => __('Google Calendar event created successfully!', 'cart-quote-woocommerce-email'),
                'resendingEmail' => __('Resending email...', 'cart-quote-woocommerce-email'),
                'emailSent' => __('Email sent successfully!', 'cart-quote-woocommerce-email'),
            ],
        ]);

        // Enqueue WordPress datepicker styles
        wp_enqueue_style('jquery-ui-datepicker');
    }

    /**
     * Enqueue frontend assets
     *
     * @return void
     */
    public function enqueue_frontend_assets()
    {
        // Enqueue frontend CSS if file exists
        $frontend_css = CART_QUOTE_WC_PLUGIN_DIR . 'assets/css/frontend.css';
        if (file_exists($frontend_css)) {
            wp_enqueue_style(
                'cart-quote-frontend',
                CART_QUOTE_WC_PLUGIN_URL . 'assets/css/frontend.css',
                [],
                $this->version
            );
        }

        // Enqueue frontend JS if file exists
        $frontend_js = CART_QUOTE_WC_PLUGIN_DIR . 'assets/js/frontend.js';
        if (file_exists($frontend_js)) {
            wp_enqueue_script(
                'cart-quote-frontend',
                CART_QUOTE_WC_PLUGIN_URL . 'assets/js/frontend.js',
                ['jquery'],
                $this->version,
                true
            );
        }

        $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '';

        wp_localize_script('cart-quote-frontend', 'cartQuoteFrontend', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('cart_quote_frontend_nonce'),
            'cartUrl' => $cart_url,
            'debug' => defined('WP_DEBUG') && WP_DEBUG,
            'i18n' => [
                'processing' => __('Processing...', 'cart-quote-woocommerce-email'),
                'success' => __('Quote submitted successfully! We will contact you soon.', 'cart-quote-woocommerce-email'),
                'error' => __('An error occurred. Please try again.', 'cart-quote-woocommerce-email'),
                'emptyCart' => __('Your cart is empty.', 'cart-quote-woocommerce-email'),
                'requiredField' => __('This field is required.', 'cart-quote-woocommerce-email'),
                'invalidEmail' => __('Please enter a valid email address.', 'cart-quote-woocommerce-email'),
            ],
        ]);
    }

    /**
     * Add body class
     *
     * @param array $classes Existing body classes
     * @return array
     */
    public function add_body_class($classes)
    {
        $classes[] = 'cart-quote-wc-active';
        return $classes;
    }

    /**
     * Get a service from the container
     *
     * @param string $name Service name
     * @return object|null
     */
    public function get_service($name)
    {
        return $this->services[$name] ?? null;
    }

    /**
     * Handle quote submission AJAX
     *
     * @return void
     */
    public function handle_quote_submission()
    {
        check_ajax_referer('cart_quote_frontend_nonce', 'nonce');

        $checkout_replacement = $this->get_service('checkout_replacement');
        if ($checkout_replacement) {
            $checkout_replacement->handle_quote_submission();
        }
    }

    /**
     * Handle cart update AJAX
     *
     * @return void
     */
    public function handle_cart_update()
    {
        check_ajax_referer('cart_quote_frontend_nonce', 'nonce');

        $frontend = $this->get_service('frontend_manager');
        if ($frontend) {
            $frontend->handle_cart_update();
        }
    }

    /**
     * Handle remove item AJAX
     *
     * @return void
     */
    public function handle_remove_item()
    {
        check_ajax_referer('cart_quote_frontend_nonce', 'nonce');

        $frontend = $this->get_service('frontend_manager');
        if ($frontend) {
            $frontend->handle_remove_item();
        }
    }

    /**
     * Handle get cart AJAX
     *
     * @return void
     */
    public function handle_get_cart()
    {
        check_ajax_referer('cart_quote_frontend_nonce', 'nonce');

        $frontend = $this->get_service('frontend_manager');
        if ($frontend) {
            $frontend->handle_get_cart();
        }
    }

    /**
     * Handle admin update status AJAX
     *
     * @return void
     */
    public function handle_admin_update_status()
    {
        check_ajax_referer('cart_quote_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
        }

        $admin = $this->get_service('admin_manager');
        if ($admin) {
            $admin->handle_update_status();
        }
    }

    /**
     * Handle admin create event AJAX
     *
     * @return void
     */
    public function handle_admin_create_event()
    {
        check_ajax_referer('cart_quote_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
        }

        $google = $this->get_service('google_calendar');
        if ($google) {
            $google->handle_create_event();
        }
    }

    /**
     * Handle admin resend email AJAX
     *
     * @return void
     */
    public function handle_admin_resend_email()
    {
        check_ajax_referer('cart_quote_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
        }

        $email = $this->get_service('email_service');
        if ($email) {
            $email->handle_resend_email();
        }
    }

    /**
     * Handle admin save notes AJAX
     *
     * @return void
     */
    public function handle_admin_save_notes()
    {
        check_ajax_referer('cart_quote_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
        }

        $admin = $this->get_service('admin_manager');
        if ($admin) {
            $admin->handle_save_notes();
        }
    }

    /**
     * Handle admin update meeting AJAX
     *
     * @return void
     */
    public function handle_admin_update_meeting()
    {
        check_ajax_referer('cart_quote_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
        }

        $admin = $this->get_service('admin_manager');
        if ($admin) {
            $admin->handle_update_meeting();
        }
    }

    /**
     * Handle admin create Google Meet AJAX
     *
     * @return void
     */
    public function handle_admin_create_meet()
    {
        check_ajax_referer('cart_quote_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
        }

        $google = $this->get_service('google_calendar');
        if ($google) {
            $google->handle_create_meet();
        }
    }

    /**
     * Handle admin export CSV AJAX
     *
     * @return void
     */
    public function handle_admin_export_csv()
    {
        check_ajax_referer('cart_quote_admin_nonce', 'nonce');

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
        }

        $admin = $this->get_service('admin_manager');
        if ($admin) {
            $admin->handle_export_csv();
        }
    }

    /**
     * Handle Google OAuth callback
     *
     * @return void
     */
    public function handle_google_oauth_callback()
    {
        check_ajax_referer('cart_quote_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
        }

        $google = $this->get_service('google_calendar');
        if ($google) {
            $google->handle_oauth_callback();
        }
    }

    /**
     * Handle Google disconnect
     *
     * @return void
     */
    public function handle_google_disconnect()
    {
        check_ajax_referer('cart_quote_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
        }

        $google = $this->get_service('google_calendar');
        if ($google) {
            $google->handle_disconnect();
        }
    }

    /**
     * Add tier data to cart item when adding to cart
     *
     * @param array $cart_item_data Cart item data
     * @param int   $product_id     Product ID
     * @return array
     */
    public function add_tier_data_to_cart($cart_item_data, $product_id)
    {
        $tier_data = \CartQuoteWooCommerce\Services\Tier_Service::get_tier_data_for_cart($product_id);
        
        if ($tier_data) {
            $cart_item_data['tier_data'] = $tier_data;
        }
        
        return $cart_item_data;
    }

    /**
     * Restore tier data from session
     *
     * @param array $cart_item Cart item
     * @param array $values    Session values
     * @return array
     */
    public function get_cart_item_from_session($cart_item, $values)
    {
        if (isset($values['tier_data'])) {
            $cart_item['tier_data'] = $values['tier_data'];
        }
        
        return $cart_item;
    }

    /**
     * Prevent cloning
     *
     * @return void
     */
    private function __clone()
    {
        // Prevent cloning
    }

    /**
     * Prevent unserialization
     *
     * @return void
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
