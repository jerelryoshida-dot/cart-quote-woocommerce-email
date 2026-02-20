<?php
/**
 * Tier Service
 *
 * Handles tier data retrieval from the wp_welp_product_tiers database table.
 *
 * @package CartQuoteWooCommerce\Services
 * @author Jerel Yoshida
 * @since 1.0.39
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Services;

use CartQuoteWooCommerce\Admin\Settings;
use CartQuoteWooCommerce\Core\Debug_Logger;

class Tier_Service
{
    private static $table_name = 'welp_product_tiers';

    public static function get_all_tiers_by_product(int $product_id): array
    {
        global $wpdb;

        if ($product_id <= 0) {
            return [];
        }

        try {
            $table = $wpdb->prefix . self::$table_name;

            $tiers = $wpdb->get_results($wpdb->prepare(
                "SELECT * FROM `{$table}` WHERE product_id = %d ORDER BY `tier_level` ASC",
                $product_id
            ), ARRAY_A);

            return $tiers ?: [];

        } catch (\Exception $e) {
            Debug_Logger::get_instance()->error(
                'Failed to get all tiers for product',
                [
                    'product_id' => $product_id,
                    'error'      => $e->getMessage(),
                ]
            );
            return [];
        }
    }

    public static function get_tier_by_product(int $product_id): ?array
    {
        global $wpdb;

        if ($product_id <= 0) {
            return null;
        }

        try {
            $table = $wpdb->prefix . self::$table_name;

            $tier = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM `{$table}` WHERE product_id = %d ORDER BY `tier_level` ASC LIMIT 1",
                $product_id
            ), ARRAY_A);

            return $tier ?: null;

        } catch (\Exception $e) {
            Debug_Logger::get_instance()->error(
                'Failed to get tier for product',
                [
                    'product_id' => $product_id,
                    'error'      => $e->getMessage(),
                ]
            );
            return null;
        }
    }

    public static function get_tier_by_level(int $product_id, int $tier_level): ?array
    {
        global $wpdb;

        if ($product_id <= 0 || $tier_level <= 0) {
            return null;
        }

        try {
            $table = $wpdb->prefix . self::$table_name;

            $tier = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM `{$table}` WHERE product_id = %d AND tier_level = %d LIMIT 1",
                $product_id,
                $tier_level
            ), ARRAY_A);

            return $tier ?: null;

        } catch (\Exception $e) {
            Debug_Logger::get_instance()->error(
                'Failed to get tier by level',
                [
                    'product_id' => $product_id,
                    'tier_level' => $tier_level,
                    'error'      => $e->getMessage(),
                ]
            );
            return null;
        }
    }

    public static function get_tier_data_for_cart(int $product_id): ?array
    {
        if ($product_id <= 0) {
            return null;
        }

        try {
            $all_tiers = self::get_all_tiers_by_product($product_id);

            self::log_debug($product_id, $all_tiers);

            if (empty($all_tiers)) {
                return null;
            }

            $first_tier = $all_tiers[0];

            $result = [
                'description'   => $first_tier['description'] ?? '',
                'tier_name'     => $first_tier['tier_name'] ?? '',
                'tier_level'    => $first_tier['tier_level'] ?? '',
                'monthly_price' => (float) ($first_tier['monthly_price'] ?? 0),
                'hourly_price'  => (float) ($first_tier['hourly_price'] ?? 0),
                '_all_tiers'    => $all_tiers,
            ];

            self::log_result($result);

            return $result;

        } catch (\Exception $e) {
            Debug_Logger::get_instance()->error(
                'Failed to get tier data for cart',
                [
                    'product_id' => $product_id,
                    'error'      => $e->getMessage(),
                ]
            );
            return null;
        }
    }

    public static function save_tiers(int $product_id, array $tiers): bool
    {
        global $wpdb;

        if ($product_id <= 0) {
            return false;
        }

        try {
            $table = $wpdb->prefix . self::$table_name;

            $wpdb->query($wpdb->prepare(
                "DELETE FROM `{$table}` WHERE product_id = %d",
                $product_id
            ));

            foreach ($tiers as $tier) {
                if (!empty($tier['level'])) {
                    $wpdb->insert($table, [
                        'product_id'    => $product_id,
                        'tier_level'    => (int) $tier['level'],
                        'description'   => sanitize_text_field($tier['description'] ?? ''),
                        'tier_name'     => sanitize_text_field($tier['tier_name'] ?? ''),
                        'monthly_price' => (float) ($tier['monthly_price'] ?? 0),
                        'hourly_price'  => (float) ($tier['hourly_price'] ?? 0),
                    ]);
                }
            }

            return true;

        } catch (\Exception $e) {
            Debug_Logger::get_instance()->error(
                'Failed to save tiers for product',
                [
                    'product_id' => $product_id,
                    'error'      => $e->getMessage(),
                ]
            );
            return false;
        }
    }

    public static function delete_all_tiers(int $product_id): bool
    {
        global $wpdb;

        if ($product_id <= 0) {
            return false;
        }

        try {
            $table = $wpdb->prefix . self::$table_name;

            $wpdb->query($wpdb->prepare(
                "DELETE FROM `{$table}` WHERE product_id = %d",
                $product_id
            ));

            return true;

        } catch (\Exception $e) {
            Debug_Logger::get_instance()->error(
                'Failed to delete tiers for product',
                [
                    'product_id' => $product_id,
                    'error'      => $e->getMessage(),
                ]
            );
            return false;
        }
    }

    private static function log_debug(int $product_id, array $all_tiers): void
    {
        if (!Settings::is_debug_mini_cart_enabled()) {
            return;
        }

        if (!defined('WP_DEBUG') || !WP_DEBUG || !defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return;
        }

        error_log('============================================================');
        error_log('MINI-CART DEBUG: Tier_Service::get_tier_data_for_cart()');
        error_log('============================================================');
        error_log('  Product ID: ' . $product_id);
        error_log('  All Tiers Found: ' . count($all_tiers));

        if (!empty($all_tiers)) {
            foreach ($all_tiers as $i => $tier) {
                error_log('  Tier [' . $i . ']:');
                error_log('    tier_level: ' . ($tier['tier_level'] ?? 'N/A'));
                error_log('    description: ' . ($tier['description'] ?? 'N/A'));
                error_log('    tier_name: ' . ($tier['tier_name'] ?? 'N/A'));
                error_log('    monthly_price: ' . ($tier['monthly_price'] ?? 'N/A'));
                error_log('    hourly_price: ' . ($tier['hourly_price'] ?? 'N/A'));
            }
        } else {
            error_log('  WARNING: No tiers found for this product!');
        }
    }

    private static function log_result(array $result): void
    {
        if (!Settings::is_debug_mini_cart_enabled()) {
            return;
        }

        if (!defined('WP_DEBUG') || !WP_DEBUG || !defined('WP_DEBUG_LOG') || !WP_DEBUG_LOG) {
            return;
        }

        error_log('  Returning first tier:');
        error_log('    tier_level: ' . $result['tier_level']);
        error_log('    description: ' . $result['description']);
        error_log('    tier_name: ' . $result['tier_name']);
    }
}
