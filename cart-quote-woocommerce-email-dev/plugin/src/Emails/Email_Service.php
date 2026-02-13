<?php
/**
 * Email Service
 *
 * @package CartQuoteWooCommerce\Emails
 * @author Jerel Yoshida
 * @since 1.0.0
 */

namespace CartQuoteWooCommerce\Emails;

/**
 * Class Email_Service
 */
class Email_Service
{
    /**
     * Quote repository
     *
     * @var Quote_Repository
     */
    private $repository;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->repository = new \CartQuoteWooCommerce\Database\Quote_Repository();
    }

    /**
     * Initialize service
     */
    public function init()
    {
        add_filter('cart_quote_email_headers', [$this, 'add_email_headers'], 10, 2);
    }

    /**
     * Send quote emails
     *
     * @param int $quote_id Quote database ID
     */
    public function send_quote_emails($quote_id)
    {
        $quote = $this->repository->find($quote_id);

        if (!$quote) {
            return;
        }

        if (\CartQuoteWooCommerce\Admin\Settings::send_to_admin()) {
            $this->send_admin_email($quote);
        }

        if (\CartQuoteWooCommerce\Admin\Settings::send_to_client()) {
            $this->send_client_email($quote);
        }

        $this->repository->log($quote->quote_id, 'emails_sent', 'Quote emails sent', 0);
    }

    /**
     * Send admin notification email
     *
     * @param object $quote Quote object
     * @return bool
     */
    public function send_admin_email($quote)
    {
        $to = \CartQuoteWooCommerce\Admin\Settings::get_admin_email();
        $subject = $this->parse_subject(\CartQuoteWooCommerce\Admin\Settings::get_email_subject_admin(), $quote);
        $message = $this->get_admin_email_content($quote);
        $headers = apply_filters('cart_quote_email_headers', [], 'admin');

        $sent = wp_mail($to, $subject, $message, $headers);

        if ($sent) {
            $this->repository->log($quote->quote_id, 'admin_email_sent', 'Admin notification email sent');
        }

        return $sent;
    }

    /**
     * Send client confirmation email
     *
     * @param object $quote Quote object
     * @return bool
     */
    public function send_client_email($quote)
    {
        $to = $quote->email;
        $subject = $this->parse_subject(\CartQuoteWooCommerce\Admin\Settings::get_email_subject_client(), $quote);
        $message = $this->get_client_email_content($quote);
        $headers = apply_filters('cart_quote_email_headers', [], 'client');

        $sent = wp_mail($to, $subject, $message, $headers);

        if ($sent) {
            $this->repository->log($quote->quote_id, 'client_email_sent', 'Client confirmation email sent');
        }

        return $sent;
    }

    /**
     * Parse subject placeholders
     *
     * @param string $subject Subject template
     * @param object $quote Quote object
     * @return string
     */
    private function parse_subject($subject, $quote)
    {
        $replacements = [
            '{quote_id}' => $quote->quote_id,
            '{customer_name}' => $quote->customer_name,
            '{company_name}' => $quote->company_name,
            '{status}' => $quote->status,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $subject);
    }

    /**
     * Get admin email content
     *
     * @param object $quote Quote object
     * @return string
     */
    private function get_admin_email_content($quote)
    {
        ob_start();
        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/emails/admin-notification.php';
        return $this->wrap_email_content(ob_get_clean(), __('New Quote Submission', 'cart-quote-woocommerce-email'));
    }

    /**
     * Get client email content
     *
     * @param object $quote Quote object
     * @return string
     */
    private function get_client_email_content($quote)
    {
        ob_start();
        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/emails/client-confirmation.php';
        return $this->wrap_email_content(ob_get_clean(), __('Quote Confirmation', 'cart-quote-woocommerce-email'));
    }

    /**
     * Wrap email content in template
     *
     * @param string $content Email content
     * @param string $title Email title
     * @return string
     */
    private function wrap_email_content($content, $title)
    {
        ob_start();
        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/emails/email-wrapper.php';
        return ob_get_clean();
    }

    /**
     * Add email headers
     *
     * @param array $headers Existing headers
     * @param string $type Email type
     * @return array
     */
    public function add_email_headers($headers, $type)
    {
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . get_bloginfo('name') . ' <' . \CartQuoteWooCommerce\Admin\Settings::get_admin_email() . '>';
        
        return $headers;
    }

    /**
     * Handle resend email AJAX
     */
    public function handle_resend_email()
    {
        $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
        $type = isset($_POST['email_type']) ? sanitize_text_field($_POST['email_type']) : 'both';

        if (!$id) {
            wp_send_json_error(['message' => __('Invalid quote ID.', 'cart-quote-woocommerce-email')]);
            return;
        }

        $quote = $this->repository->find($id);

        if (!$quote) {
            wp_send_json_error(['message' => __('Quote not found.', 'cart-quote-woocommerce-email')]);
            return;
        }

        $sent = [];

        if ($type === 'admin' || $type === 'both') {
            $sent['admin'] = $this->send_admin_email($quote);
        }

        if ($type === 'client' || $type === 'both') {
            $sent['client'] = $this->send_client_email($quote);
        }

        wp_send_json_success([
            'message' => __('Emails resent successfully.', 'cart-quote-woocommerce-email'),
            'sent' => $sent,
        ]);
    }

    /**
     * Format price for display
     *
     * @param float $amount Amount
     * @return string
     */
    public static function format_price($amount)
    {
        if (function_exists('wc_price')) {
            return wc_price($amount);
        }
        return number_format($amount, 2);
    }

    /**
     * Format date for display
     *
     * @param string $date Date string
     * @return string
     */
    public static function format_date($date)
    {
        if (empty($date)) {
            return '-';
        }
        return date_i18n(get_option('date_format'), strtotime($date));
    }

    /**
     * Format time for display
     *
     * @param string $time Time string
     * @return string
     */
    public static function format_time($time)
    {
        if (empty($time)) {
            return '-';
        }
        return date_i18n(get_option('time_format'), strtotime($time));
    }

    /**
     * Format contract duration for display
     *
     * @param string $duration Duration string
     * @return string
     */
    public static function format_duration($duration)
    {
        if (empty($duration)) {
            return '-';
        }

        $labels = [
            '1_month' => __('1 Month', 'cart-quote-woocommerce-email'),
            '3_months' => __('3 Months', 'cart-quote-woocommerce-email'),
            '6_months' => __('6 Months', 'cart-quote-woocommerce-email'),
        ];

        return isset($labels[$duration]) ? $labels[$duration] : $duration;
    }
}
