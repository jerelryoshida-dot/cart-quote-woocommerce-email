<?php
/**
 * Mini Cart Widget for Elementor
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
 * Class Mini_Cart_Widget
 */
class Mini_Cart_Widget extends \Elementor\Widget_Base
{
    /**
     * Get widget name
     *
     * @return string
     */
    public function get_name()
    {
        return 'cart_quote_mini_cart';
    }

    /**
     * Get widget title
     *
     * @return string
     */
    public function get_title()
    {
        return __('Cart Quote - Mini Cart', 'cart-quote-woocommerce-email');
    }

    /**
     * Get widget icon
     *
     * @return string
     */
    public function get_icon()
    {
        return 'eicon-cart-medium';
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
        return ['cart', 'mini', 'woocommerce', 'quote'];
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
            'icon',
            [
                'label' => __('Icon', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::ICONS,
                'default' => [
                    'value' => 'fas fa-shopping-cart',
                    'library' => 'fa-solid',
                ],
            ]
        );

        $this->add_control(
            'show_count',
            [
                'label' => __('Show Item Count', 'cart-quote-woocommerce-email'),
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
            'show_items_list',
            [
                'label' => __('Show Items Dropdown', 'cart-quote-woocommerce-email'),
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
                'default' => __('Get Quote', 'cart-quote-woocommerce-email'),
                'condition' => [
                    'show_quote_button' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section
        $this->start_controls_section(
            'container_style_section',
            [
                'label' => __('Container Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'container_bg_color',
            [
                'label' => __('Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-cart' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'container_text_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-cart' => 'color: {{VALUE}};',
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

        $cart_count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
        $cart_subtotal = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_subtotal() : '';
        $is_empty = function_exists('WC') && WC()->cart ? WC()->cart->is_empty() : true;

        // Show preview content in editor
        if ($is_editor && $is_empty) {
            $cart_count = 3;
            $cart_subtotal = '$347.00';
        }
        ?>
        <div class="cart-quote-mini-cart-wrapper" data-nonce="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>">
            <div class="cart-quote-mini-cart">
                <div class="cart-quote-mini-toggle">
                    <?php \Elementor\Icons_Manager::render_icon($settings['icon'], ['aria-hidden' => 'true']); ?>
                    
                    <?php if ($settings['show_count'] === 'yes') : ?>
                        <span class="cart-quote-mini-count <?php echo $is_empty && !$is_editor ? 'cart-empty' : ''; ?>">
                            <?php echo esc_html($cart_count); ?>
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($settings['show_subtotal'] === 'yes') : ?>
                        <span class="cart-quote-mini-subtotal">
                            <?php echo wp_kses_post($cart_subtotal); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <?php if ($settings['show_items_list'] === 'yes') : ?>
                    <div class="cart-quote-mini-dropdown">
                        <?php if ($is_empty && !$is_editor) : ?>
                            <p class="cart-quote-mini-empty">
                                <?php esc_html_e('Your cart is empty.', 'cart-quote-woocommerce-email'); ?>
                            </p>
                        <?php else : ?>
                            <?php if ($is_editor && $is_empty) : ?>
                                <div class="cart-quote-editor-notice" style="background:#fff3cd;padding:8px;margin-bottom:10px;border-radius:4px;font-size:11px;">
                                    <?php esc_html_e('Preview Mode: Sample items shown.', 'cart-quote-woocommerce-email'); ?>
                                </div>
                                <ul class="cart-quote-mini-items">
                                    <li class="cart-quote-mini-item">
                                        <span class="item-name">
                                            <?php esc_html_e('Sample Product', 'cart-quote-woocommerce-email'); ?>
                                            <span class="item-qty">x1</span>
                                        </span>
                                        <span class="item-price">$99.00</span>
                                    </li>
                                    <li class="cart-quote-mini-item">
                                        <span class="item-name">
                                            <?php esc_html_e('Another Product', 'cart-quote-woocommerce-email'); ?>
                                            <span class="item-qty">x2</span>
                                        </span>
                                        <span class="item-price">$248.00</span>
                                    </li>
                                </ul>
                            <?php else : ?>
                                <ul class="cart-quote-mini-items">
                                    <?php 
                                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                                        $product = $cart_item['data'];
                                    ?>
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
                            <?php endif; ?>

                            <div class="cart-quote-mini-total">
                                <strong><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></strong>
                                <?php echo wp_kses_post($cart_subtotal); ?>
                            </div>

                            <div class="cart-quote-mini-actions">
                                <a href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : '#'); ?>" class="cart-quote-mini-btn view-cart">
                                    <?php esc_html_e('View Cart', 'cart-quote-woocommerce-email'); ?>
                                </a>
                                
                                <?php if ($settings['show_quote_button'] === 'yes') : ?>
                                    <a href="<?php echo esc_url(function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '#'); ?>" class="cart-quote-mini-btn get-quote">
                                        <?php echo esc_html($settings['quote_button_text']); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
