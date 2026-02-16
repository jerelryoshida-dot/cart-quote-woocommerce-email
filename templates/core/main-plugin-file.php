<?php
/**
 * Main Plugin File Template
 * 
 * This is the entry point for your WordPress plugin. It handles:
 * - Plugin constants and version
 * - PSR-4 autoloader registration
 * - Dependency checks (WooCommerce, Elementor, etc.)
 * - Plugin initialization
 * - Activation/deactivation hooks
 * - WooCommerce feature compatibility
 *
 * @package PLUGIN_NAMESPACE
 * @author YOUR_NAME
 * @since 1.0.0
 */

/**
 * Plugin Header
 * -------------
 * WordPress reads this header to display plugin information.
 * Required fields: Plugin Name, Version
 */
/*
 * Plugin Name: PLUGIN_NAME
 * Plugin URI: https://your-website.com/plugin
 * Description: Brief description of what your plugin does.
 * Version: 1.0.0
 * Author: YOUR_NAME
 * Author URI: https://your-website.com
 * Text Domain: TEXT_DOMAIN
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ============================================================================
 * PLUGIN CONSTANTS
 * ============================================================================
 * 
 * Define all plugin constants here. These are available globally.
 * Use a consistent prefix (PLUGIN_PREFIX) to avoid conflicts.
 */

// Plugin version - MUST match header version above
define('PLUGIN_PREFIX_VERSION', '1.0.0');

// Full path to this file (used for activation hooks)
define('PLUGIN_PREFIX_PLUGIN_FILE', __FILE__);

// Directory path (e.g., /var/www/wp-content/plugins/plugin-slug/)
define('PLUGIN_PREFIX_PLUGIN_DIR', plugin_dir_path(__FILE__));

// URL to plugin directory (e.g., http://example.com/wp-content/plugins/plugin-slug/)
define('PLUGIN_PREFIX_PLUGIN_URL', plugin_dir_url(__FILE__));

// Plugin basename (e.g., plugin-slug/plugin-slug.php)
define('PLUGIN_PREFIX_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Database table name suffix (without wp_ prefix)
define('PLUGIN_PREFIX_TABLE_MAIN', 'plugin_main_table');

/**
 * ============================================================================
 * PSR-4 AUTOLOADER
 * ============================================================================
 * 
 * Automatically loads classes from the src/ directory based on namespace.
 * 
 * Namespace: PLUGIN_NAMESPACE\Core\Plugin
 * Maps to:   src/Core/Plugin.php
 */
spl_autoload_register('plugin_slug_autoloader');

function plugin_slug_autoloader($class) {
    // Define your namespace prefix
    $prefix = 'PLUGIN_NAMESPACE\\';
    $base_dir = PLUGIN_PREFIX_PLUGIN_DIR . 'src/';

    // Check if class uses our namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return; // Not our class, skip
    }

    // Get relative class name (e.g., Core\Plugin)
    $relative_class = substr($class, $len);

    // Convert namespace separators to directory separators
    // PLUGIN_NAMESPACE\Core\Plugin -> src/Core/Plugin.php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Load the file if it exists
    if (file_exists($file)) {
        require $file;
    }
}

/**
 * ============================================================================
 * DEPENDENCY CHECKS
 * ============================================================================
 * 
 * Check if required plugins are active before initializing.
 * This prevents fatal errors if dependencies are missing.
 */

/**
 * Check if WooCommerce is active
 * 
 * Checks both regular plugins and network-activated plugins.
 * 
 * @return bool
 */
function plugin_slug_check_woocommerce() {
    $active_plugins = (array) get_option('active_plugins', []);
    $network_active_plugins = (array) get_site_option('active_sitewide_plugins', []);
    
    $all_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    
    return in_array('woocommerce/woocommerce.php', $all_plugins, true) 
        || array_key_exists('woocommerce/woocommerce.php', $network_active_plugins);
}

/**
 * Check if Elementor is active
 * 
 * Only needed if your plugin includes Elementor widgets.
 * 
 * @return bool
 */
function plugin_slug_check_elementor() {
    if (!class_exists('\Elementor\Plugin')) {
        return false;
    }
    return \Elementor\Plugin::$instance->editor->is_edit_mode() 
        || class_exists('\Elementor\Widget_Base');
}

/**
 * Display admin notice if WooCommerce is missing
 * 
 * Uses WordPress admin notice system to show error message.
 */
function plugin_slug_woocommerce_missing_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php esc_html_e('PLUGIN_NAME', 'TEXT_DOMAIN'); ?></strong> 
            <?php esc_html_e('requires WooCommerce to be installed and activated.', 'TEXT_DOMAIN'); ?>
        </p>
    </div>
    <?php
}

/**
 * ============================================================================
 * PLUGIN INITIALIZATION
 * ============================================================================
 * 
 * Initialize the plugin on 'plugins_loaded' hook.
 * Priority 20 ensures WooCommerce loads first.
 */
function plugin_slug_init() {
    // Check WooCommerce dependency (uncomment if required)
    // if (!plugin_slug_check_woocommerce()) {
    //     add_action('admin_notices', 'plugin_slug_woocommerce_missing_notice');
    //     return;
    // }

    // Load translation files
    load_plugin_textdomain(
        'TEXT_DOMAIN',
        false,
        dirname(PLUGIN_PREFIX_PLUGIN_BASENAME) . '/languages'
    );

    // Initialize the main plugin class (Service Container)
    $plugin = \PLUGIN_NAMESPACE\Core\Plugin::get_instance();
    $plugin->init();
    
    // Initialize Elementor widgets if Elementor is active
    if (plugin_slug_check_elementor()) {
        add_action('elementor/widgets/register', 'plugin_slug_register_elementor_widgets');
    }
}

// Hook into plugins_loaded with priority 20 (after WooCommerce)
add_action('plugins_loaded', 'plugin_slug_init', 20);

/**
 * Register Elementor Widgets
 * 
 * Called when Elementor registers widgets.
 * Only loads if Elementor is active.
 * 
 * @param object $widgets_manager Elementor widgets manager
 */
function plugin_slug_register_elementor_widgets($widgets_manager) {
    // Only load if Elementor is properly loaded
    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }
    
    // Include widget files
    require_once PLUGIN_PREFIX_PLUGIN_DIR . 'src/Elementor/Sample_Widget.php';
    
    // Register widgets
    $widgets_manager->register(new \PLUGIN_NAMESPACE\Elementor\Sample_Widget());
}

/**
 * ============================================================================
 * ACTIVATION HOOK
 * ============================================================================
 * 
 * Runs when plugin is activated. Handles:
 * - PHP version check
 * - Database table creation
 * - Default options
 * - Cron job scheduling
 */
function plugin_slug_activate() {
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(PLUGIN_PREFIX_PLUGIN_BASENAME);
        wp_die(
            esc_html__('PLUGIN_NAME requires PHP 7.4 or higher.', 'TEXT_DOMAIN'),
            'Plugin Activation Error',
            ['back_link' => true]
        );
    }

    try {
        // Run activator (creates tables, options, cron jobs)
        $activator = new \PLUGIN_NAMESPACE\Core\Activator();
        $activator->activate();
    } catch (Throwable $e) {
        // Catch any errors during activation
        deactivate_plugins(PLUGIN_PREFIX_PLUGIN_BASENAME);
        wp_die(
            esc_html__('PLUGIN_NAME activation failed: ' . $e->getMessage(), 'TEXT_DOMAIN'),
            'Plugin Activation Error',
            ['back_link' => true]
        );
    }

    // Set activation flag for welcome redirect
    set_transient('plugin_slug_activation_redirect', true, 30);
}

// Register activation hook (use named function, not closure)
register_activation_hook(__FILE__, 'plugin_slug_activate');

/**
 * ============================================================================
 * DEACTIVATION HOOK
 * ============================================================================
 * 
 * Runs when plugin is deactivated. Handles:
 * - Cron job cleanup
 * - Transient cleanup
 * - Does NOT delete data (use uninstall.php for that)
 */
function plugin_slug_deactivate() {
    try {
        $deactivator = new \PLUGIN_NAMESPACE\Core\Deactivator();
        $deactivator->deactivate();
    } catch (Throwable $e) {
        // Log error but don't stop deactivation
        error_log('PLUGIN_NAME deactivation error: ' . $e->getMessage());
    }
}

// Register deactivation hook
register_deactivation_hook(__FILE__, 'plugin_slug_deactivate');

/**
 * ============================================================================
 * WOOCOMMERCE FEATURE COMPATIBILITY
 * ============================================================================
 * 
 * Declare compatibility with WooCommerce features to avoid warnings.
 * This fixes "incompatible with currently enabled WooCommerce features" notice.
 * 
 * Add this BEFORE WooCommerce initializes (before_woocommerce_init).
 */
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // High-Performance Order Storage (HPOS)
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            PLUGIN_PREFIX_PLUGIN_FILE,
            true
        );
        
        // Product block editor
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'product_block_editor',
            PLUGIN_PREFIX_PLUGIN_FILE,
            true
        );
        
        // Cart/Checkout blocks
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            PLUGIN_PREFIX_PLUGIN_FILE,
            true
        );
    }
});
