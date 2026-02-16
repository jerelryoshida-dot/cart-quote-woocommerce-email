<?php
/**
 * WooCommerce Integration Class
 *
 * Handles WooCommerce-specific functionality:
 * - Dependency checks
 * - Checkout modification
 * - Cart integration
 * - Feature compatibility
 *
 * @package PLUGIN_NAMESPACE\WooCommerce
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\WooCommerce;

/**
 * Class WooCommerce_Integration
 */
class WooCommerce_Integration
{
    /**
     * Initialize WooCommerce integration
     *
     * @return void
     */
    public function init(): void
    {
        // Check if WooCommerce is active
        if (!$this->is_woocommerce_active()) {
            return;
        }

        // Declare feature compatibility
        add_action('before_woocommerce_init', [$this, 'declare_compatibility']);

        // Modify checkout (if needed)
        add_filter('woocommerce_checkout_fields', [$this, 'modify_checkout_fields']);
        add_filter('woocommerce_order_button_text', [$this, 'change_order_button_text']);

        // Cart hooks (if needed)
        add_action('woocommerce_before_calculate_totals', [$this, 'before_calculate_totals']);

        // Custom WooCommerce endpoints (if needed)
        // add_action('init', [$this, 'add_endpoints']);
    }

    /**
     * Check if WooCommerce is active
     *
     * @return bool
     */
    public function is_woocommerce_active(): bool
    {
        return class_exists('WooCommerce');
    }

    /**
     * Declare WooCommerce feature compatibility
     *
     * IMPORTANT: This fixes "incompatible with WooCommerce features" notices.
     * Called on 'before_woocommerce_init' hook.
     *
     * @return void
     */
    public function declare_compatibility(): void
    {
        if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
            // High-Performance Order Storage (HPOS)
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'custom_order_tables',
                PLUGIN_PREFIX_PLUGIN_FILE,
                true
            );

            // Product block editor
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'product_block_editor',
                PLUGIN_PREFIX_PLUGIN_FILE,
                true
            );

            // Cart/Checkout blocks
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
                'cart_checkout_blocks',
                PLUGIN_PREFIX_PLUGIN_FILE,
                true
            );
        }
    }

    /**
     * Modify checkout fields
     *
     * @param array<string, array<string, array<string, mixed>>> $fields Checkout fields
     * @return array<string, array<string, array<string, mixed>>>
     */
    public function modify_checkout_fields(array $fields): array
    {
        // Example: Remove a field
        // unset($fields['billing']['billing_company']);

        // Example: Make a field not required
        // $fields['billing']['billing_phone']['required'] = false;

        // Example: Add custom field
        // $fields['billing']['billing_custom_field'] = [
        //     'type'        => 'text',
        //     'label'       => __('Custom Field', 'TEXT_DOMAIN'),
        //     'placeholder' => __('Enter value', 'TEXT_DOMAIN'),
        //     'required'    => false,
        //     'class'       => ['form-row-wide'],
        //     'priority'    => 25,
        // ];

        return $fields;
    }

    /**
     * Change order button text
     *
     * @return string
     */
    public function change_order_button_text(): string
    {
        return __('Submit Request', 'TEXT_DOMAIN');
    }

    /**
     * Before calculate totals hook
     *
     * @return void
     */
    public function before_calculate_totals(): void
    {
        // Example: Modify cart item prices
        // foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        //     $product = $cart_item['data'];
        //     $price = $product->get_price();
        //     // Modify price based on conditions
        // }
    }

    /**
     * Get cart data
     *
     * @return array<string, mixed>
     */
    public static function get_cart_data(): array
    {
        if (!function_exists('WC') || WC()->cart === null) {
            return [
                'items'    => [],
                'count'    => 0,
                'subtotal' => 0,
                'total'    => 0,
            ];
        }

        $cart = WC()->cart;
        $items = [];

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            $items[] = [
                'key'         => $cart_item_key,
                'product_id'  => $cart_item['product_id'],
                'name'        => $product->get_name(),
                'price'       => (float) $product->get_price(),
                'quantity'    => $cart_item['quantity'],
                'subtotal'    => (float) $cart_item['line_subtotal'],
                'total'       => (float) $cart_item['line_total'],
                'product_url' => get_permalink($cart_item['product_id']),
            ];
        }

        return [
            'items'    => $items,
            'count'    => $cart->get_cart_contents_count(),
            'subtotal' => (float) $cart->get_subtotal(),
            'total'    => (float) $cart->get_totals()['total'] ?? 0,
        ];
    }

    /**
     * Format price for display
     *
     * @param float $price Price
     * @return string
     */
    public static function format_price(float $price): string
    {
        if (!function_exists('wc_price')) {
            return '$' . number_format($price, 2);
        }
        return wc_price($price);
    }

    /**
     * Check if cart is empty
     *
     * @return bool
     */
    public static function is_cart_empty(): bool
    {
        if (!function_exists('WC') || WC()->cart === null) {
            return true;
        }
        return WC()->cart->is_empty();
    }

    /**
     * Get product by ID
     *
     * @param int $product_id Product ID
     * @return \WC_Product|null
     */
    public static function get_product(int $product_id): ?\WC_Product
    {
        $product = wc_get_product($product_id);
        return $product instanceof \WC_Product ? $product : null;
    }
}
