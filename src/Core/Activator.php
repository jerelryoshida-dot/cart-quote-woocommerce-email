<?php
/**
 * Plugin Activator
 *
 * Handles plugin activation tasks including database table creation,
 * default options setup, and cron job scheduling.
 *
 * @package CartQuoteWooCommerce\Core
 * @author Jerel Yoshida
 * @since 1.0.0
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Core;

/**
 * Class Activator
 */
class Activator
{
    /**
     * Activate the plugin
     *
     * @return void
     */
    public function activate(): void
    {
        $this->create_tables();
        $this->create_default_options();
        $this->schedule_cron_jobs();
        $this->set_version();
    }

    /**
     * Create database tables
     *
     * @return void
     */
    private function create_tables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . CART_QUOTE_WC_TABLE_SUBMISSIONS;

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            quote_id varchar(20) NOT NULL,
            customer_name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) DEFAULT NULL,
            company_name varchar(255) DEFAULT NULL,
            preferred_date date DEFAULT NULL,
            preferred_time varchar(20) DEFAULT NULL,
            contract_duration varchar(100) DEFAULT NULL,
            meeting_requested tinyint(1) DEFAULT 0,
            cart_data longtext NOT NULL,
            subtotal decimal(10,2) DEFAULT 0.00,
            status varchar(20) DEFAULT 'pending',
            admin_notes text DEFAULT NULL,
            google_event_id varchar(255) DEFAULT NULL,
            calendar_synced tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY quote_id (quote_id),
            KEY email (email),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        // Create logs table
        $this->create_logs_table();
    }

    /**
     * Create logs table for event tracking
     *
     * @return void
     */
    private function create_logs_table(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'cart_quote_logs';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            quote_id varchar(20) NOT NULL,
            action varchar(100) NOT NULL,
            details longtext DEFAULT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY quote_id (quote_id),
            KEY action (action),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Create default plugin options
     *
     * @return void
     */
    private function create_default_options(): void
    {
        // General settings
        add_option('cart_quote_wc_quote_prefix', 'Q');
        add_option('cart_quote_wc_quote_start_number', '1001');

        // Email settings
        add_option('cart_quote_wc_send_to_admin', true);
        add_option('cart_quote_wc_send_to_client', true);
        add_option('cart_quote_wc_admin_email', get_option('admin_email'));
        add_option('cart_quote_wc_email_subject_admin', 'New Quote Submission #{quote_id}');
        add_option('cart_quote_wc_email_subject_client', 'Thank you for your quote request #{quote_id}');
        add_option('cart_quote_wc_enable_pdf', false);

        // Time slot settings
        add_option('cart_quote_wc_meeting_duration', '60');
        add_option('cart_quote_wc_time_slots', [
            '09:00',
            '11:00',
            '14:00',
            '16:00',
        ]);

        // Google Calendar settings
        add_option('cart_quote_wc_google_client_id', '');
        add_option('cart_quote_wc_google_client_secret', '');
        add_option('cart_quote_wc_google_access_token', '');
        add_option('cart_quote_wc_google_refresh_token', '');
        add_option('cart_quote_wc_google_calendar_id', 'primary');
        add_option('cart_quote_wc_google_connected', false);

        // Status settings
        add_option('cart_quote_wc_default_status', 'pending');
        add_option('cart_quote_wc_auto_create_event', false);

        // Delete on uninstall
        add_option('cart_quote_wc_delete_on_uninstall', false);
    }

    /**
     * Schedule cron jobs
     *
     * @return void
     */
    private function schedule_cron_jobs(): void
    {
        // Schedule daily cleanup of expired transients
        if (!wp_next_scheduled('cart_quote_wc_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'cart_quote_wc_daily_cleanup');
        }

        // Schedule Google token refresh
        if (!wp_next_scheduled('cart_quote_wc_refresh_google_token')) {
            wp_schedule_event(time(), 'hourly', 'cart_quote_wc_refresh_google_token');
        }
    }

    /**
     * Set plugin version
     *
     * @return void
     */
    private function set_version(): void
    {
        add_option('cart_quote_wc_version', CART_QUOTE_WC_VERSION);
    }
}
