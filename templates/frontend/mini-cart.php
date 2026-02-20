<?php
/**
 * Frontend Mini Cart Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$cart_count = WC()->cart->get_cart_contents_count();
$cart_subtotal = WC()->cart->get_cart_subtotal();
$is_empty = WC()->cart->is_empty();

$parent_items = [];
$tier_items_by_parent = [];

if (!$is_empty) {
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        if (isset($cart_item['tier_data'])) {
            $parent_id = $cart_item['product_id'];
            $tier_items_by_parent[$parent_id][] = $cart_item;
        } else {
            $parent_items[] = $cart_item;
        }
    }
}
?>
<div class="cart-quote-mini-cart-container" data-nonce="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>">
    <div class="cart-quote-mini-cart">
        <div class="cart-quote-mini-toggle">
            <span class="dashicons dashicons-cart cart-quote-toggle-icon"></span>
            
            <span class="cart-quote-label">
                <?php esc_html_e('Cart', 'cart-quote-woocommerce-email'); ?>
                <span class="cart-count-badge">(<?php echo esc_html($cart_count); ?>)</span>
            </span>
            
            <?php if ($atts['show_subtotal'] === 'true') : ?>
                <span class="cart-quote-mini-subtotal">
                    <?php echo wp_kses_post($cart_subtotal); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!$is_empty) : ?>
            <div class="cart-quote-mini-dropdown">
                <?php if (!empty($parent_items)) : ?>
                    <?php foreach ($parent_items as $parent_key => $parent) : ?>
                        <?php 
                        $product = $parent['data'];
                        $parent_id = $product->get_id();
                        $tier_items = isset($tier_items_by_parent[$parent_id]) ? $tier_items_by_parent[$parent_id] : [];
                        
                        // Calculate sum of tier prices and quantities
                        $tier_total = 0;
                        $tier_qty_sum = 0;
                        foreach ($tier_items as $tier) {
                            $tier_total += $tier['line_total'];
                            $tier_qty_sum += $tier['quantity'];
                        }
                        
                        // Parent price = tier sum if tiers exist, otherwise parent's own price
                        $parent_price = !empty($tier_items) ? $tier_total : $parent['line_total'];
                        
                        // Parent quantity = sum of tier quantities if tiers exist, otherwise parent's own quantity
                        $parent_qty = !empty($tier_items) ? $tier_qty_sum : $parent['quantity'];
                        ?>
                        
                        <div class="cart-quote-mini-item parent-item">
                            <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                            <span class="item-qty">X<?php echo esc_html($parent_qty); ?></span>
                            <span class="item-price"><?php echo wc_price($parent_price); ?></span>
                        </div>
                        
                        <?php foreach ($tier_items as $tier) : ?>
                            <?php 
                            $tier_data = $tier['tier_data'];
                            $tier_label = '';
                            
                            if (!empty($tier_data['tier_level'])) {
                                $tier_label .= esc_html__('Tier', 'cart-quote-woocommerce-email') . ' ' . esc_html($tier_data['tier_level']);
                                $tier_label .= ': ';
                            }
                            if (!empty($tier_data['description'])) {
                                $tier_label .= esc_html($tier_data['description']);
                            } elseif (!empty($tier_data['tier_name'])) {
                                $tier_label .= esc_html($tier_data['tier_name']);
                            }
                            ?>
                            
                            <div class="cart-quote-mini-item tier-item">
                                <span class="item-name">• <?php echo $tier_label; ?></span>
                                <span class="item-qty">X<?php echo esc_html($tier['quantity']); ?></span>
                                <span class="item-price"><?php echo wc_price($tier['line_total']); ?></span>
                            </div>
                        <?php endforeach; ?>
                        
                        <?php if ($parent_key < count($parent_items) - 1) : ?>
                            <div class="cart-quote-item-separator"></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : ?>
                        <?php 
                        $product = $cart_item['data'];
                        ?>
                        <div class="cart-quote-mini-item">
                            <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                            <span class="item-qty">X<?php echo esc_html($cart_item['quantity']); ?></span>
                            <span class="item-price"><?php echo wc_price($cart_item['line_total']); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <div class="cart-quote-mini-total">
                    <strong><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></strong>
                    <span class="subtotal-amount"><?php echo wp_kses_post($cart_subtotal); ?></span>
                </div>

                <div class="cart-quote-mini-actions">
                    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="cart-quote-mini-btn view-cart">
                        <?php esc_html_e('View Cart', 'cart-quote-woocommerce-email'); ?>
                    </a>
                    <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="cart-quote-mini-btn get-quote">
                        <?php esc_html_e('Get Quote', 'cart-quote-woocommerce-email'); ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
