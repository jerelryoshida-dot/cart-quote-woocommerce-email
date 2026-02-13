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
?>
<div class="cart-quote-mini-cart-container" data-nonce="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>">
    <div class="cart-quote-mini-cart">
        <div class="cart-quote-mini-toggle">
            <span class="dashicons dashicons-cart"></span>

            <?php if ($atts['show_count'] === 'true') : ?>
                <span class="cart-quote-mini-count <?php echo $is_empty ? 'cart-empty' : ''; ?>">
                    <?php echo esc_html($cart_count); ?>
                </span>
            <?php endif; ?>

            <?php if ($atts['show_subtotal'] === 'true') : ?>
                <span class="cart-quote-mini-subtotal">
                    <?php echo wp_kses_post($cart_subtotal); ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (!$is_empty) : ?>
            <div class="cart-quote-mini-dropdown">
                <ul class="cart-quote-mini-items">
                    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) : ?>
                        <?php $product = $cart_item['data']; ?>
                        <li class="cart-quote-mini-item">
                            <span class="item-name">
                                <?php echo esc_html($product->get_name()); ?>
                                <span class="item-qty">x<?php echo esc_html($cart_item['quantity']); ?></span>
                            </span>
                            <span class="item-price">
                                <?php echo wc_price($cart_item['line_total']); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="cart-quote-mini-total">
                    <strong><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></strong>
                    <?php echo wp_kses_post($cart_subtotal); ?>
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
