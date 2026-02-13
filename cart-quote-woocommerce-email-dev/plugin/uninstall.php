<?php
/**
 * Uninstall Script
 *
 * Executes when the plugin is uninstalled (deleted) via WordPress admin.
 * Only removes data if the "Delete on Uninstall" option is enabled.
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

// If uninstall not called from WordPress, exit
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Check if we should delete data
$delete_on_uninstall = get_option('cart_quote_wc_delete_on_uninstall', false);

if ($delete_on_uninstall) {
    global $wpdb;

    // Drop tables
    $table_submissions = $wpdb->prefix . 'cart_quote_submissions';
    $table_logs = $wpdb->prefix . 'cart_quote_logs';

    $wpdb->query("DROP TABLE IF EXISTS `{$table_submissions}`");
    $wpdb->query("DROP TABLE IF EXISTS `{$table_logs}`");

    // Delete options
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
        'cart_quote_wc_google_token_expires',
        'cart_quote_wc_default_status',
        'cart_quote_wc_auto_create_event',
        'cart_quote_wc_delete_on_uninstall',
        'cart_quote_wc_encryption_key',
    ];

    foreach ($options as $option) {
        delete_option($option);
    }

    // Delete transients
    $wpdb->query(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_cart_quote_wc_%' OR option_name LIKE '_site_transient_cart_quote_wc_%'"
    );

    // Clear scheduled cron jobs
    wp_clear_scheduled_hook('cart_quote_wc_daily_cleanup');
    wp_clear_scheduled_hook('cart_quote_wc_refresh_google_token');
}
