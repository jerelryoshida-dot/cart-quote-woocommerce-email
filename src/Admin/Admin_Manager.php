<?php
/**
 * Admin Manager
 *
 * Handles all admin dashboard functionality including menu creation,
 * quote list table, and admin AJAX handlers.
 *
 * @package CartQuoteWooCommerce\Admin
 * @author Jerel Yoshida
 * @since 1.0.0
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Admin;

use CartQuoteWooCommerce\Database\Quote_Repository;
use CartQuoteWooCommerce\Core\Debug_Logger;

class Admin_Manager
{
    private Quote_Repository $repository;

    private Debug_Logger $logger;

    public function __construct()
    {
        $this->repository = new Quote_Repository();
        $this->logger = Debug_Logger::get_instance();
    }

    /**
     * Initialize admin functionality
     *
     * @return void
     */
    public function init(): void
    {
        add_action('admin_menu', [$this, 'add_menu_pages']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_filter('set-screen-option', [$this, 'set_screen_option'], 10, 3);
        add_action('admin_notices', [$this, 'show_welcome_notice']);
    }

    /**
     * Add menu pages
     *
     * @return void
     */
    public function add_menu_pages(): void
    {
        // Main menu
        $hook = add_menu_page(
            __('Cart Quote Manager', 'cart-quote-woocommerce-email'),
            __('Cart Quotes', 'cart-quote-woocommerce-email'),
            'manage_woocommerce',
            'cart-quote-manager',
            [$this, 'render_quotes_page'],
            'dashicons-cart',
            30
        );

        add_action("load-{$hook}", [$this, 'add_screen_options']);

        // Submenu: All Quotes
        add_submenu_page(
            'cart-quote-manager',
            __('All Quotes', 'cart-quote-woocommerce-email'),
            __('All Quotes', 'cart-quote-woocommerce-email'),
            'manage_woocommerce',
            'cart-quote-manager',
            [$this, 'render_quotes_page']
        );

        // Submenu: Settings
        add_submenu_page(
            'cart-quote-manager',
            __('Settings', 'cart-quote-woocommerce-email'),
            __('Settings', 'cart-quote-woocommerce-email'),
            'manage_options',
            'cart-quote-settings',
            [$this, 'render_settings_page']
        );

        // Submenu: Google Calendar
        add_submenu_page(
            'cart-quote-manager',
            __('Google Calendar', 'cart-quote-woocommerce-email'),
            __('Google Calendar', 'cart-quote-woocommerce-email'),
            'manage_options',
            'cart-quote-google',
            [$this, 'render_google_page']
        );
    }

    /**
     * Add screen options
     *
     * @return void
     */
    public function add_screen_options(): void
    {
        $option = 'per_page';
        $args = [
            'label' => __('Quotes per page', 'cart-quote-woocommerce-email'),
            'default' => 20,
            'option' => 'cart_quotes_per_page',
        ];
        add_screen_option($option, $args);
    }

    /**
     * Set screen option
     *
     * @param mixed $status Status
     * @param string $option Option name
     * @param mixed $value Option value
     * @return mixed
     */
    public function set_screen_option($status, string $option, $value)
    {
        if ($option === 'cart_quotes_per_page') {
            return $value;
        }
        return $status;
    }

    /**
     * Enqueue admin assets
     *
     * @param string $hook Current admin page hook
     * @return void
     */
    public function enqueue_assets(string $hook): void
    {
        // Already handled in Plugin class, but can add page-specific assets here
    }

    /**
     * Show welcome notice after activation
     *
     * @return void
     */
    public function show_welcome_notice(): void
    {
        if (!isset($_GET['welcome']) || $_GET['welcome'] !== '1') {
            return;
        }

        $screen = get_current_screen();
        if ($screen->id !== 'toplevel_page_cart-quote-manager') {
            return;
        }
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong><?php esc_html_e('Welcome to Cart Quote WooCommerce & Email!', 'cart-quote-woocommerce-email'); ?></strong>
            </p>
            <p>
                <?php esc_html_e('Thank you for installing. Configure your settings to get started.', 'cart-quote-woocommerce-email'); ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=cart-quote-settings')); ?>">
                    <?php esc_html_e('Go to Settings', 'cart-quote-woocommerce-email'); ?>
                </a>
            </p>
        </div>
        <?php
    }

    /**
     * Render quotes page
     *
     * @return void
     */
    public function render_quotes_page(): void
    {
        // Handle actions
        $this->handle_page_actions();

        // Get query args
        $status = sanitize_text_field($_GET['status'] ?? '');
        $search = sanitize_text_field($_GET['s'] ?? '');
        $paged = max(1, (int) ($_GET['paged'] ?? 1));
        $per_page = (int) get_user_option('cart_quotes_per_page', get_current_user_id()) ?: 20;

        // Get quotes
        $quotes = $this->repository->get_all([
            'status' => $status,
            'search' => $search,
            'page' => $paged,
            'per_page' => $per_page,
        ]);

        $total = $this->repository->get_total([
            'status' => $status,
            'search' => $search,
        ]);

        $stats = $this->repository->get_statistics();

        // Include template
        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/admin/quotes-list.php';
    }

    /**
     * Handle page actions
     *
     * @return void
     */
    private function handle_page_actions(): void
    {
        // Handle view quote
        if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
            $this->render_quote_detail((int) $_GET['id']);
            exit;
        }
    }

    /**
     * Render quote detail page
     *
     * @param int $id Quote ID
     * @return void
     */
    private function render_quote_detail(int $id): void
    {
        $quote = $this->repository->find($id);
        
        if (!$quote) {
            wp_die(__('Quote not found.', 'cart-quote-woocommerce-email'));
        }

        $logs = $this->repository->get_logs($quote->quote_id);
        
        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/admin/quote-detail.php';
    }

    /**
     * Render settings page
     *
     * @return void
     */
    public function render_settings_page(): void
    {
        // Handle form submission
        if (isset($_POST['cart_quote_settings_nonce']) && wp_verify_nonce($_POST['cart_quote_settings_nonce'], 'cart_quote_settings')) {
            $this->save_settings();
            echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Settings saved.', 'cart-quote-woocommerce-email') . '</p></div>';
        }

        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/admin/settings.php';
    }

    /**
     * Save settings
     *
     * @return void
     */
    private function save_settings(): void
    {
        // General settings
        if (isset($_POST['quote_prefix'])) {
            update_option('cart_quote_wc_quote_prefix', sanitize_text_field($_POST['quote_prefix']));
        }
        if (isset($_POST['quote_start_number'])) {
            update_option('cart_quote_wc_quote_start_number', (int) $_POST['quote_start_number']);
        }

        // Email settings
        if (isset($_POST['send_to_admin'])) {
            update_option('cart_quote_wc_send_to_admin', (bool) $_POST['send_to_admin']);
        }
        if (isset($_POST['send_to_client'])) {
            update_option('cart_quote_wc_send_to_client', (bool) $_POST['send_to_client']);
        }
        if (isset($_POST['admin_email'])) {
            update_option('cart_quote_wc_admin_email', sanitize_email($_POST['admin_email']));
        }
        if (isset($_POST['email_subject_admin'])) {
            update_option('cart_quote_wc_email_subject_admin', sanitize_text_field($_POST['email_subject_admin']));
        }
        if (isset($_POST['email_subject_client'])) {
            update_option('cart_quote_wc_email_subject_client', sanitize_text_field($_POST['email_subject_client']));
        }
        update_option('cart_quote_wc_enable_pdf', isset($_POST['enable_pdf']));

        // Time slot settings
        if (isset($_POST['meeting_duration'])) {
            update_option('cart_quote_wc_meeting_duration', sanitize_text_field($_POST['meeting_duration']));
        }
        if (isset($_POST['time_slots'])) {
            $time_slots = array_map('sanitize_text_field', (array) $_POST['time_slots']);
            $time_slots = array_filter($time_slots); // Remove empty
            update_option('cart_quote_wc_time_slots', $time_slots);
        }

        // Status settings
        update_option('cart_quote_wc_auto_create_event', isset($_POST['auto_create_event']));
        update_option('cart_quote_wc_delete_on_uninstall', isset($_POST['delete_on_uninstall']));
    }

    /**
     * Render Google Calendar page
     *
     * @return void
     */
    public function render_google_page(): void
    {
        include CART_QUOTE_WC_PLUGIN_DIR . 'templates/admin/google-calendar.php';
    }

    /**
     * Handle update status AJAX
     *
     * @return void
     */
    public function handle_update_status(): void
    {
        try {
            $id = (int) ($_POST['id'] ?? 0);
            $status = sanitize_text_field($_POST['status'] ?? '');

            if (!$id || !$status) {
                wp_send_json_error(['message' => __('Invalid parameters.', 'cart-quote-woocommerce-email')]);
            }

            $valid_statuses = ['pending', 'contacted', 'closed', 'canceled'];
            if (!in_array($status, $valid_statuses, true)) {
                $this->logger->warning('Invalid status update requested', [
                    'id' => $id,
                    'status' => $status,
                ]);
                wp_send_json_error(['message' => __('Invalid status.', 'cart-quote-woocommerce-email')]);
            }

            $result = $this->repository->update_status($id, $status);

            if ($result) {
                $quote = $this->repository->find($id);

                // If status is contacted and meeting was requested, potentially create event
                if ($status === 'contacted' && get_option('cart_quote_wc_auto_create_event')) {
                    if ($quote && $quote->meeting_requested && !$quote->calendar_synced) {
                        do_action('cart_quote_auto_create_event', $quote);
                    }
                }

                $this->logger->info('Status updated', [
                    'quote_id' => $quote->quote_id ?? $id,
                    'status' => $status,
                ]);

                wp_send_json_success([
                    'message' => __('Status updated successfully.', 'cart-quote-woocommerce-email'),
                    'new_status' => $status,
                ]);
            } else {
                $this->logger->error('Failed to update status', [
                    'id' => $id,
                    'status' => $status,
                ]);
                wp_send_json_error(['message' => __('Failed to update status.', 'cart-quote-woocommerce-email')]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception in handle_update_status', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            wp_send_json_error(['message' => __('An error occurred.', 'cart-quote-woocommerce-email')]);
        }
    }

    /**
     * Handle save notes AJAX
     *
     * @return void
     */
    public function handle_save_notes(): void
    {
        try {
            $id = (int) ($_POST['id'] ?? 0);
            $notes = sanitize_textarea_field($_POST['notes'] ?? '');

            if (!$id) {
                wp_send_json_error(['message' => __('Invalid quote ID.', 'cart-quote-woocommerce-email')]);
            }

            $result = $this->repository->update($id, ['admin_notes' => $notes]);

            if ($result) {
                $this->logger->info('Admin notes saved', [
                    'quote_id' => $id,
                    'notes_length' => strlen($notes),
                ]);
                wp_send_json_success(['message' => __('Notes saved.', 'cart-quote-woocommerce-email')]);
            } else {
                $this->logger->error('Failed to save notes', ['id' => $id]);
                wp_send_json_error(['message' => __('Failed to save notes.', 'cart-quote-woocommerce-email')]);
            }
        } catch (\Exception $e) {
            $this->logger->error('Exception in handle_save_notes', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            wp_send_json_error(['message' => __('An error occurred.', 'cart-quote-woocommerce-email')]);
        }
    }

    /**
     * Handle export CSV AJAX
     *
     * @return void
     */
    public function handle_export_csv(): void
    {
        try {
            $status = sanitize_text_field($_GET['status'] ?? '');
            $date_from = sanitize_text_field($_GET['date_from'] ?? '');
            $date_to = sanitize_text_field($_GET['date_to'] ?? '');

            $csv = $this->repository->export_csv([
                'status' => $status,
                'date_from' => $date_from,
                'date_to' => $date_to,
            ]);

            if ($csv === false) {
                $this->logger->error('CSV export failed: repository error');
                wp_send_json_error(['message' => __('Failed to generate CSV.', 'cart-quote-woocommerce-email')]);
            }

            $filename = 'quotes-' . date('Y-m-d-His') . '.csv';

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $csv;
            exit;
        } catch (\Exception $e) {
            $this->logger->error('Exception in handle_export_csv', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            wp_send_json_error(['message' => __('An error occurred.', 'cart-quote-woocommerce-email')]);
        }
    }

    /**
     * Get status label
     *
     * @param string $status Status
     * @return string
     */
    public static function get_status_label(string $status): string
    {
        $labels = [
            'pending' => __('Pending', 'cart-quote-woocommerce-email'),
            'contacted' => __('Contacted', 'cart-quote-woocommerce-email'),
            'closed' => __('Closed', 'cart-quote-woocommerce-email'),
            'canceled' => __('Canceled', 'cart-quote-woocommerce-email'),
        ];

        return $labels[$status] ?? $status;
    }

    /**
     * Get status class
     *
     * @param string $status Status
     * @return string
     */
    public static function get_status_class(string $status): string
    {
        $classes = [
            'pending' => 'status-pending',
            'contacted' => 'status-contacted',
            'closed' => 'status-closed',
            'canceled' => 'status-canceled',
        ];

        return $classes[$status] ?? '';
    }
}
