<?php
/**
 * Admin Settings
 *
 * Handles plugin settings and provides helper methods
 * for retrieving and validating settings.
 *
 * @package CartQuoteWooCommerce\Admin
 * @author Jerel Yoshida
 * @since 1.0.0
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Admin;

/**
 * Class Settings
 */
class Settings
{
    /**
     * Initialize settings
     *
     * @return void
     */
    public function init(): void
    {
        // Register settings (for potential REST API use)
        register_setting('cart_quote_wc_settings', 'cart_quote_wc_quote_prefix');
        register_setting('cart_quote_wc_settings', 'cart_quote_wc_quote_start_number');
        register_setting('cart_quote_wc_settings', 'cart_quote_wc_send_to_admin');
        register_setting('cart_quote_wc_settings', 'cart_quote_wc_send_to_client');
        register_setting('cart_quote_wc_settings', 'cart_quote_wc_admin_email');
        register_setting('cart_quote_wc_settings', 'cart_quote_wc_meeting_duration');
        register_setting('cart_quote_wc_settings', 'cart_quote_wc_time_slots');
    }

    /**
     * Get quote prefix
     *
     * @return string
     */
    public static function get_quote_prefix(): string
    {
        return get_option('cart_quote_wc_quote_prefix', 'Q');
    }

    /**
     * Get quote start number
     *
     * @return int
     */
    public static function get_quote_start_number(): int
    {
        return (int) get_option('cart_quote_wc_quote_start_number', '1001');
    }

    /**
     * Get admin email
     *
     * @return string
     */
    public static function get_admin_email(): string
    {
        return get_option('cart_quote_wc_admin_email', get_option('admin_email'));
    }

    /**
     * Should send email to admin?
     *
     * @return bool
     */
    public static function send_to_admin(): bool
    {
        return (bool) get_option('cart_quote_wc_send_to_admin', true);
    }

    /**
     * Should send email to client?
     *
     * @return bool
     */
    public static function send_to_client(): bool
    {
        return (bool) get_option('cart_quote_wc_send_to_client', true);
    }

    /**
     * Is PDF enabled?
     *
     * @return bool
     */
    public static function is_pdf_enabled(): bool
    {
        return (bool) get_option('cart_quote_wc_enable_pdf', false);
    }

    /**
     * Get meeting duration in minutes
     *
     * @return int
     */
    public static function get_meeting_duration(): int
    {
        return (int) get_option('cart_quote_wc_meeting_duration', '60');
    }

    /**
     * Get available time slots
     *
     * @return array
     */
    public static function get_time_slots(): array
    {
        return get_option('cart_quote_wc_time_slots', ['09:00', '11:00', '14:00', '16:00']);
    }

    /**
     * Get email subject for admin
     *
     * @return string
     */
    public static function get_email_subject_admin(): string
    {
        return get_option('cart_quote_wc_email_subject_admin', 'New Quote Submission #{quote_id}');
    }

    /**
     * Get email subject for client
     *
     * @return string
     */
    public static function get_email_subject_client(): string
    {
        return get_option('cart_quote_wc_email_subject_client', 'Thank you for your quote request #{quote_id}');
    }

    /**
     * Should auto create event?
     *
     * @return bool
     */
    public static function auto_create_event(): bool
    {
        return (bool) get_option('cart_quote_wc_auto_create_event', false);
    }

    /**
     * Get Google Client ID
     *
     * @return string
     */
    public static function get_google_client_id(): string
    {
        return get_option('cart_quote_wc_google_client_id', '');
    }

    /**
     * Get Google Client Secret
     *
     * @return string
     */
    public static function get_google_client_secret(): string
    {
        return get_option('cart_quote_wc_google_client_secret', '');
    }

    /**
     * Get Google Access Token (decrypted)
     *
     * @return string
     */
    public static function get_google_access_token(): string
    {
        $token = get_option('cart_quote_wc_google_access_token', '');
        return $token ? self::decrypt($token) : '';
    }

    /**
     * Get Google Refresh Token (decrypted)
     *
     * @return string
     */
    public static function get_google_refresh_token(): string
    {
        $token = get_option('cart_quote_wc_google_refresh_token', '');
        return $token ? self::decrypt($token) : '';
    }

    /**
     * Get Google Calendar ID
     *
     * @return string
     */
    public static function get_google_calendar_id(): string
    {
        return get_option('cart_quote_wc_google_calendar_id', 'primary');
    }

    /**
     * Is Google Calendar connected?
     *
     * @return bool
     */
    public static function is_google_connected(): bool
    {
        return (bool) get_option('cart_quote_wc_google_connected', false);
    }

    /**
     * Is Google Meet enabled?
     *
     * @return bool
     */
    public static function is_google_meet_enabled(): bool
    {
        return (bool) get_option('cart_quote_wc_enable_google_meet', false);
    }

    /**
     * Encrypt sensitive data
     *
     * @param string $data Data to encrypt
     * @return string
     */
    public static function encrypt(string $data): string
    {
        if (empty($data)) {
            return '';
        }

        $key = self::get_encryption_key();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypt sensitive data
     *
     * @param string $data Data to decrypt
     * @return string
     */
    public static function decrypt(string $data): string
    {
        if (empty($data)) {
            return '';
        }

        $key = self::get_encryption_key();
        list($encrypted, $iv) = explode('::', base64_decode($data), 2);
        
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv);
    }

    /**
     * Get encryption key
     *
     * @return string
     */
    private static function get_encryption_key(): string
    {
        $key = get_option('cart_quote_wc_encryption_key');
        
        if (!$key) {
            // Generate a new key if not exists
            $key = wp_generate_password(32, true, true);
            update_option('cart_quote_wc_encryption_key', $key);
        }
        
        return hash('sha256', $key, true);
    }

    /**
     * Save Google OAuth tokens
     *
     * @param array $tokens Token data
     * @return void
     */
    public static function save_google_tokens(array $tokens): void
    {
        if (!empty($tokens['access_token'])) {
            update_option('cart_quote_wc_google_access_token', self::encrypt($tokens['access_token']));
        }
        if (!empty($tokens['refresh_token'])) {
            update_option('cart_quote_wc_google_refresh_token', self::encrypt($tokens['refresh_token']));
        }
        if (!empty($tokens['expires_in'])) {
            update_option('cart_quote_wc_google_token_expires', time() + $tokens['expires_in']);
        }
        update_option('cart_quote_wc_google_connected', true);
    }

    /**
     * Clear Google OAuth tokens
     *
     * @return void
     */
    public static function clear_google_tokens(): void
    {
        delete_option('cart_quote_wc_google_access_token');
        delete_option('cart_quote_wc_google_refresh_token');
        delete_option('cart_quote_wc_google_token_expires');
        update_option('cart_quote_wc_google_connected', false);
    }

    /**
     * Check if Google token needs refresh
     *
     * @return bool
     */
    public static function google_token_needs_refresh(): bool
    {
        $expires = (int) get_option('cart_quote_wc_google_token_expires', 0);
        return $expires > 0 && time() > ($expires - 300); // Refresh 5 minutes before expiry
    }
}
