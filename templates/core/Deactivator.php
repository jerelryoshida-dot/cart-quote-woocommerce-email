<?php
/**
 * Plugin Deactivator
 *
 * Handles all tasks when plugin is deactivated:
 * - Clear scheduled cron jobs
 * - Remove transients
 * - Flush rewrite rules (if using custom post types)
 *
 * IMPORTANT: Deactivation should NOT delete data!
 * Data deletion belongs in uninstall.php
 *
 * @package PLUGIN_NAMESPACE\Core
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Core;

/**
 * Class Deactivator
 */
class Deactivator
{
    /**
     * Deactivate the plugin
     *
     * Called by register_deactivation_hook() in main plugin file.
     *
     * @return void
     */
    public function deactivate(): void
    {
        $this->clear_cron_jobs();
        $this->clear_transients();
        $this->flush_rewrite_rules();
    }

    /**
     * Clear scheduled cron jobs
     *
     * IMPORTANT: Always clear cron jobs on deactivation.
     * Orphaned cron jobs cause errors and performance issues.
     *
     * @return void
     */
    private function clear_cron_jobs(): void
    {
        // Clear each scheduled event
        wp_clear_scheduled_hook('plugin_slug_daily_cleanup');
        wp_clear_scheduled_hook('plugin_slug_hourly_process');
        
        // Clear any specific timestamps if needed
        // wp_unschedule_event($timestamp, 'plugin_slug_daily_cleanup');
    }

    /**
     * Clear plugin transients
     *
     * Transients may have expiration, but clear them explicitly
     * to ensure clean state on reactivation.
     *
     * @return void
     */
    private function clear_transients(): void
    {
        // Clear specific transients
        delete_transient('plugin_slug_cache_key');
        delete_transient('plugin_slug_temp_data');
        
        // Clear activation redirect flag
        delete_transient('plugin_slug_activation_redirect');
        
        // For plugins with many transients, use a pattern:
        // $this->clear_transients_by_prefix('plugin_slug_');
    }

    /**
     * Clear transients by prefix
     *
     * Helper method for cleaning up multiple transients.
     *
     * @param string $prefix Transient name prefix
     * @return void
     */
    private function clear_transients_by_prefix(string $prefix): void
    {
        global $wpdb;
        
        // Delete transients from options table
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} 
                WHERE option_name LIKE %s 
                OR option_name LIKE %s",
                '_transient_' . $prefix . '%',
                '_transient_timeout_' . $prefix . '%'
            )
        );
    }

    /**
     * Flush rewrite rules
     *
     * Required if plugin registers custom post types or taxonomies.
     * Ensures permalinks work correctly after deactivation.
     *
     * @return void
     */
    private function flush_rewrite_rules(): void
    {
        // Only needed if using custom post types
        // flush_rewrite_rules();
        
        // If you registered CPTs, flush after unregistering:
        // unregister_post_type('my_cpt');
        // flush_rewrite_rules();
    }
}
