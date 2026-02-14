<?php
/**
 * Cart Widget for Elementor
 *
 * @package CartQuoteWooCommerce\Elementor
 * @author Jerel Yoshida
 * @since 1.0.0
 */

namespace CartQuoteWooCommerce\Elementor;

// Exit if Elementor is not active
if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

/**
 * Class Cart_Widget
 */
class Cart_Widget extends \Elementor\Widget_Base
{
    /**
     * Get widget name
     *
     * @return string
     */
    public function get_name()
    {
        return 'cart_quote_cart';
    }

    /**
     * Get widget title
     *
     * @return string
     */
    public function get_title()
    {
        return __('Cart Quote - Cart', 'cart-quote-woocommerce-email');
    }

    /**
     * Get widget icon
     *
     * @return string
     */
    public function get_icon()
    {
        return 'eicon-cart';
    }

    /**
     * Get widget categories
     *
     * @return array
     */
    public function get_categories()
    {
        return ['woocommerce', 'cart-quote'];
    }

    /**
     * Get widget keywords
     *
     * @return array
     */
    public function get_keywords()
    {
        return ['cart', 'woocommerce', 'quote', 'products'];
    }

    /**
     * Register widget controls
     *
     * @return void
     */
    protected function register_controls()
    {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_product_image',
            [
                'label' => __('Show Product Image', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_quantity_controls',
            [
                'label' => __('Show Quantity Controls', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_remove_button',
            [
                'label' => __('Show Remove Button', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_subtotal',
            [
                'label' => __('Show Subtotal', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_quote_button',
            [
                'label' => __('Show Quote Button', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'quote_button_text',
            [
                'label' => __('Quote Button Text', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Proceed to Quote', 'cart-quote-woocommerce-email'),
                'condition' => [
                    'show_quote_button' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Button
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Button Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-proceed-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-proceed-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget
     *
     * @return void
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();

        // Check if we're in Elementor editor
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        // Check cart status
        $is_empty = true;
        if (function_exists('WC') && WC()->cart) {
            $is_empty = WC()->cart->is_empty();
        }

        // In editor, show preview content even with empty cart
        if (!$is_editor && $is_empty) {
            echo '<div class="cart-quote-empty">' . 
                esc_html__('Your cart is empty.', 'cart-quote-woocommerce-email') . 
                '</div>';
            return;
        }

        // Build cart items
        $cart_items = [];
        if (!$is_empty && function_exists('WC') && WC()->cart) {
            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product = $cart_item['data'];
                $cart_items[] = [
                    'key' => $cart_item_key,
                    'product_id' => $cart_item['product_id'],
                    'name' => $product->get_name(),
                    'price' => $product->get_price(),
                    'quantity' => $cart_item['quantity'],
                    'subtotal' => $cart_item['line_subtotal'],
                    'image' => $product->get_image('thumbnail'),
                    'url' => $product->get_permalink(),
                ];
            }
        }

        // Show preview content in editor
        if ($is_editor && $is_empty) {
            $cart_items = [
                [
                    'key' => 'preview_1',
                    'product_id' => 1,
                    'name' => __('Sample Product', 'cart-quote-woocommerce-email'),
                    'price' => 99.00,
                    'quantity' => 1,
                    'subtotal' => 99.00,
                    'image' => '<img src="' . esc_url(CART_QUOTE_WC_PLUGIN_URL . 'assets/images/placeholder.png') . '" alt="Sample" width="50" height="50" style="background:#eee;">',
                    'url' => '#',
                ],
                [
                    'key' => 'preview_2',
                    'product_id' => 2,
                    'name' => __('Another Product', 'cart-quote-woocommerce-email'),
                    'price' => 149.00,
                    'quantity' => 2,
                    'subtotal' => 298.00,
                    'image' => '<img src="' . esc_url(CART_QUOTE_WC_PLUGIN_URL . 'assets/images/placeholder.png') . '" alt="Sample" width="50" height="50" style="background:#eee;">',
                    'url' => '#',
                ],
            ];
        }
        ?>
        <div class="cart-quote-widget" data-nonce="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>">
            <?php if ($is_editor && $is_empty) : ?>
                <div class="cart-quote-editor-notice" style="background:#fff3cd;padding:10px;margin-bottom:15px;border-radius:4px;font-size:12px;">
                    <?php esc_html_e('Preview Mode: Showing sample cart items for styling purposes.', 'cart-quote-woocommerce-email'); ?>
                </div>
            <?php endif; ?>
            <table class="cart-quote-table">
                <thead>
                    <tr>
                        <?php if ($settings['show_product_image'] === 'yes') : ?>
                            <th><?php esc_html_e('Product', 'cart-quote-woocommerce-email'); ?></th>
                        <?php endif; ?>
                        <th><?php esc_html_e('Name', 'cart-quote-woocommerce-email'); ?></th>
                        <th><?php esc_html_e('Price', 'cart-quote-woocommerce-email'); ?></th>
                        <?php if ($settings['show_quantity_controls'] === 'yes') : ?>
                            <th><?php esc_html_e('Quantity', 'cart-quote-woocommerce-email'); ?></th>
                        <?php endif; ?>
                        <th><?php esc_html_e('Total', 'cart-quote-woocommerce-email'); ?></th>
                        <?php if ($settings['show_remove_button'] === 'yes') : ?>
                            <th></th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cart_items as $item) : ?>
                        <tr data-cart-item-key="<?php echo esc_attr($item['key']); ?>">
                            <?php if ($settings['show_product_image'] === 'yes') : ?>
                                <td class="cart-quote-product-image">
                                    <a href="<?php echo esc_url($item['url']); ?>">
                                        <?php echo wp_kses_post($item['image']); ?>
                                    </a>
                                </td>
                            <?php endif; ?>
                            <td class="cart-quote-product-name">
                                <a href="<?php echo esc_url($item['url']); ?>">
                                    <?php echo esc_html($item['name']); ?>
                                </a>
                            </td>
                            <td class="cart-quote-product-price">
                                <?php echo function_exists('wc_price') ? wc_price($item['price']) : '$' . number_format($item['price'], 2); ?>
                            </td>
                            <?php if ($settings['show_quantity_controls'] === 'yes') : ?>
                                <td class="cart-quote-product-quantity">
                                    <div class="cart-quote-quantity-controls">
                                        <button type="button" class="cart-quote-qty-btn cart-quote-qty-minus">-</button>
                                        <input type="number" 
                                               class="cart-quote-qty-input" 
                                               value="<?php echo esc_attr($item['quantity']); ?>" 
                                               min="1" 
                                               data-cart-item-key="<?php echo esc_attr($item['key']); ?>">
                                        <button type="button" class="cart-quote-qty-btn cart-quote-qty-plus">+</button>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <td class="cart-quote-product-subtotal">
                                <?php echo function_exists('wc_price') ? wc_price($item['subtotal']) : '$' . number_format($item['subtotal'], 2); ?>
                            </td>
                            <?php if ($settings['show_remove_button'] === 'yes') : ?>
                                <td class="cart-quote-product-remove">
                                    <button type="button" class="cart-quote-remove-btn" data-cart-item-key="<?php echo esc_attr($item['key']); ?>">
                                        &times;
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if ($settings['show_subtotal'] === 'yes') : ?>
                    <tfoot>
                        <tr>
                            <td colspan="<?php echo ($settings['show_product_image'] === 'yes' ? '5' : '4') + ($settings['show_remove_button'] === 'yes' ? 1 : 0); ?>" class="cart-quote-subtotal-label">
                                <?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?>
                            </td>
                            <td class="cart-quote-subtotal-value">
                                <?php 
                                $subtotal = 0;
                                foreach ($cart_items as $item) {
                                    $subtotal += $item['subtotal'];
                                }
                                echo function_exists('wc_price') ? wc_price($subtotal) : '$' . number_format($subtotal, 2);
                                ?>
                            </td>
                        </tr>
                    </tfoot>
                <?php endif; ?>
            </table>

            <?php if ($settings['show_quote_button'] === 'yes') : ?>
                <div class="cart-quote-actions">
                    <a href="<?php echo esc_url(function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '#'); ?>" class="cart-quote-proceed-btn button">
                        <?php echo esc_html($settings['quote_button_text']); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}
