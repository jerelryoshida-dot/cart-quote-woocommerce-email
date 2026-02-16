<?php
/**
 * Email Service Class
 *
 * Handles all email functionality:
 * - HTML email templates
 * - Plain text fallback
 * - Multiple recipients
 * - Attachments
 *
 * @package PLUGIN_NAMESPACE\Services
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Services;

use PLUGIN_NAMESPACE\Core\Debug_Logger;

/**
 * Class Email_Service
 */
class Email_Service
{
    /**
     * Debug logger instance
     *
     * @var Debug_Logger
     */
    private Debug_Logger $logger;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = Debug_Logger::get_instance();
    }

    /**
     * Initialize hooks
     *
     * @return void
     */
    public function init(): void
    {
        // Add HTML email filter
        add_filter('wp_mail_content_type', [$this, 'set_html_content_type']);
    }

    /**
     * Set HTML content type for emails
     *
     * @return string
     */
    public function set_html_content_type(): string
    {
        return 'text/html';
    }

    /**
     * ============================================================================
     * EMAIL SENDING
     * ============================================================================
     */

    /**
     * Send a notification email
     *
     * @param string $to Recipient email
     * @param string $subject Email subject
     * @param string $template Template name (without .php)
     * @param array<string, mixed> $data Data for template
     * @param array<string> $attachments Optional attachments
     * @return bool
     */
    public function send(
        string $to,
        string $subject,
        string $template,
        array $data = [],
        array $attachments = []
    ): bool {
        // Validate email
        if (!is_email($to)) {
            $this->logger->warning('Invalid email address', ['email' => $to]);
            return false;
        }

        // Build email body
        $body = $this->render_template($template, $data);

        if (empty($body)) {
            $this->logger->error('Failed to render email template', ['template' => $template]);
            return false;
        }

        // Build headers
        $headers = $this->build_headers();

        // Send email
        $sent = wp_mail($to, $subject, $body, $headers, $attachments);

        if ($sent) {
            $this->logger->info('Email sent successfully', [
                'to'      => $to,
                'subject' => $subject,
            ]);
        } else {
            $this->logger->error('Email send failed', [
                'to'      => $to,
                'subject' => $subject,
            ]);
        }

        return $sent;
    }

    /**
     * Send notification to admin
     *
     * @param string $subject Email subject
     * @param string $template Template name
     * @param array<string, mixed> $data Template data
     * @return bool
     */
    public function send_to_admin(string $subject, string $template, array $data = []): bool
    {
        $admin_email = get_option('admin_email');
        
        if (empty($admin_email)) {
            $this->logger->warning('No admin email configured');
            return false;
        }

        return $this->send($admin_email, $subject, $template, $data);
    }

    /**
     * Send notification to multiple recipients
     *
     * @param array<string> $recipients Email addresses
     * @param string $subject Email subject
     * @param string $template Template name
     * @param array<string, mixed> $data Template data
     * @return array<string, bool> Results per recipient
     */
    public function send_bulk(
        array $recipients,
        string $subject,
        string $template,
        array $data = []
    ): array {
        $results = [];

        foreach ($recipients as $email) {
            $results[$email] = $this->send($email, $subject, $template, $data);
        }

        return $results;
    }

    /**
     * ============================================================================
     * TEMPLATE RENDERING
     * ============================================================================
     */

    /**
     * Render email template
     *
     * Wraps content in email wrapper template.
     *
     * @param string $template Template name
     * @param array<string, mixed> $data Template data
     * @return string Rendered HTML
     */
    private function render_template(string $template, array $data): string
    {
        // Extract data for template
        extract($data);

        // Get email content
        ob_start();
        $template_path = PLUGIN_PREFIX_PLUGIN_DIR . 'templates/emails/' . $template . '.php';
        
        if (file_exists($template_path)) {
            include $template_path;
        }
        $content = ob_get_clean();

        // Wrap in email wrapper
        ob_start();
        $wrapper_data = array_merge($data, ['content' => $content]);
        extract($wrapper_data);
        include PLUGIN_PREFIX_PLUGIN_DIR . 'templates/emails/wrapper.php';
        
        return ob_get_clean() ?: '';
    }

    /**
     * ============================================================================
     * HEADERS
     * ============================================================================
     */

    /**
     * Build email headers
     *
     * @return array<string>
     */
    private function build_headers(): array
    {
        $headers = [];

        // From header
        $from_name = get_bloginfo('name');
        $from_email = get_option('admin_email');
        $headers[] = sprintf('From: %s <%s>', $from_name, $from_email);

        // Content type (HTML)
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        // Reply-to
        $headers[] = sprintf('Reply-To: %s', $from_email);

        return $headers;
    }

    /**
     * ============================================================================
     * HELPERS
     * ============================================================================
     */

    /**
     * Format price for display
     *
     * @param float $amount Amount
     * @return string Formatted price
     */
    public static function format_price(float $amount): string
    {
        return '$' . number_format($amount, 2);
    }

    /**
     * Format date for display
     *
     * @param string $date Date string
     * @return string Formatted date
     */
    public static function format_date(string $date): string
    {
        if (empty($date) || $date === '0000-00-00') {
            return '';
        }
        return date_i18n(get_option('date_format'), strtotime($date));
    }

    /**
     * Format time for display
     *
     * @param string $time Time string
     * @return string Formatted time
     */
    public static function format_time(string $time): string
    {
        if (empty($time)) {
            return '';
        }
        return date_i18n(get_option('time_format'), strtotime($time));
    }
}
