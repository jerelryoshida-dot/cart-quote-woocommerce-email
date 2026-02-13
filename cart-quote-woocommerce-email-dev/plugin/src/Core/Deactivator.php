<?php
/**
 * Plugin Deactivator
 *
 * Handles plugin deactivation tasks including cron job cleanup
 * and flushing rewrite rules.
 *
 * @package CartQuoteWooCommerce\Core
 * @author Jerel Yoshida
 * @since 1.0.0
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Core;

/**
 * Class Deactivator
 */
class Deactivator
{
    /**
     * Deactivate the plugin
     *
     * @return void
     */
    public function deactivate(): void
    {
        $this->clear_cron_jobs();
        $this->flush_rewrite_rules();
    }

    /**
     * Clear scheduled cron jobs
     *
     * @return void
     */
    private function clear_cron_jobs(): void
    {
        wp_clear_scheduled_hook('cart_quote_wc_daily_cleanup');
        wp_clear_scheduled_hook('cart_quote_wc_refresh_google_token');
    }

    /**
     * Flush rewrite rules
     *
     * @return void
     */
    private function flush_rewrite_rules(): void
    {
        flush_rewrite_rules();
    }
}
