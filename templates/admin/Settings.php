<?php
/**
 * Settings Class
 *
 * Manages plugin settings with:
 * - Type-safe getters
 * - Default values
 * - Data encryption for sensitive values
 * - WordPress Settings API integration
 *
 * NAMING CONVENTION:
 * - Option name: plugin_slug_setting_name
 * - Getter method: get_setting_name()
 *
 * @package PLUGIN_NAMESPACE\Admin
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Admin;

/**
 * Class Settings
 */
class Settings
{
    /**
     * Initialize settings
     *
     * Registers settings with WordPress Settings API.
     * Called from Plugin::initialize_services()
     *
     * @return void
     */
    public function init(): void
    {
        // Register settings group for forms
        register_setting('plugin_slug_settings', 'plugin_slug_option_enabled');
        register_setting('plugin_slug_settings', 'plugin_slug_option_mode');
        register_setting('plugin_slug_settings', 'plugin_slug_items_per_page');
        register_setting('plugin_slug_settings', 'plugin_slug_notification_email');
    }

    /**
     * ============================================================================
     * GENERAL SETTINGS
     * ============================================================================
     */

    /**
     * Is plugin enabled?
     *
     * @return bool
     */
    public static function is_enabled(): bool
    {
        return (bool) get_option('plugin_slug_option_enabled', true);
    }

    /**
     * Get plugin mode
     *
     * @return string
     */
    public static function get_mode(): string
    {
        return get_option('plugin_slug_option_mode', 'standard');
    }

    /**
     * Get items per page
     *
     * @return int
     */
    public static function get_items_per_page(): int
    {
        return (int) get_option('plugin_slug_items_per_page', 20);
    }

    /**
     * Get default status
     *
     * @return string
     */
    public static function get_default_status(): string
    {
        return get_option('plugin_slug_default_status', 'draft');
    }

    /**
     * ============================================================================
     * NOTIFICATION SETTINGS
     * ============================================================================
     */

    /**
     * Should send notifications?
     *
     * @return bool
     */
    public static function send_notifications(): bool
    {
        return (bool) get_option('plugin_slug_send_notifications', true);
    }

    /**
     * Get notification email
     *
     * Falls back to WordPress admin email if not set.
     *
     * @return string
     */
    public static function get_notification_email(): string
    {
        return get_option('plugin_slug_notification_email', get_option('admin_email'));
    }

    /**
     * ============================================================================
     * DEBUG SETTINGS
     * ============================================================================
     */

    /**
     * Is debug mode enabled?
     *
     * @return bool
     */
    public static function is_debug_mode(): bool
    {
        return (bool) get_option('plugin_slug_debug_mode', false);
    }

    /**
     * Should delete data on uninstall?
     *
     * IMPORTANT: This controls whether uninstall.php removes data.
     * Default is false to preserve data.
     *
     * @return bool
     */
    public static function delete_on_uninstall(): bool
    {
        return (bool) get_option('plugin_slug_delete_on_uninstall', false);
    }

    /**
     * ============================================================================
     * ENCRYPTION METHODS
     * ============================================================================
     * 
     * Use these for storing sensitive data like API keys, tokens, etc.
     * Uses AES-256-CBC encryption.
     */

    /**
     * Encrypt sensitive data
     *
     * @param string $data Data to encrypt
     * @return string Encrypted data (base64 encoded)
     */
    public static function encrypt(string $data): string
    {
        if (empty($data)) {
            return '';
        }

        $key = self::get_encryption_key();
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
        
        // Combine encrypted data and IV, then base64 encode
        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decrypt sensitive data
     *
     * @param string $data Encrypted data (base64 encoded)
     * @return string Decrypted data
     */
    public static function decrypt(string $data): string
    {
        if (empty($data)) {
            return '';
        }

        $key = self::get_encryption_key();
        
        // Decode and split encrypted data and IV
        $decoded = base64_decode($data);
        if ($decoded === false || !str_contains($decoded, '::')) {
            return '';
        }
        
        list($encrypted, $iv) = explode('::', $decoded, 2);
        
        return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv) ?: '';
    }

    /**
     * Get or generate encryption key
     *
     * The key is generated once and stored in the database.
     * Uses SHA-256 hash for proper key length.
     *
     * @return string Binary encryption key
     */
    private static function get_encryption_key(): string
    {
        $key = get_option('plugin_slug_encryption_key');
        
        if (!$key) {
            // Generate a new key if not exists
            $key = wp_generate_password(32, true, true);
            update_option('plugin_slug_encryption_key', $key);
        }
        
        // Hash to get proper length for AES-256
        return hash('sha256', $key, true);
    }

    /**
     * ============================================================================
     * API CREDENTIAL METHODS
     * ============================================================================
     * 
     * Example methods for storing/retreiving encrypted API credentials.
     * Always encrypt sensitive values before storing.
     */

    /**
     * Get API key (decrypted)
     *
     * @return string
     */
    public static function get_api_key(): string
    {
        $encrypted = get_option('plugin_slug_api_key', '');
        return $encrypted ? self::decrypt($encrypted) : '';
    }

    /**
     * Save API key (encrypted)
     *
     * @param string $api_key API key to store
     * @return void
     */
    public static function save_api_key(string $api_key): void
    {
        if (empty($api_key)) {
            delete_option('plugin_slug_api_key');
        } else {
            update_option('plugin_slug_api_key', self::encrypt($api_key));
        }
    }

    /**
     * Get API secret (decrypted)
     *
     * @return string
     */
    public static function get_api_secret(): string
    {
        $encrypted = get_option('plugin_slug_api_secret', '');
        return $encrypted ? self::decrypt($encrypted) : '';
    }

    /**
     * Save API secret (encrypted)
     *
     * @param string $secret API secret to store
     * @return void
     */
    public static function save_api_secret(string $secret): void
    {
        if (empty($secret)) {
            delete_option('plugin_slug_api_secret');
        } else {
            update_option('plugin_slug_api_secret', self::encrypt($secret));
        }
    }

    /**
     * Clear all API credentials
     *
     * @return void
     */
    public static function clear_api_credentials(): void
    {
        delete_option('plugin_slug_api_key');
        delete_option('plugin_slug_api_secret');
    }

    /**
     * Check if API is configured
     *
     * @return bool
     */
    public static function is_api_configured(): bool
    {
        return !empty(self::get_api_key()) && !empty(self::get_api_secret());
    }
}
