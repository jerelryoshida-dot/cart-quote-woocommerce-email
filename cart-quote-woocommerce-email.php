<?php
/**
 * Plugin Name: Cart Quote WooCommerce & Email
 * Plugin URI: https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email
 * Description: Transform WooCommerce checkout into a quote submission system with Google Calendar integration and email notifications. No payment processing required.
 * Version: 1.0.71
 * Author: Jerel Yoshida
 * Author URI: https://github.com/jerelryoshida-dot
 * Text Domain: cart-quote-woocommerce-email
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.5
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package CartQuoteWooCommerce
 * @author Jerel Yoshida
 * @company AllOutsourcing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin constants
define('CART_QUOTE_WC_VERSION', '1.0.71');
define('CART_QUOTE_WC_PLUGIN_FILE', __FILE__);
define('CART_QUOTE_WC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CART_QUOTE_WC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CART_QUOTE_WC_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('CART_QUOTE_WC_TABLE_SUBMISSIONS', 'cart_quote_submissions');

/**
 * Autoloader for PSR-4
 */
spl_autoload_register('cart_quote_wc_autoloader');

/**
 * PSR-4 Autoloader function
 *
 * @param string $class Class name
 */
function cart_quote_wc_autoloader($class) {
    // Check if the class is in our namespace
    $prefix = 'CartQuoteWooCommerce\\';
    $base_dir = CART_QUOTE_WC_PLUGIN_DIR . 'src/';

    // Does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // Get the relative class name
    $relative_class = substr($class, $len);

    // Replace namespace separators with directory separators
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // If the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
}

/**
 * Check if WooCommerce is active
 *
 * @return bool
 */
function cart_quote_wc_check_woocommerce() {
    $active_plugins = (array) get_option('active_plugins', []);
    $network_active_plugins = (array) get_site_option('active_sitewide_plugins', []);
    
    $all_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    
    return in_array('woocommerce/woocommerce.php', $all_plugins, true) 
        || array_key_exists('woocommerce/woocommerce.php', $network_active_plugins);
}

/**
 * Check if Elementor is active
 *
 * @return bool
 */
function cart_quote_wc_check_elementor() {
    $active_plugins = (array) get_option('active_plugins', []);
    $network_active_plugins = (array) get_site_option('active_sitewide_plugins', []);
    
    $all_plugins = array_merge($active_plugins, array_keys($network_active_plugins));
    
    return in_array('elementor/elementor.php', $all_plugins, true)
        || array_key_exists('elementor/elementor.php', $network_active_plugins);
}

/**
 * Display admin notice if WooCommerce is not active
 */
function cart_quote_wc_woocommerce_notice() {
    ?>
    <div class="notice notice-error">
        <p>
            <strong><?php esc_html_e('Cart Quote WooCommerce & Email', 'cart-quote-woocommerce-email'); ?></strong> 
            <?php esc_html_e('requires WooCommerce to be installed and activated.', 'cart-quote-woocommerce-email'); ?>
        </p>
    </div>
    <?php
}

/**
 * Initialize the plugin
 */
function cart_quote_wc_init() {
    // Check WooCommerce dependency
    if (!cart_quote_wc_check_woocommerce()) {
        add_action('admin_notices', 'cart_quote_wc_woocommerce_notice');
        return;
    }

    // Load plugin text domain
    load_plugin_textdomain(
        'cart-quote-woocommerce-email',
        false,
        dirname(CART_QUOTE_WC_PLUGIN_BASENAME) . '/languages'
    );

    // Initialize the plugin
    $plugin = \CartQuoteWooCommerce\Core\Plugin::get_instance();
    $plugin->init();
    
    // Initialize Elementor widgets if Elementor is active
    if (cart_quote_wc_check_elementor()) {
        add_action('elementor/elements/categories_registered', 'cart_quote_register_elementor_category');
        add_action('elementor/widgets/register', 'cart_quote_register_elementor_widgets');
    }
}

/**
 * Register custom Elementor category
 *
 * @param object $elements_manager Elementor elements manager
 */
function cart_quote_register_elementor_category($elements_manager) {
    $elements_manager->add_category(
        'yosh-tools',
        [
            'title' => __('Yosh Tools', 'cart-quote-woocommerce-email'),
            'icon'  => 'fa fa-plug',
        ]
    );
}

/**
 * Register Elementor widgets
 *
 * @param object $widgets_manager Elementor widgets manager
 */
function cart_quote_register_elementor_widgets($widgets_manager) {
    // Only load widget classes if Elementor is active
    if (!class_exists('\Elementor\Widget_Base')) {
        return;
    }
    
    require_once CART_QUOTE_WC_PLUGIN_DIR . 'src/Elementor/Cart_Widget.php';
    require_once CART_QUOTE_WC_PLUGIN_DIR . 'src/Elementor/Mini_Cart_Widget.php';
    require_once CART_QUOTE_WC_PLUGIN_DIR . 'src/Elementor/Quote_Form_Widget.php';
    
    $widgets_manager->register(new \CartQuoteWooCommerce\Elementor\Cart_Widget());
    $widgets_manager->register(new \CartQuoteWooCommerce\Elementor\Mini_Cart_Widget());
    $widgets_manager->register(new \CartQuoteWooCommerce\Elementor\Quote_Form_Widget());
}

/**
 * Plugin activation callback
 */
function cart_quote_wc_activate() {
    // Check PHP version
    if (version_compare(PHP_VERSION, '7.4', '<')) {
        deactivate_plugins(CART_QUOTE_WC_PLUGIN_BASENAME);
        wp_die(
            esc_html__('Cart Quote WooCommerce & Email requires PHP 7.4 or higher.', 'cart-quote-woocommerce-email'),
            'Plugin Activation Error',
            ['back_link' => true]
        );
    }

    try {
        // Run activator
        $activator = new \CartQuoteWooCommerce\Core\Activator();
        $activator->activate();
    } catch (Throwable $e) {
        // Catch any errors during activation
        deactivate_plugins(CART_QUOTE_WC_PLUGIN_BASENAME);
        wp_die(
            esc_html__('Cart Quote WooCommerce & Email activation failed: ' . $e->getMessage(), 'cart-quote-woocommerce-email'),
            'Plugin Activation Error',
            ['back_link' => true]
        );
    }

    // Set activation flag for redirect
    set_transient('cart_quote_wc_activation_redirect', true, 30);
}

/**
 * Plugin deactivation callback
 */
function cart_quote_wc_deactivate() {
    try {
        $deactivator = new \CartQuoteWooCommerce\Core\Deactivator();
        $deactivator->deactivate();
    } catch (Throwable $e) {
        // Log error but don't stop deactivation if deactivator fails
        error_log('Cart Quote WooCommerce & Email deactivation error: ' . $e->getMessage());
    }
}

// Hook into plugins_loaded for initialization
add_action('plugins_loaded', 'cart_quote_wc_init', 20);

// Register activation hook with named function (not closure)
register_activation_hook(__FILE__, 'cart_quote_wc_activate');

// Register deactivation hook with named function (not closure)
register_deactivation_hook(__FILE__, 'cart_quote_wc_deactivate');

/**
 * Declare WooCommerce feature compatibility
 * This fixes the "incompatible with currently enabled WooCommerce features" notice
 */
add_action('before_woocommerce_init', function() {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Declare compatibility with WooCommerce features
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            CART_QUOTE_WC_PLUGIN_FILE,
            true
        );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'product_block_editor',
            CART_QUOTE_WC_PLUGIN_FILE,
            true
        );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            CART_QUOTE_WC_PLUGIN_FILE,
            true
        );
    }
});
