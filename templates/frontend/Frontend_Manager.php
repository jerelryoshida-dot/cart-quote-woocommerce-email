<?php
/**
 * Frontend Manager Class
 *
 * Handles all frontend functionality:
 * - Shortcode registration and rendering
 * - Asset enqueueing (CSS/JS)
 * - Template loading
 * - AJAX handlers for frontend
 *
 * @package PLUGIN_NAMESPACE\Frontend
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Frontend;

/**
 * Class Frontend_Manager
 */
class Frontend_Manager
{
    /**
     * Initialize frontend functionality
     *
     * Called from Plugin::initialize_services()
     *
     * @return void
     */
    public function init(): void
    {
        // Register shortcodes
        add_shortcode('plugin_slug_display', [$this, 'render_display_shortcode']);
        add_shortcode('plugin_slug_form', [$this, 'render_form_shortcode']);
    }

    /**
     * ============================================================================
     * SHORTCODES
     * ============================================================================
     */

    /**
     * Render display shortcode
     *
     * Usage: [plugin_slug_display id="123" show_title="true"]
     *
     * @param array<string, mixed> $atts Shortcode attributes
     * @return string Rendered HTML
     */
    public function render_display_shortcode(array $atts = []): string
    {
        // Parse attributes with defaults
        $atts = shortcode_atts([
            'id'         => 0,
            'show_title' => 'true',
            'show_date'  => 'true',
            'class'      => '',
        ], $atts);

        // Validate required attributes
        $id = (int) $atts['id'];
        if ($id <= 0) {
            return '<div class="plugin-slug-error">' 
                . esc_html__('Invalid ID provided.', 'TEXT_DOMAIN') 
                . '</div>';
        }

        // Get data from repository
        $plugin = \PLUGIN_NAMESPACE\Core\Plugin::get_instance();
        $repository = $plugin->get_service('repository');
        
        if (!$repository) {
            return '';
        }

        $item = $repository->find($id);
        
        if (!$item) {
            return '<div class="plugin-slug-error">' 
                . esc_html__('Item not found.', 'TEXT_DOMAIN') 
                . '</div>';
        }

        // Prepare data for template
        $data = [
            'item'       => $item,
            'show_title' => $atts['show_title'] === 'true',
            'show_date'  => $atts['show_date'] === 'true',
            'class'      => sanitize_html_class($atts['class']),
        ];

        // Load template
        return $this->load_template('display-item.php', $data);
    }

    /**
     * Render form shortcode
     *
     * Usage: [plugin_slug_form redirect="/thank-you"]
     *
     * @param array<string, mixed> $atts Shortcode attributes
     * @return string Rendered HTML
     */
    public function render_form_shortcode(array $atts = []): string
    {
        $atts = shortcode_atts([
            'redirect' => '',
            'class'    => '',
        ], $atts);

        // Generate nonce for form
        $nonce = wp_create_nonce('plugin_slug_frontend_nonce');

        // Prepare data for template
        $data = [
            'nonce'    => $nonce,
            'redirect' => esc_url($atts['redirect']),
            'class'    => sanitize_html_class($atts['class']),
        ];

        // Load template
        return $this->load_template('form.php', $data);
    }

    /**
     * ============================================================================
     * TEMPLATE LOADING
     * ============================================================================
     */

    /**
     * Load a template file
     *
     * Templates can be overridden by theme in:
     * /wp-content/themes/your-theme/plugin-slug/templates/
     *
     * @param string $template_name Template file name
     * @param array<string, mixed> $data Data to extract into template
     * @return string Rendered HTML
     */
    private function load_template(string $template_name, array $data = []): string
    {
        // Extract data for use in template
        extract($data);

        // Start output buffering
        ob_start();

        // Check theme for override
        $theme_template = get_stylesheet_directory() . '/plugin-slug/' . $template_name;
        
        if (file_exists($theme_template)) {
            include $theme_template;
        } else {
            // Use plugin template
            $plugin_template = PLUGIN_PREFIX_PLUGIN_DIR . 'templates/frontend/' . $template_name;
            
            if (file_exists($plugin_template)) {
                include $plugin_template;
            }
        }

        // Return buffered content
        return ob_get_clean() ?: '';
    }

    /**
     * ============================================================================
     * AJAX HANDLERS
     * ============================================================================
     * 
     * These are called from Plugin.php AJAX routing.
     * They handle frontend-specific AJAX requests.
     */

    /**
     * Handle form submission
     *
     * Called from Plugin::handle_public_action()
     *
     * @return void
     */
    public function handle_form_submit(): void
    {
        // Verify nonce
        check_ajax_referer('plugin_slug_frontend_nonce', 'nonce');

        // Sanitize input data
        $data = [
            'title'   => sanitize_text_field($_POST['title'] ?? ''),
            'content' => sanitize_textarea_field($_POST['content'] ?? ''),
        ];

        // Validate required fields
        if (empty($data['title'])) {
            wp_send_json_error([
                'message' => __('Title is required.', 'TEXT_DOMAIN'),
                'field'   => 'title',
            ]);
        }

        // Get repository and insert
        $plugin = \PLUGIN_NAMESPACE\Core\Plugin::get_instance();
        $repository = $plugin->get_service('repository');

        if (!$repository) {
            wp_send_json_error([
                'message' => __('System error. Please try again.', 'TEXT_DOMAIN'),
            ]);
        }

        $insert_id = $repository->insert($data);

        if ($insert_id) {
            // Trigger action for other code to hook into
            do_action('plugin_slug_after_submission', $insert_id, $data);

            wp_send_json_success([
                'message' => __('Submission successful!', 'TEXT_DOMAIN'),
                'id'      => $insert_id,
            ]);
        } else {
            wp_send_json_error([
                'message' => __('Failed to save. Please try again.', 'TEXT_DOMAIN'),
            ]);
        }
    }
}
