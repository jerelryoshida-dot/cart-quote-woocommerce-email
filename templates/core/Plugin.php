<?php
/**
 * Main Plugin Class - Service Container
 *
 * This class is the heart of your plugin. It implements:
 * - Singleton pattern (single instance)
 * - Service container (dependency injection)
 * - Hook registration
 * - AJAX routing
 *
 * WHY SERVICE CONTAINER?
 * - Centralizes all plugin services
 * - Makes dependency injection easy
 * - Services are accessible from anywhere via get_service()
 * - Clean separation of concerns
 *
 * @package PLUGIN_NAMESPACE\Core
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Core;

/**
 * Class Plugin
 *
 * Main plugin class that acts as a service container
 */
final class Plugin
{
    /**
     * SINGLETON PATTERN
     * -----------------
     * Ensures only one instance of this class exists.
     * Access via Plugin::get_instance()
     *
     * @var Plugin|null
     */
    private static ?Plugin $instance = null;

    /**
     * SERVICE CONTAINER
     * -----------------
     * Stores all plugin services (Settings, Repository, Email, etc.)
     * Services are registered in register_services()
     *
     * @var array<string, object>
     */
    private array $services = [];

    /**
     * Plugin version
     * Used for cache busting CSS/JS files
     *
     * @var string
     */
    private string $version = '1.0.0';

    /**
     * Get singleton instance
     *
     * USAGE:
     *   $plugin = Plugin::get_instance();
     *   $settings = $plugin->get_service('settings');
     *
     * @return Plugin
     */
    public static function get_instance(): Plugin
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Private constructor prevents direct instantiation
     *
     * Use Plugin::get_instance() instead of new Plugin()
     */
    private function __construct()
    {
        // Intentionally empty - singleton pattern
    }

    /**
     * ============================================================================
     * INITIALIZATION
     * ============================================================================
     * 
     * Called from main plugin file after plugins_loaded hook.
     * Order matters: register → initialize → hooks
     */
    public function init(): void
    {
        // 1. Register all services (create instances)
        $this->register_services();

        // 2. Initialize services (run their init() methods)
        $this->initialize_services();

        // 3. Setup WordPress hooks (actions, filters, AJAX)
        $this->setup_hooks();
    }

    /**
     * Register all plugin services
     *
     * SERVICES EXPLAINED:
     * Each service handles a specific area of functionality:
     * - 'logger': Debug logging to wp-content/debug.log
     * - 'settings': Getting/setting plugin options
     * - 'repository': Database CRUD operations
     * - 'email': Sending emails
     * - 'admin': Admin interface
     * - 'frontend': Frontend display
     *
     * Add new services here as your plugin grows.
     */
    private function register_services(): void
    {
        // Register singleton services first (they manage themselves)
        $logger = Debug_Logger::get_instance();

        $this->services = [
            // Core services
            'logger'              => $logger,
            
            // Admin services
            'settings'            => new \PLUGIN_NAMESPACE\Admin\Settings(),
            'admin_manager'       => new \PLUGIN_NAMESPACE\Admin\Admin_Manager(),
            
            // Database
            'repository'          => new \PLUGIN_NAMESPACE\Database\Repository(),
            
            // Frontend
            'frontend_manager'    => new \PLUGIN_NAMESPACE\Frontend\Frontend_Manager(),
            
            // Optional services (uncomment as needed)
            // 'email'            => new \PLUGIN_NAMESPACE\Services\Email_Service(),
            // 'external_api'     => new \PLUGIN_NAMESPACE\Services\External_API_Service(),
            // 'rate_limiter'     => new \PLUGIN_NAMESPACE\Core\Rate_Limiter(),
            // 'cache'            => new \PLUGIN_NAMESPACE\Core\Cache_Manager(),
        ];
    }

    /**
     * Initialize all services
     *
     * Calls init() on each service that has it.
     * Services use init() to register their own hooks.
     */
    private function initialize_services(): void
    {
        foreach ($this->services as $name => $service) {
            if (method_exists($service, 'init')) {
                $service->init();
            }
        }
    }

    /**
     * ============================================================================
     * HOOK REGISTRATION
     * ============================================================================
     */
    
    private function setup_hooks(): void
    {
        // Admin redirect after activation (welcome page)
        add_action('admin_init', [$this, 'activation_redirect']);

        // Enqueue admin CSS/JS
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

        // Enqueue frontend CSS/JS
        add_action('wp_enqueue_scripts', [$this, 'enqueue_frontend_assets']);

        // ========================================
        // PUBLIC AJAX HANDLERS
        // ========================================
        // These work for both logged-in users and guests
        // wp_ajax_nopriv_ = guests, wp_ajax_ = logged-in
        
        add_action('wp_ajax_plugin_slug_public_action', [$this, 'handle_public_action']);
        add_action('wp_ajax_nopriv_plugin_slug_public_action', [$this, 'handle_public_action']);

        // ========================================
        // ADMIN AJAX HANDLERS
        // ========================================
        // These only work for logged-in users with proper capabilities
        
        add_action('wp_ajax_plugin_slug_admin_action', [$this, 'handle_admin_action']);

        // Body class (adds plugin-active class)
        add_filter('body_class', [$this, 'add_body_class']);

        // Register health check (WordPress Site Health integration)
        \PLUGIN_NAMESPACE\Admin\Health_Check::register();
    }

    /**
     * ============================================================================
     * SERVICE ACCESS
     * ============================================================================
     */
    
    /**
     * Get a service from the container
     *
     * USAGE:
     *   $settings = $plugin->get_service('settings');
     *   $value = $settings->get_some_option();
     *
     * @param string $name Service name (e.g., 'settings', 'repository')
     * @return object|null Service instance or null if not found
     */
    public function get_service(string $name): ?object
    {
        return $this->services[$name] ?? null;
    }

    /**
     * Get plugin version
     *
     * @return string
     */
    public function get_version(): string
    {
        return $this->version;
    }

    /**
     * ============================================================================
     * ACTIVATION REDIRECT
     * ============================================================================
     */
    
    /**
     * Redirect to settings page after activation
     *
     * Shows welcome/onboarding page when plugin is first activated.
     */
    public function activation_redirect(): void
    {
        if (get_transient('plugin_slug_activation_redirect')) {
            delete_transient('plugin_slug_activation_redirect');
            
            // Don't redirect if activating multiple plugins
            if (!isset($_GET['activate-multi'])) {
                wp_safe_redirect(
                    admin_url('admin.php?page=plugin-slug-settings&welcome=1')
                );
                exit;
            }
        }
    }

    /**
     * ============================================================================
     * ASSET ENQUEUEING
     * ============================================================================
     */

    /**
     * Enqueue admin assets (CSS/JS)
     *
     * Only loads on plugin admin pages to avoid conflicts.
     *
     * @param string $hook Current admin page hook suffix
     */
    public function enqueue_admin_assets(string $hook): void
    {
        // Only load on plugin pages (check for page slug)
        if (strpos($hook, 'plugin-slug') === false) {
            return;
        }

        // Enqueue CSS
        $admin_css = PLUGIN_PREFIX_PLUGIN_DIR . 'assets/css/admin.css';
        if (file_exists($admin_css)) {
            wp_enqueue_style(
                'plugin-slug-admin',
                PLUGIN_PREFIX_PLUGIN_URL . 'assets/css/admin.css',
                [],
                $this->version
            );
        }

        // Enqueue JavaScript
        $admin_js = PLUGIN_PREFIX_PLUGIN_DIR . 'assets/js/admin.js';
        if (file_exists($admin_js)) {
            wp_enqueue_script(
                'plugin-slug-admin',
                PLUGIN_PREFIX_PLUGIN_URL . 'assets/js/admin.js',
                ['jquery'],
                $this->version,
                false // Load in header for admin
            );
        }

        // Localize script (pass PHP data to JavaScript)
        wp_localize_script('plugin-slug-admin', 'pluginSlugAdmin', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('plugin_slug_admin_nonce'),
            'debug'   => (defined('WP_DEBUG') && WP_DEBUG),
            'i18n'    => [
                'saving'    => __('Saving...', 'TEXT_DOMAIN'),
                'saved'     => __('Saved!', 'TEXT_DOMAIN'),
                'error'     => __('An error occurred.', 'TEXT_DOMAIN'),
                'confirm'   => __('Are you sure?', 'TEXT_DOMAIN'),
            ],
        ]);
    }

    /**
     * Enqueue frontend assets (CSS/JS)
     *
     * Loads on all frontend pages (or conditionally if needed).
     */
    public function enqueue_frontend_assets(): void
    {
        // Enqueue CSS
        $frontend_css = PLUGIN_PREFIX_PLUGIN_DIR . 'assets/css/frontend.css';
        if (file_exists($frontend_css)) {
            wp_enqueue_style(
                'plugin-slug-frontend',
                PLUGIN_PREFIX_PLUGIN_URL . 'assets/css/frontend.css',
                [],
                $this->version
            );
        }

        // Enqueue JavaScript
        $frontend_js = PLUGIN_PREFIX_PLUGIN_DIR . 'assets/js/frontend.js';
        if (file_exists($frontend_js)) {
            wp_enqueue_script(
                'plugin-slug-frontend',
                PLUGIN_PREFIX_PLUGIN_URL . 'assets/js/frontend.js',
                ['jquery'],
                $this->version,
                true // Load in footer for better performance
            );
        }

        // Localize script
        wp_localize_script('plugin-slug-frontend', 'pluginSlugFrontend', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('plugin_slug_frontend_nonce'),
            'debug'   => defined('WP_DEBUG') && WP_DEBUG,
            'i18n'    => [
                'processing' => __('Processing...', 'TEXT_DOMAIN'),
                'success'    => __('Success!', 'TEXT_DOMAIN'),
                'error'      => __('An error occurred.', 'TEXT_DOMAIN'),
            ],
        ]);
    }

    /**
     * ============================================================================
     * AJAX HANDLERS
     * ============================================================================
     * 
     * Each AJAX handler follows this pattern:
     * 1. Verify nonce (security)
     * 2. Check capabilities (permissions)
     * 3. Sanitize input
     * 4. Call appropriate service
     * 5. Return JSON response
     */

    /**
     * Handle public AJAX action (guest-accessible)
     *
     * Example: Form submission, cart update, etc.
     */
    public function handle_public_action(): void
    {
        // 1. Verify nonce
        check_ajax_referer('plugin_slug_frontend_nonce', 'nonce');

        // 2. No capability check needed for public actions
        // (Add if this action should only work for logged-in users)

        // 3. Sanitize input
        $param = sanitize_text_field($_POST['param'] ?? '');

        // 4. Call service
        $service = $this->get_service('repository');
        $result = $service->do_something($param);

        // 5. Return response
        if ($result) {
            wp_send_json_success([
                'message' => __('Action completed!', 'TEXT_DOMAIN'),
                'data'    => $result,
            ]);
        } else {
            wp_send_json_error([
                'message' => __('Action failed.', 'TEXT_DOMAIN'),
            ]);
        }
    }

    /**
     * Handle admin AJAX action (requires login + capability)
     */
    public function handle_admin_action(): void
    {
        // 1. Verify nonce
        check_ajax_referer('plugin_slug_admin_nonce', 'nonce');

        // 2. Check capability
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Unauthorized', 'TEXT_DOMAIN'),
            ]);
        }

        // 3. Sanitize input
        $param = sanitize_text_field($_POST['param'] ?? '');

        // 4. Call service
        $service = $this->get_service('admin_manager');
        $result = $service->do_something($param);

        // 5. Return response
        wp_send_json_success([
            'message' => __('Success!', 'TEXT_DOMAIN'),
            'data'    => $result,
        ]);
    }

    /**
     * ============================================================================
     * HELPERS
     * ============================================================================
     */

    /**
     * Add body class for plugin detection
     *
     * @param array $classes Existing body classes
     * @return array
     */
    public function add_body_class(array $classes): array
    {
        $classes[] = 'plugin-slug-active';
        return $classes;
    }

    /**
     * Prevent cloning (singleton pattern)
     */
    private function __clone()
    {
        // Prevent cloning
    }

    /**
     * Prevent unserialization (singleton pattern)
     *
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
