<?php
/**
 * PHPUnit Bootstrap File
 *
 * Sets up the testing environment for WordPress plugin testing.
 *
 * @package CartQuoteWooCommerce\Tests
 * @since 1.0.0
 */

declare(strict_types=1);

// Define plugin constants for testing
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../../');
}

if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

if (!defined('WP_PLUGIN_DIR')) {
    define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');
}

if (!defined('CART_QUOTE_WC_VERSION')) {
    define('CART_QUOTE_WC_VERSION', '1.0.7');
}

if (!defined('CART_QUOTE_WC_PLUGIN_FILE')) {
    define('CART_QUOTE_WC_PLUGIN_FILE', dirname(__DIR__, 2) . '/plugin/cart-quote-woocommerce-email.php');
}

if (!defined('CART_QUOTE_WC_PLUGIN_DIR')) {
    define('CART_QUOTE_WC_PLUGIN_DIR', dirname(__DIR__, 2) . '/plugin/');
}

if (!defined('CART_QUOTE_WC_PLUGIN_URL')) {
    define('CART_QUOTE_WC_PLUGIN_URL', 'http://example.org/wp-content/plugins/cart-quote-woocommerce-email/');
}

if (!defined('CART_QUOTE_WC_PLUGIN_BASENAME')) {
    define('CART_QUOTE_WC_PLUGIN_BASENAME', 'cart-quote-woocommerce-email/cart-quote-woocommerce-email.php');
}

if (!defined('CART_QUOTE_WC_TABLE_SUBMISSIONS')) {
    define('CART_QUOTE_WC_TABLE_SUBMISSIONS', 'cart_quote_submissions');
}

// Load Composer autoloader
$autoload_path = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($autoload_path)) {
    require_once $autoload_path;
}

// Load WP Mock if available
if (class_exists('WP_Mock')) {
    WP_Mock::setUsePatchwork(true);
    WP_Mock::bootstrap();
}

// Define mock WordPress functions if WP Mock is not available
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field($str) {
        return htmlspecialchars(strip_tags((string)$str), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email($email) {
        return filter_var($email, FILTER_SANITIZE_EMAIL);
    }
}

if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field($str) {
        return htmlspecialchars(strip_tags((string)$str), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('sanitize_sql_orderby')) {
    function sanitize_sql_orderby($orderby) {
        if (preg_match('/^(ASC|DESC|RAND\(\))$/i', $orderby)) {
            return $orderby;
        }
        if (preg_match('/^(\w+)\s+(ASC|DESC)$/i', $orderby, $matches)) {
            return $matches[1] . ' ' . strtoupper($matches[2]);
        }
        return null;
    }
}

if (!function_exists('is_email')) {
    function is_email($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce($nonce, $action = -1) {
        return $nonce === 'valid_nonce' ? 1 : false;
    }
}

if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce($action = -1) {
        return 'test_nonce_' . md5($action);
    }
}

if (!function_exists('check_ajax_referer')) {
    function check_ajax_referer($action, $query_arg = false, $die = true) {
        return true;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can($capability) {
        global $current_user_can_result;
        return $current_user_can_result ?? false;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        global $mock_options;
        return $mock_options[$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        global $mock_options;
        $mock_options[$option] = $value;
        return true;
    }
}

if (!function_exists('add_option')) {
    function add_option($option, $value = '', $deprecated = '', $autoload = 'yes') {
        global $mock_options;
        if (!isset($mock_options[$option])) {
            $mock_options[$option] = $value;
            return true;
        }
        return false;
    }
}

if (!function_exists('delete_option')) {
    function delete_option($option) {
        global $mock_options;
        unset($mock_options[$option]);
        return true;
    }
}

if (!function_exists('get_current_user_id')) {
    function get_current_user_id() {
        return 1;
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode($data, $options = 0, $depth = 512) {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('current_time')) {
    function current_time($type, $gmt = 0) {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('wp_parse_args')) {
    function wp_parse_args($args, $defaults = []) {
        if (is_array($args)) {
            return array_merge($defaults, $args);
        }
        return $defaults;
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('esc_html')) {
    function esc_html($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url($url) {
        return filter_var($url, FILTER_SANITIZE_URL);
    }
}

if (!function_exists('wp_send_json_error')) {
    function wp_send_json_error($data = null, $status_code = null) {
        wp_send_json(['success' => false, 'data' => $data], $status_code);
    }
}

if (!function_exists('wp_send_json_success')) {
    function wp_send_json_success($data = null, $status_code = null) {
        wp_send_json(['success' => true, 'data' => $data], $status_code);
    }
}

if (!function_exists('wp_send_json')) {
    function wp_send_json($response, $status_code = null) {
        echo json_encode($response);
        exit;
    }
}

if (!function_exists('is_wp_error')) {
    function is_wp_error($thing) {
        return $thing instanceof WP_Error;
    }
}

// Mock WP_Error class
if (!class_exists('WP_Error')) {
    class WP_Error {
        private $errors = [];
        private $error_data = [];

        public function __construct($code = '', $message = '', $data = '') {
            if (!empty($code)) {
                $this->errors[$code][] = $message;
                if (!empty($data)) {
                    $this->error_data[$code] = $data;
                }
            }
        }

        public function add($code, $message, $data = '') {
            $this->errors[$code][] = $message;
            if (!empty($data)) {
                $this->error_data[$code] = $data;
            }
        }

        public function get_error_messages($code = '') {
            if (empty($code)) {
                return array_merge(...array_values($this->errors));
            }
            return $this->errors[$code] ?? [];
        }

        public function get_error_message($code = '') {
            $messages = $this->get_error_messages($code);
            return $messages[0] ?? '';
        }

        public function has_errors() {
            return !empty($this->errors);
        }
    }
}

// Mock wpdb class
if (!class_exists('wpdb')) {
    class wpdb {
        public $prefix = 'wp_';
        public $insert_id = 0;
        public $last_error = '';
        private $query_log = [];

        public function prepare($query, ...$args) {
            if (empty($args)) {
                return $query;
            }
            return vsprintf(str_replace(['%s', '%d', '%f'], ['%s', '%d', '%F'], $query), $args);
        }

        public function query($query) {
            $this->query_log[] = $query;
            return true;
        }

        public function get_var($query = null) {
            return null;
        }

        public function get_row($query = null, $output = OBJECT, $y = 0) {
            return null;
        }

        public function get_results($query = null, $output = OBJECT) {
            return [];
        }

        public function insert($table, $data, $format = null) {
            $this->insert_id = rand(1, 1000);
            return 1;
        }

        public function update($table, $data, $where, $format = null, $where_format = null) {
            return 1;
        }

        public function delete($table, $where, $where_format = null) {
            return 1;
        }

        public function esc_like($text) {
            return str_replace(['%', '_'], ['\\%', '\\_'], $text);
        }

        public function get_charset_collate() {
            return 'utf8mb4_unicode_ci';
        }
    }
}

// Initialize global $wpdb
$GLOBALS['wpdb'] = new wpdb();

// Initialize mock options array
$GLOBALS['mock_options'] = [];

// Reset function for tests
function cart_quote_test_reset() {
    global $wpdb, $mock_options, $current_user_can_result;
    $wpdb = new wpdb();
    $mock_options = [];
    $current_user_can_result = null;
}

echo "Bootstrap loaded successfully.\n";
