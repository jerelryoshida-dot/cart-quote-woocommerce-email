<?php
/**
 * Tier Service
 *
 * Handles tier data retrieval from WooCommerce product meta fields.
 * Tier data is stored as individual meta fields per tier level.
 *
 * Meta field naming convention: _cart_quote_tier_{n}_{field}
 * Where n = tier number (1, 2, 3, ...) and field = level|description|tier_name|monthly_price|hourly_price|is_active
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
    const MAX_TIERS = 10;

    public static function get_all_tiers_by_product(int $product_id): array
    {
        if ($product_id <= 0) {
            return [];
        }

        try {
            $tiers = [];

            for ($i = 1; $i <= self::MAX_TIERS; $i++) {
                $level = get_post_meta($product_id, "_cart_quote_tier_{$i}_level", true);

                if ($level === '' || $level === false) {
                    break;
                }

                $tiers[] = [
                    'tier_level'    => (int) $level,
                    'description'   => get_post_meta($product_id, "_cart_quote_tier_{$i}_description", true) ?: '',
                    'tier_name'     => get_post_meta($product_id, "_cart_quote_tier_{$i}_tier_name", true) ?: '',
                    'monthly_price' => (float) get_post_meta($product_id, "_cart_quote_tier_{$i}_monthly_price", true) ?: 0.0,
                    'hourly_price'  => (float) get_post_meta($product_id, "_cart_quote_tier_{$i}_hourly_price", true) ?: 0.0,
                    'is_active'     => (bool) get_post_meta($product_id, "_cart_quote_tier_{$i}_is_active", true),
                ];
            }

            return $tiers;

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
        if ($product_id <= 0) {
            return null;
        }

        $tiers = self::get_all_tiers_by_product($product_id);

        foreach ($tiers as $tier) {
            if ($tier['is_active']) {
                return $tier;
            }
        }

        return null;
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
                'monthly_price' => $first_tier['monthly_price'] ?? 0.0,
                'hourly_price'  => $first_tier['hourly_price'] ?? 0.0,
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
        if ($product_id <= 0) {
            return false;
        }

        try {
            for ($i = 1; $i <= self::MAX_TIERS; $i++) {
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_level");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_description");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_tier_name");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_monthly_price");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_hourly_price");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_is_active");
            }

            foreach ($tiers as $index => $tier) {
                $i = $index + 1;

                if (!empty($tier['level'])) {
                    update_post_meta($product_id, "_cart_quote_tier_{$i}_level", (int) $tier['level']);
                    update_post_meta($product_id, "_cart_quote_tier_{$i}_description", sanitize_text_field($tier['description'] ?? ''));
                    update_post_meta($product_id, "_cart_quote_tier_{$i}_tier_name", sanitize_text_field($tier['tier_name'] ?? ''));
                    update_post_meta($product_id, "_cart_quote_tier_{$i}_monthly_price", (float) ($tier['monthly_price'] ?? 0));
                    update_post_meta($product_id, "_cart_quote_tier_{$i}_hourly_price", (float) ($tier['hourly_price'] ?? 0));
                    update_post_meta($product_id, "_cart_quote_tier_{$i}_is_active", !empty($tier['is_active']) ? 1 : 0);
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
        if ($product_id <= 0) {
            return false;
        }

        try {
            for ($i = 1; $i <= self::MAX_TIERS; $i++) {
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_level");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_description");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_tier_name");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_monthly_price");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_hourly_price");
                delete_post_meta($product_id, "_cart_quote_tier_{$i}_is_active");
            }

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
                error_log('    tier_level: ' . $tier['tier_level']);
                error_log('    description: ' . $tier['description']);
                error_log('    tier_name: ' . $tier['tier_name']);
                error_log('    monthly_price: ' . $tier['monthly_price']);
                error_log('    hourly_price: ' . $tier['hourly_price']);
                error_log('    is_active: ' . ($tier['is_active'] ? 'true' : 'false'));
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
