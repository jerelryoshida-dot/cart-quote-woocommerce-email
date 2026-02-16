# AI Plugin Builder Guide

> **Master knowledge base for building professional WordPress plugins.**
> Use this as a reference for patterns, best practices, and code examples.

**Generated:** 2026-02-17 | **Version:** 1.0.0

---

## Table of Contents

1. [Project Structure](#1-project-structure)
2. [Core Architecture](#2-core-architecture)
3. [Database](#3-database)
4. [Admin Interface](#4-admin-interface)
5. [Frontend](#5-frontend)
6. [AJAX](#6-ajax)
7. [Security](#7-security)
8. [Services](#8-services)
9. [Testing](#9-testing)
10. [Build & Deploy](#10-build--deploy)

---

## 1. Project Structure

### Standard Layout

```
plugin-slug/
├── plugin-slug.php          # Main file (constants, autoloader, init)
├── readme.txt               # WordPress.org format
├── uninstall.php            # Clean uninstall handler
├── src/
│   ├── Core/
│   │   ├── Plugin.php       # Service container, singleton
│   │   ├── Activator.php    # DB tables, options, cron
│   │   ├── Deactivator.php  # Cron cleanup
│   │   ├── Uninstaller.php  # Data cleanup
│   │   └── Debug_Logger.php # Centralized logging
│   ├── Admin/
│   │   ├── Admin_Manager.php
│   │   └── Settings.php
│   ├── Database/
│   │   └── Repository.php
│   ├── Frontend/
│   │   └── Frontend_Manager.php
│   ├── Services/
│   │   ├── Email_Service.php
│   │   └── External_API_Service.php
│   └── WooCommerce/         # Optional
│       └── WooCommerce_Integration.php
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
└── templates/
    ├── admin/
    ├── frontend/
    └── emails/
```

### Naming Conventions

| Type | Convention | Example |
|------|------------|---------|
| Classes | PascalCase | `Admin_Manager` |
| Methods | snake_case | `get_all_items()` |
| Variables | snake_case | `$total_count` |
| Constants | UPPER_SNAKE | `PLUGIN_PREFIX_VERSION` |
| Tables | prefix_entity | `wp_plugin_items` |
| Options | prefix_setting | `plugin_prefix_enabled` |
| AJAX | prefix_action | `plugin_prefix_save_item` |
| Nonces | prefix_context_nonce | `plugin_prefix_admin_nonce` |

---

## 2. Core Architecture

### Service Container Pattern

```php
// In Plugin.php
class Plugin {
    private static ?Plugin $instance = null;
    private array $services = [];

    public static function get_instance(): Plugin {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function register_services(): void {
        $this->services = [
            'settings'   => new Settings(),
            'repository' => new Repository(),
            'email'      => new Email_Service(),
            'admin'      => new Admin_Manager(),
            'frontend'   => new Frontend_Manager(),
        ];
    }

    public function get_service(string $name): ?object {
        return $this->services[$name] ?? null;
    }
}
```

### PSR-4 Autoloader

```php
spl_autoload_register('plugin_slug_autoloader');

function plugin_slug_autoloader($class) {
    $prefix = 'PLUGIN_NAMESPACE\\';
    $base_dir = PLUGIN_PREFIX_PLUGIN_DIR . 'src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
}
```

### Plugin Constants

```php
define('PLUGIN_PREFIX_VERSION', '1.0.0');
define('PLUGIN_PREFIX_PLUGIN_FILE', __FILE__);
define('PLUGIN_PREFIX_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('PLUGIN_PREFIX_PLUGIN_URL', plugin_dir_url(__FILE__));
define('PLUGIN_PREFIX_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('PLUGIN_PREFIX_TABLE_MAIN', 'plugin_items');
```

---

## 3. Database

### Table Creation with dbDelta()

```php
private function create_tables(): void {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . PLUGIN_PREFIX_TABLE_MAIN;

    $sql = "CREATE TABLE $table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
        title varchar(255) NOT NULL,
        content longtext DEFAULT NULL,
        status varchar(20) DEFAULT 'draft',
        author_id bigint(20) unsigned DEFAULT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY status (status),
        KEY author_id (author_id),
        KEY idx_status_created (status, created_at)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
```

### Repository Pattern

```php
public function insert(array $data) {
    try {
        $result = $this->wpdb->insert(
            $this->table_name,
            [
                'title'   => sanitize_text_field($data['title']),
                'status'  => 'draft',
            ],
            ['%s', '%s']
        );

        if ($result === false) {
            $this->logger->error('Insert failed', ['error' => $this->wpdb->last_error]);
            return false;
        }

        return (int) $this->wpdb->insert_id;
    } catch (\Exception $e) {
        $this->logger->error('Exception', ['message' => $e->getMessage()]);
        return false;
    }
}

public function find(int $id): ?object {
    return $this->wpdb->get_row(
        $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
    );
}

public function get_all(array $args = []): array {
    $where = ['1=1'];
    $values = [];

    if (!empty($args['status'])) {
        $where[] = 'status = %s';
        $values[] = $args['status'];
    }

    $sql = "SELECT * FROM {$this->table_name} WHERE " . implode(' AND ', $where);
    
    return $this->wpdb->get_results($this->wpdb->prepare($sql, $values));
}
```

---

## 4. Admin Interface

### Menu Registration

```php
public function add_admin_menu(): void {
    add_menu_page(
        __('PLUGIN_NAME', 'TEXT_DOMAIN'),
        __('PLUGIN_NAME', 'TEXT_DOMAIN'),
        'manage_options',
        'plugin-slug',
        [$this, 'render_main_page'],
        'dashicons-admin-generic',
        30
    );

    add_submenu_page(
        'plugin-slug',
        __('Settings', 'TEXT_DOMAIN'),
        __('Settings', 'TEXT_DOMAIN'),
        'manage_options',
        'plugin-slug-settings',
        [$this, 'render_settings_page']
    );
}
```

### Settings API

```php
// Register settings
register_setting('plugin_slug_settings', 'plugin_slug_enabled');
register_setting('plugin_slug_settings', 'plugin_slug_items_per_page');

// Getter
public static function is_enabled(): bool {
    return (bool) get_option('plugin_slug_enabled', true);
}
```

---

## 5. Frontend

### Shortcode Registration

```php
public function init(): void {
    add_shortcode('plugin_slug_display', [$this, 'render_display_shortcode']);
}

public function render_display_shortcode(array $atts = []): string {
    $atts = shortcode_atts([
        'id'    => 0,
        'class' => '',
    ], $atts);

    $id = (int) $atts['id'];
    if ($id <= 0) {
        return '';
    }

    $plugin = Plugin::get_instance();
    $repository = $plugin->get_service('repository');
    $item = $repository->find($id);

    return $this->load_template('display-item.php', ['item' => $item]);
}
```

### Template Loading

```php
private function load_template(string $template_name, array $data = []): string {
    extract($data);
    ob_start();

    // Check theme override
    $theme_template = get_stylesheet_directory() . '/plugin-slug/' . $template_name;
    
    if (file_exists($theme_template)) {
        include $theme_template;
    } else {
        include PLUGIN_PREFIX_PLUGIN_DIR . 'templates/frontend/' . $template_name;
    }

    return ob_get_clean() ?: '';
}
```

---

## 6. AJAX

### Handler Registration

```php
// Public AJAX (guest-accessible)
add_action('wp_ajax_plugin_slug_submit', [$this, 'handle_submit']);
add_action('wp_ajax_nopriv_plugin_slug_submit', [$this, 'handle_submit']);

// Admin-only AJAX
add_action('wp_ajax_plugin_slug_admin_action', [$this, 'handle_admin_action']);
```

### Handler Template

```php
public function handle_submit(): void {
    // 1. Verify nonce
    check_ajax_referer('plugin_slug_frontend_nonce', 'nonce');

    // 2. Sanitize input
    $title = sanitize_text_field($_POST['title'] ?? '');

    // 3. Validate
    if (empty($title)) {
        wp_send_json_error(['message' => __('Title is required.', 'TEXT_DOMAIN')]);
    }

    // 4. Process
    $plugin = Plugin::get_instance();
    $repository = $plugin->get_service('repository');
    $result = $repository->insert(['title' => $title]);

    // 5. Respond
    if ($result) {
        wp_send_json_success(['id' => $result]);
    } else {
        wp_send_json_error(['message' => __('Failed to save.', 'TEXT_DOMAIN')]);
    }
}
```

### JavaScript AJAX

```javascript
$.ajax({
    url: pluginSlugFrontend.ajaxUrl,
    type: 'POST',
    data: {
        action: 'plugin_slug_submit',
        nonce: pluginSlugFrontend.nonce,
        title: $('#title').val()
    },
    success: function(response) {
        if (response.success) {
            console.log('Success:', response.data);
        } else {
            console.log('Error:', response.data.message);
        }
    }
});
```

---

## 7. Security

### Sanitization Functions

| Input Type | Function |
|------------|----------|
| Text | `sanitize_text_field($value)` |
| Email | `sanitize_email($value)` |
| URL | `esc_url_raw($value)` |
| Textarea | `sanitize_textarea_field($value)` |
| Integer | `(int) $value` |
| Array | `array_map('sanitize_text_field', $array)` |

### Escaping Functions

| Context | Function |
|---------|----------|
| HTML content | `esc_html($value)` |
| Attributes | `esc_attr($value)` |
| URLs | `esc_url($value)` |
| JavaScript | `esc_js($value)` |
| Allowed HTML | `wp_kses_post($value)` |

### Nonce Pattern

```php
// Create nonce
$nonce = wp_create_nonce('plugin_slug_action_nonce');

// Verify in form
check_admin_referer('plugin_slug_action_nonce');

// Verify in AJAX
check_ajax_referer('plugin_slug_action_nonce', 'nonce');
```

### Capability Check

```php
if (!current_user_can('manage_options')) {
    wp_send_json_error(['message' => __('Unauthorized', 'TEXT_DOMAIN')]);
}
```

### Encryption

```php
public static function encrypt(string $data): string {
    $key = self::get_encryption_key();
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($encrypted . '::' . $iv);
}

public static function decrypt(string $data): string {
    $key = self::get_encryption_key();
    list($encrypted, $iv) = explode('::', base64_decode($data), 2);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $key, 0, $iv) ?: '';
}
```

---

## 8. Services

### Debug Logger

```php
$logger = Debug_Logger::get_instance();
$logger->error('Database error', ['query' => $sql]);  // With stack trace
$logger->warning('Cache miss', ['key' => $key]);
$logger->info('User logged in', ['user_id' => $id]);
$logger->debug('Request data', ['post' => $_POST]);
```

### Email Service

```php
$email = new Email_Service();
$email->send(
    'user@example.com',
    'Subject Line',
    'template-name',  // templates/emails/template-name.php
    ['data' => $value]
);
```

---

## 9. Testing

### PHPUnit Bootstrap

```php
// bootstrap.php
define('PLUGIN_PREFIX_VERSION', '1.0.0');

// Mock WordPress functions
function get_option($key, $default = false) {
    global $mock_options;
    return $mock_options[$key] ?? $default;
}

function update_option($key, $value) {
    global $mock_options;
    $mock_options[$key] = $value;
    return true;
}

// Autoload classes
require_once __DIR__ . '/../../src/Core/Plugin.php';
```

### Test Example

```php
class Repository_Test extends \PHPUnit\Framework\TestCase {
    private $repository;

    protected function setUp(): void {
        global $wpdb;
        $wpdb = new class {
            public $prefix = 'wp_';
            public $last_error = '';
            public $insert_id = 1;
            
            public function prepare($sql, ...$args) {
                return vsprintf(str_replace(['%s', '%d'], ['%s', '%d'], $sql), $args);
            }
            
            public function insert($table, $data, $format) {
                return 1;
            }
        };
        
        $this->repository = new Repository();
    }

    public function test_insert_returns_id_on_success() {
        $result = $this->repository->insert(['title' => 'Test']);
        $this->assertEquals(1, $result);
    }
}
```

---

## 10. Build & Deploy

### Build ZIP

```bash
cd .build
python build-zip.py 1.0.0
```

### Deploy

```bash
python deploy.py              # Full deployment
python deploy.py --dry-run    # Preview only
python deploy.py --dev-only   # Push to dev only
```

### Version Update Locations

| File | Pattern |
|------|---------|
| Main plugin file | ` * Version: X.X.X` |
| Main plugin file | `define('PREFIX_VERSION', 'X.X.X')` |
| Plugin.php | `private $version = 'X.X.X'` |
| Test bootstrap | `define('PREFIX_VERSION', 'X.X.X')` |

---

## Quick Reference

### Common Tasks

| Task | File |
|------|------|
| Add new setting | `Activator.php`, `Settings.php` |
| Add AJAX endpoint | `Plugin.php` |
| Modify database | `Activator.php` |
| Add shortcode | `Frontend_Manager.php` |
| Add admin page | `Admin_Manager.php` |
| Send email | `Email_Service.php` |

### Hooks Quick Reference

```php
// Actions
add_action('admin_menu', [$this, 'add_menu']);
add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);

// Filters
add_filter('plugin_action_links_' . PREFIX_BASENAME, [$this, 'add_links']);
add_filter('body_class', [$this, 'add_body_class']);
```

---

**Generated by AI_Builder_Template**
