<?php
/**
 * Plugin Activator
 *
 * Handles all tasks when plugin is activated:
 * - Database table creation
 * - Default options setup
 * - Cron job scheduling
 * - Version tracking
 *
 * IMPORTANT: Always use dbDelta() for table creation
 * This handles CREATE and ALTER safely
 *
 * @package PLUGIN_NAMESPACE\Core
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Core;

/**
 * Class Activator
 */
class Activator
{
    /**
     * Activate the plugin
     *
     * Called by register_activation_hook() in main plugin file.
     * All methods are private to keep activation atomic.
     *
     * @return void
     */
    public function activate(): void
    {
        // Order matters: tables before options, options before cron
        $this->create_tables();
        $this->create_default_options();
        $this->schedule_cron_jobs();
        $this->set_version();
    }

    /**
     * ============================================================================
     * DATABASE TABLES
     * ============================================================================
     */

    /**
     * Create database tables
     *
     * Uses dbDelta() which:
     * - Creates table if it doesn't exist
     * - Alters table if structure changed
     * - Is safe to run on every activation
     *
     * @return void
     */
    private function create_tables(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        
        // Main table name with WordPress prefix
        $table_name = $wpdb->prefix . PLUGIN_PREFIX_TABLE_MAIN;

        /**
         * SQL STRUCTURE BEST PRACTICES:
         * 
         * - Always use $wpdb->prefix for table names
         * - bigint(20) unsigned for IDs
         * - varchar(255) for strings
         * - text/longtext for content
         * - datetime for timestamps
         * - Include created_at and updated_at
         * - Add indexes for columns used in WHERE/ORDER BY
         */
        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            
            -- Core fields
            title varchar(255) NOT NULL,
            content longtext DEFAULT NULL,
            status varchar(20) DEFAULT 'draft',
            
            -- Metadata
            author_id bigint(20) unsigned DEFAULT NULL,
            
            -- Timestamps
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            
            -- Primary key
            PRIMARY KEY (id),
            
            -- Indexes for common queries
            KEY status (status),
            KEY author_id (author_id),
            KEY created_at (created_at),
            
            -- Composite indexes for filtered queries
            KEY idx_status_created (status, created_at)
        ) $charset_collate;";

        // Load dbDelta function
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        
        // Create or update table
        dbDelta($sql);

        // Create additional tables if needed
        $this->create_logs_table();
    }

    /**
     * Create logs table for tracking actions
     *
     * Separate table for audit logs, history, etc.
     *
     * @return void
     */
    private function create_logs_table(): void
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'plugin_slug_logs';

        $sql = "CREATE TABLE $table_name (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            item_id bigint(20) unsigned NOT NULL,
            action varchar(100) NOT NULL,
            details longtext DEFAULT NULL,
            user_id bigint(20) unsigned DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY item_id (item_id),
            KEY action (action),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * ============================================================================
     * DEFAULT OPTIONS
     * ============================================================================
     */

    /**
     * Create default plugin options
     *
     * Use add_option() which only adds if option doesn't exist.
     * This preserves user settings on reactivation.
     *
     * @return void
     */
    private function create_default_options(): void
    {
        // ========================================
        // General Settings
        // ========================================
        
        add_option('plugin_slug_option_enabled', true);
        add_option('plugin_slug_option_mode', 'standard');
        
        // ========================================
        // Display Settings
        // ========================================
        
        add_option('plugin_slug_items_per_page', '20');
        add_option('plugin_slug_default_status', 'draft');
        
        // ========================================
        // Notification Settings
        // ========================================
        
        add_option('plugin_slug_send_notifications', true);
        add_option('plugin_slug_notification_email', get_option('admin_email'));
        
        // ========================================
        // Advanced Settings
        // ========================================
        
        add_option('plugin_slug_debug_mode', false);
        add_option('plugin_slug_delete_on_uninstall', false);
    }

    /**
     * ============================================================================
     * CRON JOBS
     * ============================================================================
     */

    /**
     * Schedule cron jobs
     *
     * Use wp_schedule_event() for recurring tasks.
     * Always check if already scheduled first.
     *
     * @return void
     */
    private function schedule_cron_jobs(): void
    {
        // Daily cleanup task
        if (!wp_next_scheduled('plugin_slug_daily_cleanup')) {
            wp_schedule_event(time(), 'daily', 'plugin_slug_daily_cleanup');
        }

        // Hourly processing task
        if (!wp_next_scheduled('plugin_slug_hourly_process')) {
            wp_schedule_event(time(), 'hourly', 'plugin_slug_hourly_process');
        }

        // Custom interval (add filter first)
        // add_filter('cron_schedules', function($schedules) {
        //     $schedules['every_5_minutes'] = [
        //         'interval' => 5 * 60,
        //         'display' => 'Every 5 Minutes'
        //     ];
        //     return $schedules;
        // });
    }

    /**
     * ============================================================================
     * VERSION TRACKING
     * ============================================================================
     */

    /**
     * Set plugin version in database
     *
     * Used for:
     * - Detecting updates
     * - Running upgrade routines
     *
     * @return void
     */
    private function set_version(): void
    {
        // Store current version
        add_option('plugin_slug_version', PLUGIN_PREFIX_VERSION);
        
        // Check if this is an upgrade
        $previous_version = get_option('plugin_slug_previous_version');
        
        if ($previous_version && version_compare($previous_version, PLUGIN_PREFIX_VERSION, '<')) {
            // Run upgrade routines if needed
            $this->run_upgrades($previous_version);
        }
        
        // Update stored version
        update_option('plugin_slug_previous_version', PLUGIN_PREFIX_VERSION);
    }

    /**
     * Run upgrade routines for version changes
     *
     * @param string $from_version Previous version
     * @return void
     */
    private function run_upgrades(string $from_version): void
    {
        // Example: Add new column in version 1.1.0
        // if (version_compare($from_version, '1.1.0', '<')) {
        //     global $wpdb;
        //     $table = $wpdb->prefix . PLUGIN_PREFIX_TABLE_MAIN;
        //     $wpdb->query("ALTER TABLE $table ADD COLUMN new_field varchar(255) DEFAULT ''");
        // }
    }
}
