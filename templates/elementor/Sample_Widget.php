<?php
/**
 * Sample Elementor Widget
 *
 * Template for creating Elementor widgets with controls and rendering.
 *
 * @package PLUGIN_NAMESPACE\Elementor
 * @author YOUR_NAME
 * @since 1.0.0
 */

namespace PLUGIN_NAMESPACE\Elementor;

// Exit if Elementor is not active
if (!class_exists('\Elementor\Widget_Base')) {
    return;
}

/**
 * Class Sample_Widget
 */
class Sample_Widget extends \Elementor\Widget_Base
{
    /**
     * Get widget name
     *
     * @return string
     */
    public function get_name(): string
    {
        return 'plugin_slug_sample';
    }

    /**
     * Get widget title
     *
     * @return string
     */
    public function get_title(): string
    {
        return __('PLUGIN_NAME - Sample Widget', 'TEXT_DOMAIN');
    }

    /**
     * Get widget icon
     *
     * @return string
     */
    public function get_icon(): string
    {
        return 'eicon-form-horizontal';
    }

    /**
     * Get widget categories
     *
     * @return array<string>
     */
    public function get_categories(): array
    {
        return ['general', 'plugin-slug'];
    }

    /**
     * Get widget keywords
     *
     * @return array<string>
     */
    public function get_keywords(): array
    {
        return ['plugin', 'sample', 'widget'];
    }

    /**
     * Register widget controls
     *
     * @return void
     */
    protected function register_controls(): void
    {
        // ========================================
        // Content Section
        // ========================================
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'TEXT_DOMAIN'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        // Title control
        $this->add_control(
            'title',
            [
                'label' => __('Title', 'TEXT_DOMAIN'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Default Title', 'TEXT_DOMAIN'),
                'placeholder' => __('Enter your title', 'TEXT_DOMAIN'),
            ]
        );

        // Description control
        $this->add_control(
            'description',
            [
                'label' => __('Description', 'TEXT_DOMAIN'),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'rows' => 5,
                'default' => __('Default description text.', 'TEXT_DOMAIN'),
            ]
        );

        // Show/Hide toggle
        $this->add_control(
            'show_date',
            [
                'label' => __('Show Date', 'TEXT_DOMAIN'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'TEXT_DOMAIN'),
                'label_off' => __('No', 'TEXT_DOMAIN'),
                'default' => 'yes',
            ]
        );

        // Select control
        $this->add_control(
            'display_style',
            [
                'label' => __('Display Style', 'TEXT_DOMAIN'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'card',
                'options' => [
                    'card' => __('Card', 'TEXT_DOMAIN'),
                    'list' => __('List', 'TEXT_DOMAIN'),
                    'table' => __('Table', 'TEXT_DOMAIN'),
                ],
            ]
        );

        $this->end_controls_section();

        // ========================================
        // Style Section
        // ========================================
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'TEXT_DOMAIN'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Title color
        $this->add_control(
            'title_color',
            [
                'label' => __('Title Color', 'TEXT_DOMAIN'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .plugin-slug-widget-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        // Title typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => __('Title Typography', 'TEXT_DOMAIN'),
                'selector' => '{{WRAPPER}} .plugin-slug-widget-title',
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Render widget output
     *
     * @return void
     */
    protected function render(): void
    {
        $settings = $this->get_settings_for_display();
        ?>
        <div class="plugin-slug-widget plugin-slug-style-<?php echo esc_attr($settings['display_style']); ?>">
            <?php if (!empty($settings['title'])) : ?>
                <h3 class="plugin-slug-widget-title">
                    <?php echo esc_html($settings['title']); ?>
                </h3>
            <?php endif; ?>

            <?php if (!empty($settings['description'])) : ?>
                <div class="plugin-slug-widget-description">
                    <?php echo esc_html($settings['description']); ?>
                </div>
            <?php endif; ?>

            <?php if ($settings['show_date'] === 'yes') : ?>
                <div class="plugin-slug-widget-date">
                    <?php echo esc_html(date_i18n(get_option('date_format'))); ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Render widget output in elementor editor
     *
     * @return void
     */
    protected function content_template(): void
    {
        ?>
        <#
        var displayStyle = settings.display_style || 'card';
        #>
        <div class="plugin-slug-widget plugin-slug-style-{{ displayStyle }}">
            <# if (settings.title) { #>
                <h3 class="plugin-slug-widget-title">{{{ settings.title }}}</h3>
            <# } #>
            
            <# if (settings.description) { #>
                <div class="plugin-slug-widget-description">{{{ settings.description }}}</div>
            <# } #>
            
            <# if (settings.show_date === 'yes') { #>
                <div class="plugin-slug-widget-date">
                    <?php echo esc_html(date_i18n(get_option('date_format'))); ?>
                </div>
            <# } #>
        </div>
        <?php
    }
}
