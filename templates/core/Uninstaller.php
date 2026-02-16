<?php
/**
 * Plugin Uninstaller
 *
 * Handles complete plugin removal:
 * - Drop database tables
 * - Delete all options
 * - Delete all transients
 * - Remove uploaded files
 *
 * IMPORTANT: This only runs when user chooses "Delete" in plugins admin.
 * Deactivation does NOT trigger this.
 *
 * SECURITY: User must explicitly enable "Delete data on uninstall" option.
 * Never delete data without user consent!
 *
 * @package PLUGIN_NAMESPACE\Core
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Core;

/**
 * Class Uninstaller
 * 
 * Note: This class uses static methods because uninstall.php
 * runs outside the normal WordPress bootstrap.
 */
class Uninstaller
{
    /**
     * Uninstall the plugin
     *
     * Called by uninstall.php (not a hook).
     * This is a static method for direct invocation.
     *
     * @return void
     */
    public static function uninstall(): void
    {
        global $wpdb;

        // ========================================
        // SECURITY CHECK: Only delete if user opted in
        // ========================================
        
        $delete_data = get_option('plugin_slug_delete_on_uninstall', false);
        
        if (!$delete_data) {
            // User wants to preserve data, exit early
            return;
        }

        // ========================================
        // DROP DATABASE TABLES
        // ========================================
        
        $table_main = $wpdb->prefix . PLUGIN_PREFIX_TABLE_MAIN;
        $table_logs = $wpdb->prefix . 'plugin_slug_logs';

        // Use $wpdb->query() with DROP (dbDelta is for CREATE/ALTER only)
        $wpdb->query("DROP TABLE IF EXISTS {$table_main}");
        $wpdb->query("DROP TABLE IF EXISTS {$table_logs}");

        // ========================================
        // DELETE OPTIONS
        // ========================================
        
        // Delete each option individually
        delete_option('plugin_slug_version');
        delete_option('plugin_slug_previous_version');
        delete_option('plugin_slug_delete_on_uninstall');
        
        // Or delete by prefix (more thorough but use with caution)
        self::delete_options_by_prefix('plugin_slug_');

        // ========================================
        // DELETE TRANSIENTS
        // ========================================
        
        self::delete_transients_by_prefix('plugin_slug_');

        // ========================================
        // CLEAR CRON JOBS (just in case)
        // ========================================
        
        wp_clear_scheduled_hook('plugin_slug_daily_cleanup');
        wp_clear_scheduled_hook('plugin_slug_hourly_process');

        // ========================================
        // DELETE USER META (if stored)
        // ========================================
        
        // Delete meta for all users
        // $wpdb->delete(
        //     $wpdb->usermeta,
        //     ['meta_key' => 'plugin_slug_user_preference']
        // );

        // ========================================
        // DELETE POST META (if stored)
        // ========================================
        
        // $wpdb->delete(
        //     $wpdb->postmeta,
        //     ['meta_key' => 'plugin_slug_post_meta']
        // );

        // ========================================
        // DELETE UPLOADED FILES (if any)
        // ========================================
        
        // $upload_dir = wp_upload_dir();
        // $plugin_dir = $upload_dir['basedir'] . '/plugin-slug/';
        // if (is_dir($plugin_dir)) {
        //     self::recursive_rmdir($plugin_dir);
        // }

        // ========================================
        // FLUSH CACHE
        // ========================================
        
        wp_cache_flush();
    }

    /**
     * Delete options by prefix
     *
     * Removes all options starting with a given prefix.
     *
     * @param string $prefix Option name prefix
     * @return void
     */
    private static function delete_options_by_prefix(string $prefix): void
    {
        global $wpdb;

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                $prefix . '%'
            )
        );
    }

    /**
     * Delete transients by prefix
     *
     * Removes all transients starting with a given prefix.
     *
     * @param string $prefix Transient name prefix
     * @return void
     */
    private static function delete_transients_by_prefix(string $prefix): void
    {
        global $wpdb;

        // Delete transient values
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_' . $prefix . '%'
            )
        );

        // Delete transient timeouts
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_' . $prefix . '%'
            )
        );
    }

    /**
     * Recursively remove directory
     *
     * @param string $dir Directory path
     * @return bool
     */
    private static function recursive_rmdir(string $dir): bool
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? self::recursive_rmdir($path) : unlink($path);
        }

        return rmdir($dir);
    }
}
