<?php
/**
 * Admin Manager Class
 *
 * Handles all admin interface functionality:
 * - Menu registration
 * - Admin pages rendering
 * - Admin AJAX handlers
 * - List tables
 * - CSV export
 *
 * @package PLUGIN_NAMESPACE\Admin
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Admin;

/**
 * Class Admin_Manager
 */
class Admin_Manager
{
    /**
     * Initialize admin functionality
     *
     * @return void
     */
    public function init(): void
    {
        // Add admin menu
        add_action('admin_menu', [$this, 'add_admin_menu']);
        
        // Add admin notices
        add_action('admin_notices', [$this, 'show_admin_notices']);
        
        // Add plugin action links
        add_filter('plugin_action_links_' . PLUGIN_PREFIX_PLUGIN_BASENAME, [$this, 'add_action_links']);
    }

    /**
     * ============================================================================
     * MENU REGISTRATION
     * ============================================================================
     */

    /**
     * Add admin menu pages
     *
     * @return void
     */
    public function add_admin_menu(): void
    {
        // Main menu page
        add_menu_page(
            __('PLUGIN_NAME', 'TEXT_DOMAIN'),           // Page title
            __('PLUGIN_NAME', 'TEXT_DOMAIN'),           // Menu title
            'manage_options',                            // Capability
            'plugin-slug',                               // Menu slug
            [$this, 'render_main_page'],                // Callback
            'dashicons-admin-generic',                   // Icon
            30                                           // Position
        );

        // Submenu: All Items
        add_submenu_page(
            'plugin-slug',                               // Parent slug
            __('All Items', 'TEXT_DOMAIN'),             // Page title
            __('All Items', 'TEXT_DOMAIN'),             // Menu title
            'manage_options',                            // Capability
            'plugin-slug',                               // Menu slug (same as parent = default)
            [$this, 'render_main_page']                 // Callback
        );

        // Submenu: Add New
        add_submenu_page(
            'plugin-slug',
            __('Add New', 'TEXT_DOMAIN'),
            __('Add New', 'TEXT_DOMAIN'),
            'manage_options',
            'plugin-slug-new',
            [$this, 'render_new_page']
        );

        // Submenu: Settings
        add_submenu_page(
            'plugin-slug',
            __('Settings', 'TEXT_DOMAIN'),
            __('Settings', 'TEXT_DOMAIN'),
            'manage_options',
            'plugin-slug-settings',
            [$this, 'render_settings_page']
        );
    }

    /**
     * ============================================================================
     * PAGE RENDERING
     * ============================================================================
     */

    /**
     * Render main page (list view)
     *
     * @return void
     */
    public function render_main_page(): void
    {
        // Get data
        $plugin = \PLUGIN_NAMESPACE\Core\Plugin::get_instance();
        $repository = $plugin->get_service('repository');

        $page = isset($_GET['paged']) ? max(1, (int) $_GET['paged']) : 1;
        $status = sanitize_text_field($_GET['status'] ?? '');
        $search = sanitize_text_field($_GET['s'] ?? '');

        $args = [
            'page'     => $page,
            'per_page' => 20,
            'status'   => $status,
            'search'   => $search,
        ];

        $items = $repository ? $repository->get_all($args) : [];
        $total = $repository ? $repository->get_total($args) : 0;
        $statistics = $repository ? $repository->get_statistics() : [];

        // Include template
        include PLUGIN_PREFIX_PLUGIN_DIR . 'templates/admin/list-page.php';
    }

    /**
     * Render new/edit page
     *
     * @return void
     */
    public function render_new_page(): void
    {
        $item = null;
        $is_edit = false;

        // Check if editing existing item
        if (isset($_GET['id'])) {
            $id = (int) $_GET['id'];
            $plugin = \PLUGIN_NAMESPACE\Core\Plugin::get_instance();
            $repository = $plugin->get_service('repository');
            $item = $repository ? $repository->find($id) : null;
            $is_edit = true;
        }

        include PLUGIN_PREFIX_PLUGIN_DIR . 'templates/admin/edit-page.php';
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings_page(): void
    {
        // Check if form submitted
        if (isset($_POST['plugin_slug_settings_submit'])) {
            check_admin_referer('plugin_slug_settings_nonce');
            $this->save_settings();
            echo '<div class="notice notice-success"><p>' 
                . esc_html__('Settings saved.', 'TEXT_DOMAIN') 
                . '</p></div>';
        }

        include PLUGIN_PREFIX_PLUGIN_DIR . 'templates/admin/settings-page.php';
    }

    /**
     * Save settings
     *
     * @return void
     */
    private function save_settings(): void
    {
        // Save each setting
        $settings = [
            'plugin_slug_option_enabled',
            'plugin_slug_option_mode',
            'plugin_slug_items_per_page',
            'plugin_slug_notification_email',
            'plugin_slug_debug_mode',
            'plugin_slug_delete_on_uninstall',
        ];

        foreach ($settings as $setting) {
            if (isset($_POST[$setting])) {
                update_option($setting, sanitize_text_field($_POST[$setting]));
            } else {
                // Handle checkboxes (not sent when unchecked)
                if (in_array($setting, ['plugin_slug_option_enabled', 'plugin_slug_debug_mode', 'plugin_slug_delete_on_uninstall'])) {
                    update_option($setting, false);
                }
            }
        }
    }

    /**
     * ============================================================================
     * ADMIN NOTICES
     * ============================================================================
     */

    /**
     * Show admin notices
     *
     * @return void
     */
    public function show_admin_notices(): void
    {
        // Show welcome notice after activation
        if (get_transient('plugin_slug_welcome_notice')) {
            delete_transient('plugin_slug_welcome_notice');
            ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <strong><?php esc_html_e('PLUGIN_NAME activated!', 'TEXT_DOMAIN'); ?></strong>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=plugin-slug-settings')); ?>">
                        <?php esc_html_e('Configure settings', 'TEXT_DOMAIN'); ?>
                    </a>
                </p>
            </div>
            <?php
        }
    }

    /**
     * ============================================================================
     * PLUGIN ACTION LINKS
     * ============================================================================
     */

    /**
     * Add action links to plugins page
     *
     * @param array<string> $links Existing links
     * @return array<string> Modified links
     */
    public function add_action_links(array $links): array
    {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('admin.php?page=plugin-slug-settings')),
            esc_html__('Settings', 'TEXT_DOMAIN')
        );

        return array_merge([$settings_link], $links);
    }

    /**
     * ============================================================================
     * AJAX HANDLERS
     * ============================================================================
     */

    /**
     * Handle status update
     *
     * @return void
     */
    public function handle_update_status(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');

        if ($id <= 0) {
            wp_send_json_error(['message' => __('Invalid ID.', 'TEXT_DOMAIN')]);
        }

        $plugin = \PLUGIN_NAMESPACE\Core\Plugin::get_instance();
        $repository = $plugin->get_service('repository');

        if (!$repository) {
            wp_send_json_error(['message' => __('System error.', 'TEXT_DOMAIN')]);
        }

        $result = $repository->update_status($id, $status);

        if ($result) {
            wp_send_json_success(['message' => __('Status updated.', 'TEXT_DOMAIN')]);
        } else {
            wp_send_json_error(['message' => __('Update failed.', 'TEXT_DOMAIN')]);
        }
    }

    /**
     * Handle delete
     *
     * @return void
     */
    public function handle_delete(): void
    {
        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            wp_send_json_error(['message' => __('Invalid ID.', 'TEXT_DOMAIN')]);
        }

        $plugin = \PLUGIN_NAMESPACE\Core\Plugin::get_instance();
        $repository = $plugin->get_service('repository');

        if (!$repository) {
            wp_send_json_error(['message' => __('System error.', 'TEXT_DOMAIN')]);
        }

        $result = $repository->delete($id);

        if ($result) {
            wp_send_json_success(['message' => __('Item deleted.', 'TEXT_DOMAIN')]);
        } else {
            wp_send_json_error(['message' => __('Delete failed.', 'TEXT_DOMAIN')]);
        }
    }

    /**
     * Handle CSV export
     *
     * @return void
     */
    public function handle_export_csv(): void
    {
        $status = sanitize_text_field($_GET['status'] ?? '');
        $date_from = sanitize_text_field($_GET['date_from'] ?? '');
        $date_to = sanitize_text_field($_GET['date_to'] ?? '');

        $plugin = \PLUGIN_NAMESPACE\Core\Plugin::get_instance();
        $repository = $plugin->get_service('repository');

        if (!$repository) {
            wp_die(__('System error.', 'TEXT_DOMAIN'));
        }

        $csv = $repository->export_csv([
            'status'    => $status,
            'date_from' => $date_from,
            'date_to'   => $date_to,
        ]);

        // Send as download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=export-' . date('Y-m-d') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $csv;
        exit;
    }
}
