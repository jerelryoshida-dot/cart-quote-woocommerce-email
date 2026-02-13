<?php
/**
 * Frontend Cart Display Template
 *
 * @package CartQuoteWooCommerce
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$cart_data = \CartQuoteWooCommerce\Frontend\Frontend_Manager::get_cart_data();
?>
<div class="cart-quote-cart-container" data-nonce="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>">
    <?php if (empty($cart_data['items'])) : ?>
        <div class="cart-quote-empty-cart">
            <?php esc_html_e('Your cart is empty.', 'cart-quote-woocommerce-email'); ?>
        </div>
    <?php else : ?>
        <table class="cart-quote-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Product', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Price', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Quantity', 'cart-quote-woocommerce-email'); ?></th>
                    <th><?php esc_html_e('Total', 'cart-quote-woocommerce-email'); ?></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_data['items'] as $item) : ?>
                    <tr data-cart-item-key="<?php echo esc_attr($item['key']); ?>">
                        <td class="cart-quote-product-info">
                            <?php if (!empty($item['image'])) : ?>
                                <span class="cart-quote-product-image">
                                    <a href="<?php echo esc_url($item['url']); ?>">
                                        <?php echo wp_kses_post($item['image']); ?>
                                    </a>
                                </span>
                            <?php endif; ?>
                            <span class="cart-quote-product-name">
                                <a href="<?php echo esc_url($item['url']); ?>">
                                    <?php echo esc_html($item['name']); ?>
                                </a>
                            </span>
                        </td>
                        <td class="cart-quote-product-price">
                            <?php echo wc_price($item['price']); ?>
                        </td>
                        <td class="cart-quote-product-quantity">
                            <div class="cart-quote-quantity-controls">
                                <button type="button" class="cart-quote-qty-btn cart-quote-qty-minus">-</button>
                                <input type="number" class="cart-quote-qty-input" value="<?php echo esc_attr($item['quantity']); ?>" min="1" data-cart-item-key="<?php echo esc_attr($item['key']); ?>">
                                <button type="button" class="cart-quote-qty-btn cart-quote-qty-plus">+</button>
                            </div>
                        </td>
                        <td class="cart-quote-product-subtotal">
                            <?php echo wc_price($item['subtotal']); ?>
                        </td>
                        <td class="cart-quote-product-remove">
                            <button type="button" class="cart-quote-remove-btn" data-cart-item-key="<?php echo esc_attr($item['key']); ?>" title="<?php esc_attr_e('Remove item', 'cart-quote-woocommerce-email'); ?>">
                                &times;
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="cart-quote-subtotal-row">
                    <td colspan="5">
                        <span class="cart-quote-subtotal-label"><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></span>
                        <span class="cart-quote-subtotal-value"><?php echo wp_kses_post($cart_data['formatted_subtotal']); ?></span>
                    </td>
                </tr>
            </tfoot>
        </table>

        <?php if ($atts['show_button'] === 'true') : ?>
            <div class="cart-quote-actions">
                <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="cart-quote-proceed-btn button">
                    <?php esc_html_e('Proceed to Quote', 'cart-quote-woocommerce-email'); ?>
                </a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
