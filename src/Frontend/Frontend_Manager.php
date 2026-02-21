<?php
/**
 * Frontend Manager
 *
 * Handles all frontend functionality including AJAX handlers
 * for cart operations and quote form submission.
 *
 * @package CartQuoteWooCommerce\Frontend
 * @author Jerel Yoshida
 * @since 1.0.0
 */

namespace CartQuoteWooCommerce\Frontend;

use CartQuoteWooCommerce\Core\Debug_Logger;

class Frontend_Manager
{
    private $logger;

    public function __construct()
    {
        $this->logger = Debug_Logger::get_instance();
    }

    public function init()
    {
        // Add shortcode for quote form
        add_shortcode('cart_quote_form', [$this, 'render_quote_form_shortcode']);
        
        // Add shortcode for cart display
        add_shortcode('cart_quote_cart', [$this, 'render_cart_shortcode']);
        
        // Add shortcode for mini cart
        add_shortcode('cart_quote_mini_cart', [$this, 'render_mini_cart_shortcode']);
        
        // Add success message after submission
        add_action('woocommerce_before_cart', [$this, 'show_submission_success']);
    }

    /**
     * Render quote form shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_quote_form_shortcode($atts = [])
    {
        $atts = shortcode_atts([
            'show_cart' => 'true',
        ], $atts);

        // Check if cart is empty
        if (WC()->cart->is_empty()) {
            return '<div class="cart-quote-empty-cart">' . 
                __('Your cart is empty. Please add items before submitting a quote.', 'cart-quote-woocommerce-email') .
                '</div>';
        }

        $time_slots = get_option('cart_quote_wc_time_slots', ['09:00', '11:00', '14:00', '16:00']);

        ob_start();
        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/frontend/quote-form.php';
        return ob_get_clean();
    }

    /**
     * Render cart shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_cart_shortcode($atts = [])
    {
        $atts = shortcode_atts([
            'show_button' => 'true',
        ], $atts);

        if (WC()->cart->is_empty()) {
            return '<div class="cart-quote-empty-cart">' . 
                __('Your cart is empty.', 'cart-quote-woocommerce-email') .
                '</div>';
        }

        ob_start();
        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/frontend/cart-display.php';
        return ob_get_clean();
    }

    /**
     * Render mini cart shortcode
     *
     * @param array $atts Shortcode attributes
     * @return string
     */
    public function render_mini_cart_shortcode($atts = [])
    {
        $atts = shortcode_atts([
            'show_subtotal' => 'true',
            'show_count' => 'true',
            'show_tier_items' => 'true',
        ], $atts);

        ob_start();
        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/frontend/mini-cart.php';
        return ob_get_clean();
    }

    /**
     * Show submission success message
     *
     * @return void
     */
    public function show_submission_success()
    {
        if (!isset($_GET['quote_submitted']) || $_GET['quote_submitted'] !== '1') {
            return;
        }

        $quote_id = sanitize_text_field($_GET['quote_id'] ?? '');
        ?>
        <div class="woocommerce-message cart-quote-success" role="alert">
            <strong><?php esc_html_e('Quote Submitted Successfully!', 'cart-quote-woocommerce-email'); ?></strong>
            <p>
                <?php 
                printf(
                    esc_html__('Your quote reference is %s. We will contact you shortly.', 'cart-quote-woocommerce-email'),
                    '<strong>' . esc_html($quote_id) . '</strong>'
                );
                ?>
            </p>
        </div>
        <?php
    }

    /**
     * Handle cart update AJAX
     *
     * @return void
     */
    public function handle_cart_update()
    {
        try {
            $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');
            $quantity = (int) ($_POST['quantity'] ?? 0);

            if (empty($cart_item_key)) {
                wp_send_json_error(['message' => __('Invalid cart item key.', 'cart-quote-woocommerce-email')]);
            }

            if ($quantity < 0) {
                wp_send_json_error(['message' => __('Invalid quantity.', 'cart-quote-woocommerce-email')]);
            }

            if (!function_exists('WC') || !WC()->cart) {
                $this->logger->error('Cart update failed: WooCommerce cart not available');
                wp_send_json_error(['message' => __('Cart not available.', 'cart-quote-woocommerce-email')]);
            }

            if ($quantity === 0) {
                $removed = WC()->cart->remove_cart_item($cart_item_key);
                if (!$removed) {
                    $this->logger->warning('Failed to remove cart item', ['cart_item_key' => $cart_item_key]);
                    wp_send_json_error(['message' => __('Could not remove item.', 'cart-quote-woocommerce-email')]);
                }
            } else {
                $updated = WC()->cart->set_quantity($cart_item_key, $quantity, true);
                if (!$updated) {
                    $this->logger->warning('Failed to update cart quantity', [
                        'cart_item_key' => $cart_item_key,
                        'quantity' => $quantity,
                    ]);
                    wp_send_json_error(['message' => __('Could not update quantity.', 'cart-quote-woocommerce-email')]);
                }
            }

            WC()->cart->calculate_totals();

            $cart_items = [];
            foreach (WC()->cart->get_cart() as $item_key => $cart_item) {
                $product = $cart_item['data'];
                $cart_items[] = [
                    'key' => $item_key,
                    'quantity' => $cart_item['quantity'],
                    'line_total' => wc_price($cart_item['line_total']),
                ];
            }

            wp_send_json_success([
                'message' => __('Cart updated.', 'cart-quote-woocommerce-email'),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'subtotal' => WC()->cart->get_cart_subtotal(),
                'cart_hash' => WC()->cart->get_cart_hash(),
                'items' => $cart_items,
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Exception in handle_cart_update', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            wp_send_json_error(['message' => __('An error occurred.', 'cart-quote-woocommerce-email')]);
        }
    }

    /**
     * Handle remove item AJAX
     *
     * @return void
     */
    public function handle_remove_item()
    {
        try {
            $cart_item_key = sanitize_text_field($_POST['cart_item_key'] ?? '');

            if (empty($cart_item_key)) {
                wp_send_json_error(['message' => __('Invalid cart item.', 'cart-quote-woocommerce-email')]);
            }

            if (!function_exists('WC') || !WC()->cart) {
                $this->logger->error('Remove item failed: WooCommerce cart not available');
                wp_send_json_error(['message' => __('Cart not available.', 'cart-quote-woocommerce-email')]);
            }

            $removed = WC()->cart->remove_cart_item($cart_item_key);

            if ($removed) {
                WC()->cart->calculate_totals();
                
                wp_send_json_success([
                    'message' => __('Item removed.', 'cart-quote-woocommerce-email'),
                    'cart_count' => WC()->cart->get_cart_contents_count(),
                    'subtotal' => WC()->cart->get_cart_subtotal(),
                ]);
            } else {
                $this->logger->warning('Failed to remove cart item', ['cart_item_key' => $cart_item_key]);
                wp_send_json_error(['message' => __('Failed to remove item.', 'cart-quote-woocommerce-email')]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception in handle_remove_item', [
                'exception' => $e->getMessage(),
            ]);
            wp_send_json_error(['message' => __('An error occurred.', 'cart-quote-woocommerce-email')]);
        }
    }

    /**
     * Handle get cart AJAX
     *
     * @return void
     */
    public function handle_get_cart()
    {
        // Make sure WooCommerce cart is available
        if (!function_exists('WC') || !WC()->cart) {
            wp_send_json_error(['message' => __('Cart not available.', 'cart-quote-woocommerce-email')]);
        }

        // Calculate totals first to ensure everything is up to date
        WC()->cart->calculate_totals();

        $cart_items = [];

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            
            $tier_data = isset($cart_item['tier_data']) ? $cart_item['tier_data'] : null;
            
            $cart_items[] = [
                'key' => $cart_item_key,
                'product_id' => $cart_item['product_id'],
                'product_name' => $product->get_name(),
                'product_url' => $product->get_permalink(),
                'product_image' => $product->get_image(),
                'quantity' => $cart_item['quantity'],
                'price' => $product->get_price_html(),
                'line_total' => wc_price($cart_item['line_total']),
                'line_total_raw' => $cart_item['line_total'],
                'tier_data' => $tier_data,
            ];
        }

        wp_send_json_success([
            'items' => $cart_items,
            'count' => WC()->cart->get_cart_contents_count(),
            'subtotal' => WC()->cart->get_cart_subtotal(),
            'is_empty' => WC()->cart->is_empty(),
            'formatted_subtotal' => WC()->cart->get_cart_subtotal(),
        ]);
    }

    /**
     * Get formatted cart data for display
     *
     * @return array
     */
    public static function get_cart_data()
    {
        $items = [];

        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product = $cart_item['data'];
            
            $items[] = [
                'key' => $cart_item_key,
                'product_id' => $cart_item['product_id'],
                'name' => $product->get_name(),
                'sku' => $product->get_sku(),
                'price' => (float) $product->get_price(),
                'quantity' => $cart_item['quantity'],
                'subtotal' => $cart_item['line_subtotal'],
                'total' => $cart_item['line_total'],
                'image' => $product->get_image('thumbnail'),
                'url' => $product->get_permalink(),
            ];
        }

        return [
            'items' => $items,
            'count' => WC()->cart->get_cart_contents_count(),
            'subtotal' => WC()->cart->get_subtotal(),
            'formatted_subtotal' => WC()->cart->get_cart_subtotal(),
        ];
    }
}
