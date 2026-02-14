<?php
/**
 * Plugin Uninstaller
 *
 * Handles complete plugin uninstallation including database table removal
 * and options cleanup. Only runs when 'Delete on Uninstall' is enabled.
 *
 * @package CartQuoteWooCommerce\Core
 * @author Jerel Yoshida
 * @since 1.0.0
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Core;

/**
 * Class Uninstaller
 */
class Uninstaller
{
    /**
     * Uninstall the plugin completely
     *
     * @return void
     */
    public function uninstall(): void
    {
        $this->drop_tables();
        $this->delete_options();
        $this->delete_transients();
        $this->clear_cron_jobs();
    }

    /**
     * Drop database tables
     *
     * @return void
     */
    private function drop_tables(): void
    {
        global $wpdb;

        $tables = [
            $wpdb->prefix . CART_QUOTE_WC_TABLE_SUBMISSIONS,
            $wpdb->prefix . 'cart_quote_logs',
        ];

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS `{$table}`"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
        }
    }

    /**
     * Delete all plugin options
     *
     * @return void
     */
    private function delete_options(): void
    {
        $options = [
            'cart_quote_wc_version',
            'cart_quote_wc_quote_prefix',
            'cart_quote_wc_quote_start_number',
            'cart_quote_wc_send_to_admin',
            'cart_quote_wc_send_to_client',
            'cart_quote_wc_admin_email',
            'cart_quote_wc_email_subject_admin',
            'cart_quote_wc_email_subject_client',
            'cart_quote_wc_enable_pdf',
            'cart_quote_wc_meeting_duration',
            'cart_quote_wc_time_slots',
            'cart_quote_wc_google_client_id',
            'cart_quote_wc_google_client_secret',
            'cart_quote_wc_google_access_token',
            'cart_quote_wc_google_refresh_token',
            'cart_quote_wc_google_calendar_id',
            'cart_quote_wc_google_connected',
            'cart_quote_wc_default_status',
            'cart_quote_wc_auto_create_event',
            'cart_quote_wc_delete_on_uninstall',
            'cart_quote_wc_google_token_expires',
        ];

        foreach ($options as $option) {
            delete_option($option);
        }

        // Delete any remaining options with our prefix
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $wpdb->esc_like('cart_quote_wc_') . '%'
            )
        );
    }

    /**
     * Delete all plugin transients
     *
     * @return void
     */
    private function delete_transients(): void
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
                '_transient_cart_quote_wc_%',
                '_site_transient_cart_quote_wc_%'
            )
        );
    }

    /**
     * Clear any remaining cron jobs
     *
     * @return void
     */
    private function clear_cron_jobs(): void
    {
        wp_clear_scheduled_hook('cart_quote_wc_daily_cleanup');
        wp_clear_scheduled_hook('cart_quote_wc_refresh_google_token');
    }
}
