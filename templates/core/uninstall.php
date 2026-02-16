<?php
/**
 * Uninstall Script
 *
 * This file is executed when the user clicks "Delete" in WordPress admin.
 * It runs OUTSIDE the normal WordPress environment, so:
 * - No $wpdb object (must create your own)
 * - No plugin functions (plugin is inactive)
 * - Direct execution only
 *
 * IMPORTANT: This file must be in the plugin root directory.
 * WordPress looks for uninstall.php first, then the Uninstall hook.
 *
 * @package PLUGIN_NAMESPACE
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Prevent execution if uninstall.php was called incorrectly
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * UNINSTALL PROCESS:
 * 
 * 1. Check if we should delete data (user option)
 * 2. Drop database tables
 * 3. Delete options
 * 4. Delete transients
 * 5. Clear cron jobs
 * 6. Remove files
 */

// Load WordPress database functions
global $wpdb;

// ========================================
// CHECK USER PREFERENCE
// ========================================

// Only delete data if user explicitly enabled this option
$delete_on_uninstall = get_option('plugin_slug_delete_on_uninstall', false);

if (!$delete_on_uninstall) {
    // User wants to keep data, exit silently
    exit;
}

// ========================================
// DROP DATABASE TABLES
// ========================================

$table_main = $wpdb->prefix . 'plugin_main_table';
$table_logs = $wpdb->prefix . 'plugin_slug_logs';

// Use $wpdb->query() for DROP (not dbDelta)
$wpdb->query("DROP TABLE IF EXISTS {$table_main}");
$wpdb->query("DROP TABLE IF EXISTS {$table_logs}");

// ========================================
// DELETE ALL OPTIONS
// ========================================

$options_to_delete = [
    // Version info
    'plugin_slug_version',
    'plugin_slug_previous_version',
    
    // Settings
    'plugin_slug_option_enabled',
    'plugin_slug_option_mode',
    'plugin_slug_items_per_page',
    'plugin_slug_default_status',
    'plugin_slug_send_notifications',
    'plugin_slug_notification_email',
    'plugin_slug_debug_mode',
    'plugin_slug_delete_on_uninstall',
    
    // Add all your option names here
];

foreach ($options_to_delete as $option) {
    delete_option($option);
}

// Alternative: Delete by prefix (more thorough)
// $wpdb->query(
//     $wpdb->prepare(
//         "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
//         'plugin_slug_%'
//     )
// );

// ========================================
// DELETE TRANSIENTS
// ========================================

// Delete transient values
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        '_transient_plugin_slug_%'
    )
);

// Delete transient timeouts
$wpdb->query(
    $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        '_transient_timeout_plugin_slug_%'
    )
);

// ========================================
// CLEAR CRON JOBS
// ========================================

wp_clear_scheduled_hook('plugin_slug_daily_cleanup');
wp_clear_scheduled_hook('plugin_slug_hourly_process');

// ========================================
// DELETE USER META (if used)
// ========================================

// $wpdb->delete(
//     $wpdb->usermeta,
//     ['meta_key' => 'plugin_slug_user_setting']
// );

// ========================================
// DELETE POST META (if used)
// ========================================

// $wpdb->delete(
//     $wpdb->postmeta,
//     ['meta_key' => 'plugin_slug_post_meta']
// );

// ========================================
// DELETE UPLOADED FILES (if used)
// ========================================

// $upload_dir = wp_upload_dir();
// $plugin_upload_dir = $upload_dir['basedir'] . '/plugin-slug/';
// 
// if (is_dir($plugin_upload_dir)) {
//     // Recursive directory removal
//     $iterator = new RecursiveIteratorIterator(
//         new RecursiveDirectoryIterator($plugin_upload_dir, RecursiveDirectoryIterator::SKIP_DOTS),
//         RecursiveIteratorIterator::CHILD_FIRST
//     );
//     
//     foreach ($iterator as $file) {
//         if ($file->isDir()) {
//             rmdir($file->getPathname());
//         } else {
//             unlink($file->getPathname());
//         }
//     }
//     
//     rmdir($plugin_upload_dir);
// }

// ========================================
// CLEAR CACHE
// ========================================

wp_cache_flush();

// Uninstall complete
