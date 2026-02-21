<?php
/**
 * Mini Cart Widget for Elementor
 *
 * @package CartQuoteWooCommerce\Elementor
 * @author Jerel Yoshida
 * @since 1.0.0
 */

namespace CartQuoteWooCommerce\Elementor;

use CartQuoteWooCommerce\Admin\Settings;

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
        return ['yosh-tools'];
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

        $this->add_control(
            'view_cart_button_text',
            [
                'label' => __('View Cart Button Text', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('View Cart', 'cart-quote-woocommerce-email'),
                'condition' => [
                    'show_items_list' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'show_tier_badge',
            [
                'label' => __('Show Tier Badge', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
                'condition' => [
                    'show_items_list' => 'yes',
                ],
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
                'description' => __('When disabled, tier items will not be displayed in the dropdown', 'cart-quote-woocommerce-email'),
                'condition' => [
                    'show_items_list' => 'yes',
                ],
            ]
        );

        $this->end_controls_section();

        // Icon Style Section
        $this->start_controls_section(
            'icon_style_section',
            [
                'label' => __('Icon Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'icon_size',
            [
                'label' => __('Icon Size', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 12,
                        'max' => 80,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 24,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-toggle i' => 'font-size: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .cart-quote-mini-toggle svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color',
            [
                'label' => __('Icon Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-toggle i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cart-quote-mini-toggle svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_color_hover',
            [
                'label' => __('Icon Hover Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-toggle:hover i' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .cart-quote-mini-toggle:hover svg' => 'fill: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Badge Style Section
        $this->start_controls_section(
            'badge_style_section',
            [
                'label' => __('Badge Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'badge_bg_color',
            [
                'label' => __('Badge Background', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-count' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'badge_text_color',
            [
                'label' => __('Badge Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-count' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'badge_font_size',
            [
                'label' => __('Badge Font Size', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 8,
                        'max' => 24,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 11,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-count' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'badge_border_radius',
            [
                'label' => __('Badge Border Radius', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['%', 'px'],
                'range' => [
                    '%' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 50,
                    'unit' => '%',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-count' => 'border-radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Icon Text Style Section
        $this->start_controls_section(
            'icon_text_style_section',
            [
                'label' => __('Icon Text Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_subtotal' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'icon_text_typography',
                'selector' => '{{WRAPPER}} .cart-quote-mini-subtotal',
            ]
        );

        $this->add_control(
            'icon_text_font_weight',
            [
                'label' => __('Font Weight', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '600',
                'options' => [
                    'normal' => __('Normal (400)', 'cart-quote-woocommerce-email'),
                    '500' => __('Medium (500)', 'cart-quote-woocommerce-email'),
                    '600' => __('Semi-Bold (600)', 'cart-quote-woocommerce-email'),
                    '700' => __('Bold (700)', 'cart-quote-woocommerce-email'),
                    '800' => __('Extra Bold (800)', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-subtotal' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_text_font_style',
            [
                'label' => __('Font Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'normal',
                'options' => [
                    'normal' => __('Normal', 'cart-quote-woocommerce-email'),
                    'italic' => __('Italic', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-subtotal' => 'font-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_text_line_height',
            [
                'label' => __('Line Height', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['em', 'px'],
                'range' => [
                    'em' => [
                        'min' => 0.8,
                        'max' => 2.5,
                        'step' => 0.1,
                    ],
                    'px' => [
                        'min' => 10,
                        'max' => 40,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 1.2,
                    'unit' => 'em',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-subtotal' => 'line-height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'icon_text_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-subtotal' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'icon_text_margin',
            [
                'label' => __('Margin', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-subtotal' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Container Style Section
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

        $this->add_control(
            'container_padding',
            [
                'label' => __('Padding', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-cart' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'container_border_radius',
            [
                'label' => __('Border Radius', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // View Cart Button Style Section
        $this->start_controls_section(
            'view_cart_button_style_section',
            [
                'label' => __('View Cart Button Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_items_list' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'view_cart_button_typography',
                'selector' => '{{WRAPPER}} .cart-quote-mini-btn.view-cart',
            ]
        );

        $this->add_control(
            'view_cart_button_font_size',
            [
                'label' => __('Font Size', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 36,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0.7,
                        'max' => 2.5,
                        'step' => 0.1,
                    ],
                    'rem' => [
                        'min' => 0.7,
                        'max' => 2.5,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 13,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_font_weight',
            [
                'label' => __('Font Weight', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '600',
                'options' => [
                    'normal' => __('Normal (400)', 'cart-quote-woocommerce-email'),
                    '500' => __('Medium (500)', 'cart-quote-woocommerce-email'),
                    '600' => __('Semi-Bold (600)', 'cart-quote-woocommerce-email'),
                    '700' => __('Bold (700)', 'cart-quote-woocommerce-email'),
                    '800' => __('Extra Bold (800)', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_font_style',
            [
                'label' => __('Font Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'normal',
                'options' => [
                    'normal' => __('Normal', 'cart-quote-woocommerce-email'),
                    'italic' => __('Italic', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'font-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_text_transform',
            [
                'label' => __('Text Transform', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'cart-quote-woocommerce-email'),
                    'uppercase' => __('Uppercase', 'cart-quote-woocommerce-email'),
                    'lowercase' => __('Lowercase', 'cart-quote-woocommerce-email'),
                    'capitalize' => __('Capitalize', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'text-transform: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_letter_spacing',
            [
                'label' => __('Letter Spacing', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => -2,
                        'max' => 5,
                        'step' => 0.5,
                    ],
                    'em' => [
                        'min' => -0.1,
                        'max' => 0.5,
                        'step' => 0.05,
                    ],
                ],
                'default' => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'letter-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_bg_color',
            [
                'label' => __('Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_text_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_padding',
            [
                'label' => __('Padding', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_border_radius',
            [
                'label' => __('Border Radius', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_border_width',
            [
                'label' => __('Border Width', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_border_color',
            [
                'label' => __('Border Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_border_style',
            [
                'label' => __('Border Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'cart-quote-woocommerce-email'),
                    'solid' => __('Solid', 'cart-quote-woocommerce-email'),
                    'dashed' => __('Dashed', 'cart-quote-woocommerce-email'),
                    'dotted' => __('Dotted', 'cart-quote-woocommerce-email'),
                    'double' => __('Double', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'border-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'view_cart_button_box_shadow',
                'selector' => '{{WRAPPER}} .cart-quote-mini-btn.view-cart',
            ]
        );

        $this->add_control(
            'view_cart_button_transition',
            [
                'label' => __('Transition Duration', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['s'],
                'range' => [
                    's' => [
                        'min' => 0,
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 0.2,
                    'unit' => 's',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart' => 'transition: all {{SIZE}}{{UNIT}} ease;',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_hover_heading',
            [
                'label' => __('Hover State', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'view_cart_button_hover_font_weight',
            [
                'label' => __('Hover Font Weight', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('Same as default', 'cart-quote-woocommerce-email'),
                    'normal' => __('Normal (400)', 'cart-quote-woocommerce-email'),
                    '500' => __('Medium (500)', 'cart-quote-woocommerce-email'),
                    '600' => __('Semi-Bold (600)', 'cart-quote-woocommerce-email'),
                    '700' => __('Bold (700)', 'cart-quote-woocommerce-email'),
                    '800' => __('Extra Bold (800)', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart:hover' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_hover_text_transform',
            [
                'label' => __('Hover Text Transform', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('Same as default', 'cart-quote-woocommerce-email'),
                    'none' => __('None', 'cart-quote-woocommerce-email'),
                    'uppercase' => __('Uppercase', 'cart-quote-woocommerce-email'),
                    'lowercase' => __('Lowercase', 'cart-quote-woocommerce-email'),
                    'capitalize' => __('Capitalize', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart:hover' => 'text-transform: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_hover_bg_color',
            [
                'label' => __('Hover Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_hover_text_color',
            [
                'label' => __('Hover Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'view_cart_button_hover_border_color',
            [
                'label' => __('Hover Border Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.view-cart:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'view_cart_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .cart-quote-mini-btn.view-cart:hover',
            ]
        );

        $this->end_controls_section();

        // Quote Button Style Section
        $this->start_controls_section(
            'quote_button_style_section',
            [
                'label' => __('Quote Button Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_items_list' => 'yes',
                    'show_quote_button' => 'yes',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'quote_button_typography',
                'selector' => '{{WRAPPER}} .cart-quote-mini-btn.get-quote',
            ]
        );

        $this->add_control(
            'quote_button_font_size',
            [
                'label' => __('Font Size', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em', 'rem'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 36,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0.7,
                        'max' => 2.5,
                        'step' => 0.1,
                    ],
                    'rem' => [
                        'min' => 0.7,
                        'max' => 2.5,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 13,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_font_weight',
            [
                'label' => __('Font Weight', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '600',
                'options' => [
                    'normal' => __('Normal (400)', 'cart-quote-woocommerce-email'),
                    '500' => __('Medium (500)', 'cart-quote-woocommerce-email'),
                    '600' => __('Semi-Bold (600)', 'cart-quote-woocommerce-email'),
                    '700' => __('Bold (700)', 'cart-quote-woocommerce-email'),
                    '800' => __('Extra Bold (800)', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_font_style',
            [
                'label' => __('Font Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'normal',
                'options' => [
                    'normal' => __('Normal', 'cart-quote-woocommerce-email'),
                    'italic' => __('Italic', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'font-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_text_transform',
            [
                'label' => __('Text Transform', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'cart-quote-woocommerce-email'),
                    'uppercase' => __('Uppercase', 'cart-quote-woocommerce-email'),
                    'lowercase' => __('Lowercase', 'cart-quote-woocommerce-email'),
                    'capitalize' => __('Capitalize', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'text-transform: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_letter_spacing',
            [
                'label' => __('Letter Spacing', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => -2,
                        'max' => 5,
                        'step' => 0.5,
                    ],
                    'em' => [
                        'min' => -0.1,
                        'max' => 0.5,
                        'step' => 0.05,
                    ],
                ],
                'default' => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'letter-spacing: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_bg_color',
            [
                'label' => __('Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_text_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_padding',
            [
                'label' => __('Padding', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_border_radius',
            [
                'label' => __('Border Radius', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_border_width',
            [
                'label' => __('Border Width', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 0,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'border-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_border_color',
            [
                'label' => __('Border Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_border_style',
            [
                'label' => __('Border Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none' => __('None', 'cart-quote-woocommerce-email'),
                    'solid' => __('Solid', 'cart-quote-woocommerce-email'),
                    'dashed' => __('Dashed', 'cart-quote-woocommerce-email'),
                    'dotted' => __('Dotted', 'cart-quote-woocommerce-email'),
                    'double' => __('Double', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'border-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'quote_button_box_shadow',
                'selector' => '{{WRAPPER}} .cart-quote-mini-btn.get-quote',
            ]
        );

        $this->add_control(
            'quote_button_transition',
            [
                'label' => __('Transition Duration', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['s'],
                'range' => [
                    's' => [
                        'min' => 0,
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 0.2,
                    'unit' => 's',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote' => 'transition: all {{SIZE}}{{UNIT}} ease;',
                ],
            ]
        );

        $this->add_control(
            'quote_button_hover_heading',
            [
                'label' => __('Hover State', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'quote_button_hover_font_weight',
            [
                'label' => __('Hover Font Weight', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('Same as default', 'cart-quote-woocommerce-email'),
                    'normal' => __('Normal (400)', 'cart-quote-woocommerce-email'),
                    '500' => __('Medium (500)', 'cart-quote-woocommerce-email'),
                    '600' => __('Semi-Bold (600)', 'cart-quote-woocommerce-email'),
                    '700' => __('Bold (700)', 'cart-quote-woocommerce-email'),
                    '800' => __('Extra Bold (800)', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote:hover' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_hover_text_transform',
            [
                'label' => __('Hover Text Transform', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '',
                'options' => [
                    '' => __('Same as default', 'cart-quote-woocommerce-email'),
                    'none' => __('None', 'cart-quote-woocommerce-email'),
                    'uppercase' => __('Uppercase', 'cart-quote-woocommerce-email'),
                    'lowercase' => __('Lowercase', 'cart-quote-woocommerce-email'),
                    'capitalize' => __('Capitalize', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote:hover' => 'text-transform: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_hover_bg_color',
            [
                'label' => __('Hover Background Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_hover_text_color',
            [
                'label' => __('Hover Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'quote_button_hover_border_color',
            [
                'label' => __('Hover Border Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-btn.get-quote:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'quote_button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .cart-quote-mini-btn.get-quote:hover',
            ]
        );

        $this->end_controls_section();

        // Dropdown Style Section
        $this->start_controls_section(
            'dropdown_style_section',
            [
                'label' => __('Dropdown Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_items_list' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'dropdown_bg_color',
            [
                'label' => __('Dropdown Background', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-dropdown' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dropdown_border_color',
            [
                'label' => __('Dropdown Border', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-dropdown' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'dropdown_border_radius',
            [
                'label' => __('Dropdown Border Radius', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-dropdown' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Dropdown Item Style Section
        $this->start_controls_section(
            'dropdown_item_style_section',
            [
                'label' => __('Dropdown Item Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_items_list' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'item_name_heading',
            [
                'label' => __('Item Name', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'item_name_typography',
                'selector' => '{{WRAPPER}} .cart-quote-mini-item .item-name',
            ]
        );

        $this->add_control(
            'item_name_font_weight',
            [
                'label' => __('Font Weight', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'normal',
                'options' => [
                    'normal' => __('Normal (400)', 'cart-quote-woocommerce-email'),
                    '500' => __('Medium (500)', 'cart-quote-woocommerce-email'),
                    '600' => __('Semi-Bold (600)', 'cart-quote-woocommerce-email'),
                    '700' => __('Bold (700)', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item .item-name' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_name_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item .item-name' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_qty_heading',
            [
                'label' => __('Quantity (x2)', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'item_qty_font_size',
            [
                'label' => __('Font Size', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 10,
                        'max' => 20,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0.7,
                        'max' => 1.5,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 14,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item .item-qty' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'item_qty_font_weight',
            [
                'label' => __('Font Weight', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'normal',
                'options' => [
                    'normal' => __('Normal (400)', 'cart-quote-woocommerce-email'),
                    '500' => __('Medium (500)', 'cart-quote-woocommerce-email'),
                    '600' => __('Semi-Bold (600)', 'cart-quote-woocommerce-email'),
                    '700' => __('Bold (700)', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item .item-qty' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_qty_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#999999',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item .item-qty' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_qty_margin_left',
            [
                'label' => __('Margin Left', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 20,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 1.5,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 5,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item .item-qty' => 'margin-left: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'item_price_heading',
            [
                'label' => __('Item Price', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'item_price_typography',
                'selector' => '{{WRAPPER}} .cart-quote-mini-item .item-price',
            ]
        );

        $this->add_control(
            'item_price_font_weight',
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
                    '{{WRAPPER}} .cart-quote-mini-item .item-price' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_price_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#333333',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item .item-price' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_price_alignment',
            [
                'label' => __('Alignment', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'cart-quote-woocommerce-email'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'cart-quote-woocommerce-email'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'cart-quote-woocommerce-email'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'right',
                'toggle' => false,
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item .item-price' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_separator_heading',
            [
                'label' => __('Item Separator', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'item_separator_color',
            [
                'label' => __('Separator Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#eeeeee',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item' => 'border-bottom-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_separator_width',
            [
                'label' => __('Separator Width', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 5,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 1,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item' => 'border-bottom-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'item_separator_style',
            [
                'label' => __('Separator Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => [
                    'none' => __('None', 'cart-quote-woocommerce-email'),
                    'solid' => __('Solid', 'cart-quote-woocommerce-email'),
                    'dashed' => __('Dashed', 'cart-quote-woocommerce-email'),
                    'dotted' => __('Dotted', 'cart-quote-woocommerce-email'),
                    'double' => __('Double', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-item' => 'border-bottom-style: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // Subtotal Style Section
        $this->start_controls_section(
            'subtotal_style_section',
            [
                'label' => __('Subtotal Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_items_list' => 'yes',
                ],
            ]
        );

        $this->add_control(
            'subtotal_label_heading',
            [
                'label' => __('Subtotal Label', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'subtotal_label_typography',
                'selector' => '{{WRAPPER}} .cart-quote-mini-total strong',
            ]
        );

        $this->add_control(
            'subtotal_label_font_weight',
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
                    '{{WRAPPER}} .cart-quote-mini-total strong' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'subtotal_label_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#666666',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-total strong' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'subtotal_amount_heading',
            [
                'label' => __('Subtotal Amount', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'subtotal_amount_typography',
                'selector' => '{{WRAPPER}} .cart-quote-mini-total .subtotal-amount',
            ]
        );

        $this->add_control(
            'subtotal_amount_font_size',
            [
                'label' => __('Font Size', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', 'em'],
                'range' => [
                    'px' => [
                        'min' => 14,
                        'max' => 28,
                        'step' => 1,
                    ],
                    'em' => [
                        'min' => 0.9,
                        'max' => 2,
                        'step' => 0.1,
                    ],
                ],
                'default' => [
                    'size' => 16,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-total .subtotal-amount' => 'font-size: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subtotal_amount_font_weight',
            [
                'label' => __('Font Weight', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => '700',
                'options' => [
                    'normal' => __('Normal (400)', 'cart-quote-woocommerce-email'),
                    '500' => __('Medium (500)', 'cart-quote-woocommerce-email'),
                    '600' => __('Semi-Bold (600)', 'cart-quote-woocommerce-email'),
                    '700' => __('Bold (700)', 'cart-quote-woocommerce-email'),
                    '800' => __('Extra Bold (800)', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-total .subtotal-amount' => 'font-weight: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'subtotal_amount_color',
            [
                'label' => __('Text Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#0073aa',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-total .subtotal-amount' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'subtotal_separator_heading',
            [
                'label' => __('Separator Line', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'subtotal_separator_width',
            [
                'label' => __('Separator Width', 'cart-quote-woocommerce-email'),
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
                    'size' => 2,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-total' => 'border-top-width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'subtotal_separator_style',
            [
                'label' => __('Separator Style', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'solid',
                'options' => [
                    'none' => __('None', 'cart-quote-woocommerce-email'),
                    'solid' => __('Solid', 'cart-quote-woocommerce-email'),
                    'dashed' => __('Dashed', 'cart-quote-woocommerce-email'),
                    'dotted' => __('Dotted', 'cart-quote-woocommerce-email'),
                    'double' => __('Double', 'cart-quote-woocommerce-email'),
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-total' => 'border-top-style: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'subtotal_separator_color',
            [
                'label' => __('Separator Color', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#eeeeee',
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-total' => 'border-top-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'subtotal_separator_spacing',
            [
                'label' => __('Top Spacing', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 5,
                        'max' => 40,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 15,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .cart-quote-mini-total' => 'padding-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Toggle Behavior Section
        $this->start_controls_section(
            'toggle_behavior_section',
            [
                'label' => __('Toggle Behavior', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'toggle_mode',
            [
                'label' => __('Toggle Mode', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'click',
                'options' => [
                    'click' => __('Click', 'cart-quote-woocommerce-email'),
                    'hover' => __('Hover', 'cart-quote-woocommerce-email'),
                ],
            ]
        );
        
        $this->add_control(
            'auto_close_delay',
            [
                'label' => __('Auto-Close Delay (seconds)', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => '5',
                'size_units' => ['s'],
                'range' => [
                    's' => [
                        'min' => 0,
                        'max' => 10,
                        'step' => 1,
                    ],
                ],
                'condition' => [
                    'toggle_mode' => 'click',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Empty Cart Section
        $this->start_controls_section(
            'empty_cart_style_section',
            [
                'label' => __('Empty Cart Style', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'empty_cart_text',
            [
                'label' => __('Empty Cart Text', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Your cart is empty', 'cart-quote-woocommerce-email'),
            ]
        );
        
        $this->add_control(
            'empty_cart_subtext',
            [
                'label' => __('Empty Cart Subtext', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Add items to get started', 'cart-quote-woocommerce-email'),
            ]
        );
        
        $this->end_controls_section();
        
        // Close Button Section
        $this->start_controls_section(
            'close_button_section',
            [
                'label' => __('Close Button', 'cart-quote-woocommerce-email'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'show_close_button',
            [
                'label' => __('Show Close Button', 'cart-quote-woocommerce-email'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'cart-quote-woocommerce-email'),
                'label_off' => __('No', 'cart-quote-woocommerce-email'),
                'default' => 'yes',
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
        
        // Get toggle mode setting
        $toggle_mode = isset($settings['toggle_mode']) ? $settings['toggle_mode'] : 'click';
        
        // Get empty cart settings
        $empty_cart_text = isset($settings['empty_cart_text']) ? $settings['empty_cart_text'] : __('Your cart is empty', 'cart-quote-woocommerce-email');
        $empty_cart_subtext = isset($settings['empty_cart_subtext']) ? $settings['empty_cart_subtext'] : __('Add items to get started', 'cart-quote-woocommerce-email');
        
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
                            <div class="cart-quote-mini-empty-state">
                                <div class="empty-cart-icon">
                                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M3 3h2l8 4-8 4v2a2 2 0 012 2h12a2 2 0 012-2v-2a2 2 0 01-2 2z"/>
                                                <circle cx="9" cy="9" r="2"/>
                                    </svg>
                                </div>
                                <div class="empty-cart-content">
                                    <h3><?php echo esc_html($empty_cart_text); ?></h3>
                                    <p><?php echo esc_html($empty_cart_subtext); ?></p>
                                </div>
                            </div>
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
                                <div class="cart-quote-mini-items">
                                    <?php
                                    // Group items by product_id
                                    $items_by_product = [];
                                    $parent_items = [];
                                    $tier_items_by_parent = [];

                                    if (!$is_empty) {
                                        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                                            $product_id = $cart_item['product_id'];
                                            $items_by_product[$product_id][] = $cart_item;
                                        }

                                        // Create virtual parent items with aggregated data
                                        foreach ($items_by_product as $product_id => $items) {
                                            $first_item = $items[0];
                                            $product = $first_item['data'];

                                            $parent_item = [
                                                'data'        => $product,
                                                'product_id'  => $product_id,
                                                'quantity'    => 0,
                                                'line_total'  => 0,
                                            ];

                                            // Aggregate quantities and prices (sum of all items including tiers)
                                            foreach ($items as $item) {
                                                $parent_item['quantity'] += $item['quantity'];
                                                $parent_item['line_total'] += $item['line_total'];

                                                // Track tier items separately
                                                if (isset($item['tier_data'])) {
                                                    $tier_items_by_parent[$product_id][] = $item;
                                                }
                                            }

                                            $parent_items[] = $parent_item;
                                        }
                                    }

                                    // Render parent items with tier items below
                                    if (!empty($parent_items)) {
                                        foreach ($parent_items as $parent_key => $parent) {
                                            $product = $parent['data'];
                                            $parent_id = $parent['product_id'];
                                            $tier_items = isset($tier_items_by_parent[$parent_id]) ? $tier_items_by_parent[$parent_id] : [];

                                            // Filter tier items by selected_tier
                                            $selected_tier = null;
                                            if (!empty($tier_items)) {
                                                $selected_tier = isset($tier_items[0]['selected_tier'])
                                                    ? (int) $tier_items[0]['selected_tier']
                                                    : 1;
                                            }

                                            if ($selected_tier && !empty($tier_items)) {
                                                $tier_items = array_filter($tier_items, function($item) use ($selected_tier) {
                                                    return isset($item['tier_data']['tier_level'])
                                                        && (int) $item['tier_data']['tier_level'] === $selected_tier;
                                                });
                                            }

                                            // Render parent item
                                            ?>
                                            <div class="cart-quote-mini-item parent-item">
                                                <span class="item-name"><?php echo esc_html($product->get_name()); ?></span>
                                                <span class="item-qty">X<?php echo esc_html($parent['quantity']); ?></span>
                                                <span class="item-price"><?php echo wc_price($parent['line_total']); ?></span>
                                            </div>
                                            <?php

                                            // Render tier items if enabled
                                            if ($settings['show_tier_items'] === 'yes' && !empty($tier_items)) {
                                                foreach ($tier_items as $tier) {
                                                    $tier_data = $tier['tier_data'];
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

                                                    // Render tier item
                                                    ?>
                                                    <div class="cart-quote-mini-item tier-item">
                                                        <span class="item-name">• <?php echo $tier_label; ?></span>
                                                        <span class="item-qty">X<?php echo esc_html($tier['quantity']); ?></span>
                                                        <span class="item-price"><?php echo wc_price($tier['line_total']); ?></span>
                                                    </div>
                                                    <?php
                                                }

                                                // Add separator after each parent group
                                                if ($parent_key < count($parent_items) - 1) {
                                                    ?>
                                                    <div class="cart-quote-item-separator"></div>
                                                    <?php
                                                }
                                            }
                                        }
                                    }
                                    ?>
                                </div>
                            <?php endif; ?>

                            <div class="cart-quote-mini-total">
                                <strong><?php esc_html_e('Subtotal:', 'cart-quote-woocommerce-email'); ?></strong>
                                <span class="subtotal-amount"><?php echo wp_kses_post($cart_subtotal); ?></span>
                            </div>

                            <div class="cart-quote-mini-actions">
                                <a href="<?php echo esc_url(function_exists('wc_get_cart_url') ? wc_get_cart_url() : '#'); ?>" class="cart-quote-mini-btn view-cart">
                                    <?php echo esc_html($settings['view_cart_button_text']); ?>
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
