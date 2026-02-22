<?php
/**
 * Quote Form Widget for Elementor
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
 * Class Quote_Form_Widget
 */
class Quote_Form_Widget extends \Elementor\Widget_Base
{
    /**
     * Get widget name
     */
    /**
     * Get widget name
     *
     * @return string
     */
    public function get_name()
    {
        return 'cart_quote_form';
    }

    /**
     * Get widget title
     */
    public function get_title()
    {
        return __('Cart Quote - Quote Form', 'cart-quote-woocommerce-email');
    }

    /**
     * Get widget icon
     */
    public function get_icon()
    {
        return 'eicon-form-horizontal';
    }

    /**
     * Get widget categories
     */
    public function get_categories()
    {
        return ['yosh-tools'];
    }

    /**
     * Get widget keywords
     */
    public function get_keywords()
    {
        return ['form', 'quote', 'contact', 'woocommerce'];
    }

    /**
     * Register widget controls
     */
    protected function register_controls()
    {
        // Content Section - Form Fields
        $this->start_controls_section(
            'fields_section',
            [
                'label' => __('Form Fields', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'show_company',
            [
                'label' => __('Show Company', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_phone',
            [
                'label' => __('Show Phone', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_preferred_date',
            [
                'label' => __('Show Preferred Date', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_contract_duration',
            [
                'label' => __('Show Contract Duration', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_meeting_toggle',
            [
                'label' => __('Show Meeting Toggle', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'meeting_checkbox_label',
            [
                'label' => __('Meeting Checkbox Label', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Request a meeting', 'cart-quote-woocommerce-email'),
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'show_notes',
            [
                'label' => __('Show Additional Notes', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_cart_summary',
            [
                'label' => __('Show Cart Summary', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
            ]
        );

        $this->add_control(
            'show_tier_items',
            [
                'label' => __('Show Tier Items', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
                'description' => __('Display tier items under parent products with quantity controls', 'cart-quote-woocommerce-email'),
                'condition' => [
                    'show_cart_summary' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Content Section - Button
        $this->start_controls_section(
            'button_section',
            [
                'label' => __('Submit Button', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Submit Quote Request', 'cart-quote-woocommerce-email'),
            ]
        );

        $this->add_control(
            'success_message',
            [
                'label' => __('Success Message', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => __('Thank you! Your quote request has been submitted. We will contact you shortly.', 'cart-quote-woocommerce-email'),
            ]
        );

        $this->end_controls_section();

        // Style Section - Form
        $this->start_controls_section(
            'form_style_section',
            [
                'label' => __('Form Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'form_bg_color',
            [
                'label' => __('Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-form' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'form_padding',
            [
                'label' => __('Padding', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'form_border_radius',
            [
                'label' => __('Border Radius', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Style Section - Labels
        $this->start_controls_section(
            'labels_style_section',
            [
                'label' => __('Labels Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'label_color',
            [
                'label' => __('Label Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-form label' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'label_typography',
                'selector' => '{{WRAPPER}} .cart-quote-form label',
            ]
        );

        $this->end_controls_section();

        // Style Section - Inputs
        $this->start_controls_section(
            'inputs_style_section',
            [
                'label' => __('Inputs Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'input_bg_color',
            [
                'label' => __('Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-input,
                     {{WRAPPER}} .cart-quote-select,
                     {{WRAPPER}} .cart-quote-textarea' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_text_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-input,
                     {{WRAPPER}} .cart-quote-select,
                     {{WRAPPER}} .cart-quote-textarea' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_border_color',
            [
                'label' => __('Border Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-input,
                     {{WRAPPER}} .cart-quote-select,
                     {{WRAPPER}} .cart-quote-textarea' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'input_border_radius',
            [
                'label' => __('Border Radius', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-input,
                     {{WRAPPER}} .cart-quote-select,
                     {{WRAPPER}} .cart-quote-textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .cart-quote-submit-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-submit-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .cart-quote-submit-btn',
            ]
        );

        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-submit-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_padding',
            [
                'label' => __('Padding', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-submit-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_heading',
            [
                'label' => __('Hover State', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'button_hover_bg_color',
            [
                'label' => __('Hover Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-submit-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'button_hover_text_color',
            [
                'label' => __('Hover Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-submit-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Cart Summary Style Section
        $this->start_controls_section(
            'cart_summary_style_section',
            [
                'label' => __('Cart Summary Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Container
        $this->add_control(
            'cart_summary_bg_color',
            [
                'label' => __('Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-cart-summary' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'cart_summary_padding',
            [
                'label' => __('Padding', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-cart-summary' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'cart_summary_border_radius',
            [
                'label' => __('Border Radius', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-cart-summary' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        // Parent Item Style
        $this->add_control(
            'parent_item_heading',
            [
                'label' => __('Parent Item Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'parent_item_typography',
                'selector' => '{{WRAPPER}} .cart-quote-parent-item .item-name',
            ]
        );

        $this->add_control(
            'parent_item_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#1a1a1a',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-parent-item .item-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'parent_item_font_weight',
            [
                'label' => __('Font Weight', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '600',
                'options' => [
                    'normal' => __('Normal (400)', 'cart-quote-woocommerce-email'),
                    '500' => __('Medium (500)', 'cart-quote-woocommerce-email'),
                    '600' => __('Semi-Bold (600)', 'cart-quote-woocommerce-email'),
                    '700' => __('Bold (700)', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-parent-item' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'parent_item_qty_color',
            [
                'label' => __('Quantity Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#666666',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-parent-item .item-qty' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Tier Item Style
        $this->add_control(
            'tier_item_heading',
            [
                'label' => __('Tier Item Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'tier_item_typography',
                'selector' => '{{WRAPPER}} .cart-quote-tier-item .item-name',
            ]
        );

        $this->add_control(
            'tier_item_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#666666',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-tier-item .item-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'tier_item_indent',
            [
                'label' => __('Indentation', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 3,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 16,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-tier-item' => 'padding-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        // Separator Style
        $this->add_control(
            'separator_heading',
            [
                'label' => __('Separator Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'separator_color',
            [
                'label' => __('Separator Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f0f0f0',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-item-separator' => 'background: linear-gradient(to right, transparent, {{VALUE}}, transparent);',
                ],
            ]
        );

        $this->add_control(
            'separator_height',
            [
                'label' => __('Separator Height', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 5,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 1,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-item-separator' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'separator_margin',
            [
                'label' => __('Separator Spacing', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'default' => [
                    'top' => 8,
                    'right' => 0,
                    'bottom' => 8,
                    'left' => 0,
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-item-separator' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget
     */
    protected function render()
    {
        $settings = $this->get_settings_for_display();
        $time_slots = get_option('cart_quote_wc_time_slots', ['09:00', '11:00', '14:00', '16:00']);

        // Check if we're in Elementor editor
        $is_editor = \Elementor\Plugin::$instance->editor->is_edit_mode();

        // Check cart
        $is_empty = true;
        if (function_exists('WC') && WC()->cart) {
            $is_empty = WC()->cart->is_empty();
        }

        // In editor, always show the form for preview purposes
        if (!$is_editor && $is_empty) {
            echo '<div class="cart-quote-empty-cart">' . 
                esc_html__('Your cart is empty. Please add items before submitting a quote.', 'cart-quote-woocommerce-email') .
                '</div>';
            return;
        }
        ?>
        <div class="cart-quote-form-wrapper" 
             data-nonce="<?php echo esc_attr(wp_create_nonce('cart_quote_frontend_nonce')); ?>"
             data-success-message="<?php echo esc_attr($settings['success_message']); ?>">
            
            <?php if ($settings['show_cart_summary'] === 'yes') : 
                if ($is_editor) : ?>
                    <!-- Cart Summary Preview (Editor Mode) -->
                    <div class="cart-quote-cart-summary">
                        <h3><?php esc_html_e('Your Cart', 'cart-quote-woocommerce-email'); ?></h3>
                        <ul class="cart-quote-summary-items">
                            <li data-cart-item-key="preview_1">
                                <span class="item-name">
                                    <?php esc_html_e('Sample Product', 'cart-quote-woocommerce-email'); ?>
                                </span>
                                <span class="item-quantity">
                                    <button type="button" class="cart-quote-qty-btn cart-quote-qty-minus" data-cart-item-key="preview_1">-</button>
                                    <input type="number" class="cart-quote-qty-input" value="1" min="1" data-cart-item-key="preview_1" readonly>
                                    <button type="button" class="cart-quote-qty-btn cart-quote-qty-plus" data-cart-item-key="preview_1">+</button>
                                </span>
                                <span class="item-price">$99.00</span>
                                <button type="button" class="cart-quote-remove-btn" data-product-name="<?php esc_attr_e('Sample Product', 'cart-quote-woocommerce-email'); ?>" title="<?php esc_attr_e('Remove item', 'cart-quote-woocommerce-email'); ?>">
                                    <span class="cart-quote-remove-icon">×</span>
                                </button>
                            </li>
                        </ul>
                        <div class="cart-quote-summary-total">
                            <strong><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></strong>
                            <span class="cart-quote-subtotal-amount">$99.00</span>
                        </div>
                    </div>
                <?php elseif (function_exists('WC') && WC()->cart && !WC()->cart->is_empty()) : 
                    // Parent+tier grouping logic (same as mini-cart)
                    $items_by_product = [];
                    $parent_items = [];
                    $tier_items_by_parent = [];
                    
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                        $product_id = $cart_item['product_id'];
                        $items_by_product[$product_id][] = array_merge($cart_item, ['key' => $cart_item_key]);
                    }
                    
                    foreach ($items_by_product as $product_id => $items) {
                        $first_item = $items[0];
                        $product = $first_item['data'];
                        
                        $parent_item = [
                            'data' => $product,
                            'product_id' => $product_id,
                            'quantity' => 0,
                            'line_total' => 0,
                        ];
                        
                        foreach ($items as $item) {
                            $parent_item['quantity'] += $item['quantity'];
                            $parent_item['line_total'] += $item['line_total'];
                            
                            if (isset($item['tier_data'])) {
                                $tier_items_by_parent[$product_id][] = $item;
                            }
                        }
                        
                        $parent_items[] = $parent_item;
                    }
                    
                    $show_tier_items = isset($settings['show_tier_items']) && ($settings['show_tier_items'] === 'yes');
                    ?>
                    <div class="cart-quote-cart-summary">
                        <h3><?php esc_html_e('Your Cart', 'cart-quote-woocommerce-email'); ?></h3>
                        <ul class="cart-quote-summary-items">
                            <?php 
                            $parent_index = 0;
                            foreach ($parent_items as $parent_key => $parent) :
                                $product = $parent['data'];
                                $parent_id = $parent['product_id'];
                                $tier_items = isset($tier_items_by_parent[$parent_id]) ? $tier_items_by_parent[$parent_id] : [];
                            ?>
                                <!-- Parent Item -->
                                <li class="cart-quote-parent-item" data-product-id="<?php echo esc_attr($parent_id); ?>">
                                    <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                                    <span class="item-qty">X<?php echo esc_html($parent['quantity']); ?></span>
                                    <span class="item-price"><?php echo wc_price($parent['line_total']); ?></span>
                                </li>
                                
                                <?php if ($show_tier_items) : ?>
                                    <!-- Tier Items -->
                                    <?php foreach ($tier_items as $tier_item) :
                                        $tier_data = $tier_item['tier_data'];
                                        $tier_label = '';
                                        
                                        if (!empty($tier_data['tier_level'])) {
                                            $tier_label = esc_html__('Tier', 'cart-quote-woocommerce-email') . ' ' . esc_html($tier_data['tier_level']);
                                            if (!empty($tier_data['description'])) {
                                                $tier_label .= ': ' . esc_html($tier_data['description']);
                                            } elseif (!empty($tier_data['tier_name'])) {
                                                $tier_label .= ': ' . esc_html($tier_data['tier_name']);
                                            }
                                        } elseif (!empty($tier_data['description'])) {
                                            $tier_label = esc_html($tier_data['description']);
                                        } elseif (!empty($tier_data['tier_name'])) {
                                            $tier_label = esc_html($tier_data['tier_name']);
                                        }
                                    ?>
                                        <li class="cart-quote-tier-item" data-cart-item-key="<?php echo esc_attr($tier_item['key']); ?>" data-product-id="<?php echo esc_attr($parent_id); ?>">
                                            <span class="item-name">• <?php echo $tier_label; ?></span>
                                            <span class="item-quantity">
                                                <button type="button" class="cart-quote-qty-btn cart-quote-qty-minus" data-cart-item-key="<?php echo esc_attr($tier_item['key']); ?>">-</button>
                                                <input type="number" class="cart-quote-qty-input" value="<?php echo esc_attr($tier_item['quantity']); ?>" min="1" data-cart-item-key="<?php echo esc_attr($tier_item['key']); ?>">
                                                <button type="button" class="cart-quote-qty-btn cart-quote-qty-plus" data-cart-item-key="<?php echo esc_attr($tier_item['key']); ?>">+</button>
                                            </span>
                                            <span class="item-price" data-price="<?php echo esc_attr($tier_item['data']->get_price()); ?>">
                                                <?php echo wc_price($tier_item['line_total']); ?>
                                            </span>
                                            <button type="button" class="cart-quote-remove-btn" data-cart-item-key="<?php echo esc_attr($tier_item['key']); ?>" data-product-name="<?php echo esc_attr($tier_label); ?>" title="<?php esc_attr_e('Remove item', 'cart-quote-woocommerce-email'); ?>">
                                                <span class="cart-quote-remove-icon">×</span>
                                            </button>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <?php if (!empty($tier_items) && !$show_tier_items) : ?>
                                    <?php foreach ($tier_items as $tier_item) : ?>
                                        <li class="cart-quote-tier-item" data-cart-item-key="<?php echo esc_attr($tier_item['key']); ?>" style="display:none;"></li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <!-- Separator -->
                                <?php if ($parent_index < count($parent_items) - 1) : ?>
                                    <li class="cart-quote-item-separator"></li>
                                <?php endif; ?>
                                
                                <?php $parent_index++; ?>
                            <?php endforeach; ?>
                        </ul>
                        <div class="cart-quote-summary-total">
                            <strong><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></strong>
                            <span class="cart-quote-subtotal-amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <form class="cart-quote-form" id="cart-quote-form" method="post">
                <div class="cart-quote-form-row">
                    <div class="cart-quote-field">
                        <label for="billing_first_name">
                            <?php esc_html_e('First Name', 'cart-quote-woocommerce-email'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="billing_first_name" 
                               name="billing_first_name" 
                               required
                               class="cart-quote-input">
                    </div>

                    <div class="cart-quote-field">
                        <label for="billing_last_name">
                            <?php esc_html_e('Last Name', 'cart-quote-woocommerce-email'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="billing_last_name" 
                               name="billing_last_name" 
                               required
                               class="cart-quote-input">
                    </div>
                </div>

                <div class="cart-quote-form-row">
                    <div class="cart-quote-field">
                        <label for="billing_email">
                            <?php esc_html_e('Email', 'cart-quote-woocommerce-email'); ?>
                            <span class="required">*</span>
                        </label>
                        <input type="email" 
                               id="billing_email" 
                               name="billing_email" 
                               required
                               class="cart-quote-input">
                    </div>

                    <?php if ($settings['show_phone'] === 'yes') : ?>
                        <div class="cart-quote-field">
                            <label for="billing_phone">
                                <?php esc_html_e('Phone', 'cart-quote-woocommerce-email'); ?>
                                <span class="required">*</span>
                            </label>
                            <input type="tel" 
                                   id="billing_phone" 
                                   name="billing_phone" 
                                   required
                                   class="cart-quote-input">
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($settings['show_company'] === 'yes') : ?>
                    <div class="cart-quote-field cart-quote-field-wide">
                        <label for="billing_company">
                            <?php esc_html_e('Company Name', 'cart-quote-woocommerce-email'); ?>
                            <span class="optional"><?php esc_html_e('(optional)', 'cart-quote-woocommerce-email'); ?></span>
                        </label>
                        <input type="text" 
                               id="billing_company" 
                               name="billing_company" 
                               class="cart-quote-input"
                               placeholder="<?php esc_attr_e('Enter your company name', 'cart-quote-woocommerce-email'); ?>">
                    </div>
                <?php endif; ?>

                <?php if ($settings['show_contract_duration'] === 'yes') : ?>
                    <div class="cart-quote-field cart-quote-field-wide">
                        <label for="contract_duration">
                            <?php esc_html_e('Contract Duration', 'cart-quote-woocommerce-email'); ?>
                            <span class="required">*</span>
                        </label>
                        <select id="contract_duration" name="contract_duration" required class="cart-quote-select">
                            <option value=""><?php esc_html_e('Select duration', 'cart-quote-woocommerce-email'); ?></option>
                            <option value="1_month"><?php esc_html_e('1 Month', 'cart-quote-woocommerce-email'); ?></option>
                            <option value="3_months"><?php esc_html_e('3 Months', 'cart-quote-woocommerce-email'); ?></option>
                            <option value="6_months"><?php esc_html_e('6 Months', 'cart-quote-woocommerce-email'); ?></option>
                            <option value="custom"><?php esc_html_e('Custom (please specify)', 'cart-quote-woocommerce-email'); ?></option>
                        </select>
                    </div>

                    <div class="cart-quote-field cart-quote-field-wide cart-quote-custom-duration" style="display: none;">
                        <label for="custom_duration">
                            <?php esc_html_e('Custom Duration', 'cart-quote-woocommerce-email'); ?>
                        </label>
                        <input type="text" 
                               id="custom_duration" 
                               name="custom_duration" 
                               class="cart-quote-input"
                               placeholder="<?php esc_attr_e('e.g., 2 months, 1 year', 'cart-quote-woocommerce-email'); ?>">
                    </div>
                <?php endif; ?>

                <?php if ($settings['show_meeting_toggle'] === 'yes') : ?>
                    <div class="cart-quote-field cart-quote-field-checkbox" aria-required="false">
                        <label class="cart-quote-checkbox-label" for="meeting_requested">
                            <input type="checkbox" 
                                   name="meeting_requested" 
                                   id="meeting_requested" 
                                   value="1"
                                   aria-required="false">
                            <span><?php echo esc_html($settings['meeting_checkbox_label']); ?></span>
                        </label>
                        <span class="field-hint"><?php esc_html_e('Select this option to schedule a meeting', 'cart-quote-woocommerce-email'); ?></span>
                    </div>
                <?php endif; ?>

                <div class="cart-quote-meeting-fields" 
                     style="display: none;" 
                     role="region"
                     aria-labelledby="meeting_requested_label">
                    
                    <div class="cart-quote-meeting-header">
                        <h4 id="meeting_requested_label">
                            <?php esc_html_e('Meeting Details', 'cart-quote-woocommerce-email'); ?>
                        </h4>
                    </div>
                    
                    <div class="cart-quote-fields-row">
                        <?php if ($settings['show_preferred_date'] === 'yes') : ?>
                            <div class="cart-quote-field cart-quote-field-half" aria-required="false">
                                <label for="preferred_date">
                                    <?php esc_html_e('Preferred Start Date', 'cart-quote-woocommerce-email'); ?>
                                    <span class="required">*</span>
                                </label>
                                <input type="date" 
                                       id="preferred_date" 
                                       name="preferred_date" 
                                       min="<?php echo esc_attr(date('Y-m-d')); ?>"
                                       aria-required="true"
                                       aria-describedby="preferred_date_error">
                                <span id="preferred_date_error" class="sr-only"></span>
                            </div>
                        <?php endif; ?>

                        <div class="cart-quote-field cart-quote-field-half" aria-required="false">
                            <label for="preferred_time">
                                <?php esc_html_e('Preferred Meeting Time', 'cart-quote-woocommerce-email'); ?>
                            </label>
                            <select id="preferred_time" name="preferred_time" 
                                    aria-required="true"
                                    aria-describedby="preferred_time_error">
                                <option value=""><?php esc_html_e('Select a time slot', 'cart-quote-woocommerce-email'); ?></option>
                                <?php foreach ($time_slots as $slot) : ?>
                                    <option value="<?php echo esc_attr($slot); ?>">
                                        <?php echo esc_html(date_i18n(get_option('time_format'), strtotime($slot))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span id="preferred_time_error" class="sr-only"></span>
                        </div>
                    </div>
                </div>

                <?php if ($settings['show_notes'] === 'yes') : ?>
                    <div class="cart-quote-field cart-quote-field-wide">
                        <label for="additional_notes">
                            <?php esc_html_e('Additional Notes', 'cart-quote-woocommerce-email'); ?>
                        </label>
                        <textarea id="additional_notes" 
                                  name="additional_notes" 
                                  rows="4"
                                  class="cart-quote-textarea"
                                  placeholder="<?php esc_attr_e('Any additional information you\'d like to share...', 'cart-quote-woocommerce-email'); ?>"></textarea>
                    </div>
                <?php endif; ?>

                <div class="cart-quote-form-actions">
                    <button type="submit" class="cart-quote-submit-btn">
                        <?php echo esc_html($settings['button_text']); ?>
                    </button>
                </div>
            </form>

            <div class="cart-quote-form-success" style="display: none;">
                <div class="cart-quote-success-content">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <p><?php echo esc_html($settings['success_message']); ?></p>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Render widget in editor
     */
    protected function content_template()
    {
        ?>
        <#
        var timeSlots = ['09:00', '11:00', '14:00', '16:00'];
        #>
        <div class="cart-quote-form-wrapper">
            <# if (settings.show_cart_summary === 'yes') { #>
            <div class="cart-quote-cart-summary">
                <h3><?php esc_html_e('Your Cart', 'cart-quote-woocommerce-email'); ?></h3>
                <ul class="cart-quote-summary-items">
                    <!-- Parent Item Preview -->
                    <li class="cart-quote-parent-item">
                        <span class="item-name"><?php esc_html_e('Sample Product', 'cart-quote-woocommerce-email'); ?></span>
                        <span class="item-qty">X2</span>
                        <span class="item-price">$198.00</span>
                    </li>
                    <# if (settings.show_tier_items === 'yes') { #>
                    <!-- Tier Item 1 Preview -->
                    <li class="cart-quote-tier-item" data-cart-item-key="preview_tier_1">
                        <span class="item-name">• <?php esc_html_e('Tier 1: Basic', 'cart-quote-woocommerce-email'); ?></span>
                        <span class="item-quantity">
                            <button type="button" class="cart-quote-qty-btn cart-quote-qty-minus">-</button>
                            <input type="number" class="cart-quote-qty-input" value="1" min="1" readonly>
                            <button type="button" class="cart-quote-qty-btn cart-quote-qty-plus">+</button>
                        </span>
                        <span class="item-price">$99.00</span>
                        <button type="button" class="cart-quote-remove-btn" title="<?php esc_attr_e('Remove item', 'cart-quote-woocommerce-email'); ?>">
                            <span class="cart-quote-remove-icon">×</span>
                        </button>
                    </li>
                    <!-- Tier Item 2 Preview -->
                    <li class="cart-quote-tier-item" data-cart-item-key="preview_tier_2">
                        <span class="item-name">• <?php esc_html_e('Tier 2: Premium', 'cart-quote-woocommerce-email'); ?></span>
                        <span class="item-quantity">
                            <button type="button" class="cart-quote-qty-btn cart-quote-qty-minus">-</button>
                            <input type="number" class="cart-quote-qty-input" value="1" min="1" readonly>
                            <button type="button" class="cart-quote-qty-btn cart-quote-qty-plus">+</button>
                        </span>
                        <span class="item-price">$99.00</span>
                        <button type="button" class="cart-quote-remove-btn" title="<?php esc_attr_e('Remove item', 'cart-quote-woocommerce-email'); ?>">
                            <span class="cart-quote-remove-icon">×</span>
                        </button>
                    </li>
                    <# } #>
                    <!-- Separator -->
                    <li class="cart-quote-item-separator"></li>
                    <!-- Second Parent Item Preview -->
                    <li class="cart-quote-parent-item">
                        <span class="item-name"><?php esc_html_e('Another Product', 'cart-quote-woocommerce-email'); ?></span>
                        <span class="item-qty">X1</span>
                        <span class="item-price">$49.00</span>
                    </li>
                </ul>
                <div class="cart-quote-summary-total">
                    <strong><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></strong>
                    <span class="cart-quote-subtotal-amount">$247.00</span>
                </div>
            </div>
            <# } #>

            <form class="cart-quote-form" id="cart-quote-form">
                <div class="cart-quote-form-row">
                    <div class="cart-quote-field">
                        <label><?php esc_html_e('First Name', 'cart-quote-woocommerce-email'); ?> <span class="required">*</span></label>
                        <input type="text" class="cart-quote-input" placeholder="John">
                    </div>
                    <div class="cart-quote-field">
                        <label><?php esc_html_e('Last Name', 'cart-quote-woocommerce-email'); ?> <span class="required">*</span></label>
                        <input type="text" class="cart-quote-input" placeholder="Doe">
                    </div>
                </div>

                <div class="cart-quote-form-row">
                    <div class="cart-quote-field">
                        <label><?php esc_html_e('Email', 'cart-quote-woocommerce-email'); ?> <span class="required">*</span></label>
                        <input type="email" class="cart-quote-input" placeholder="john@example.com">
                    </div>
                    <# if (settings.show_phone === 'yes') { #>
                    <div class="cart-quote-field">
                        <label><?php esc_html_e('Phone', 'cart-quote-woocommerce-email'); ?> <span class="required">*</span></label>
                        <input type="tel" class="cart-quote-input" placeholder="+1 234 567 890">
                    </div>
                    <# } #>
                </div>

                <# if (settings.show_company === 'yes') { #>
                <div class="cart-quote-field cart-quote-field-wide">
                    <label><?php esc_html_e('Company Name', 'cart-quote-woocommerce-email'); ?> <span class="optional"><?php esc_html_e('(optional)', 'cart-quote-woocommerce-email'); ?></span></label>
                    <input type="text" class="cart-quote-input" placeholder="<?php esc_attr_e('Enter your company name', 'cart-quote-woocommerce-email'); ?>">
                </div>
                <# } #>

                <div class="cart-quote-form-row cart-quote-meeting-fields" style="display: none;">
                    <# if (settings.show_preferred_date === 'yes') { #>
                    <div class="cart-quote-field">
                        <label><?php esc_html_e('Preferred Start Date', 'cart-quote-woocommerce-email'); ?> <span class="required">*</span></label>
                        <input type="date" id="preferred_date" class="cart-quote-input">
                    </div>
                    <# } #>
                    <div class="cart-quote-field">
                        <label><?php esc_html_e('Preferred Meeting Time', 'cart-quote-woocommerce-email'); ?></label>
                        <select id="preferred_time" class="cart-quote-select">
                            <option><?php esc_html_e('Select a time slot', 'cart-quote-woocommerce-email'); ?></option>
                            <option>9:00 AM</option>
                            <option>11:00 AM</option>
                            <option>2:00 PM</option>
                            <option>4:00 PM</option>
                        </select>
                    </div>
                </div>

                <# if (settings.show_contract_duration === 'yes') { #>
                <div class="cart-quote-field cart-quote-field-wide">
                    <label><?php esc_html_e('Contract Duration', 'cart-quote-woocommerce-email'); ?> <span class="required">*</span></label>
                    <select class="cart-quote-select">
                        <option><?php esc_html_e('Select duration', 'cart-quote-woocommerce-email'); ?></option>
                        <option><?php esc_html_e('1 Month', 'cart-quote-woocommerce-email'); ?></option>
                        <option><?php esc_html_e('3 Months', 'cart-quote-woocommerce-email'); ?></option>
                        <option><?php esc_html_e('6 Months', 'cart-quote-woocommerce-email'); ?></option>
                        <option><?php esc_html_e('Custom (please specify)', 'cart-quote-woocommerce-email'); ?></option>
                    </select>
                </div>
                <# } #>

                <# if (settings.show_meeting_toggle === 'yes') { #>
                <div class="cart-quote-field cart-quote-field-checkbox">
                    <label class="cart-quote-checkbox-label">
                        <input type="checkbox" id="meeting_requested">
                        <span>{{{ settings.meeting_checkbox_label }}}</span>
                    </label>
                </div>
                <# } #>

                <# if (settings.show_notes === 'yes') { #>
                <div class="cart-quote-field cart-quote-field-wide">
                    <label><?php esc_html_e('Additional Notes', 'cart-quote-woocommerce-email'); ?></label>
                    <textarea class="cart-quote-textarea" rows="4" placeholder="<?php esc_attr_e('Any additional information...', 'cart-quote-woocommerce-email'); ?>"></textarea>
                </div>
                <# } #>

                <div class="cart-quote-form-actions">
                    <button type="button" class="cart-quote-submit-btn">{{{ settings.button_text }}}</button>
                </div>
            </form>
        </div>
        <?php
    }
}
