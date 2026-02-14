# AI Agent Context Cache

> **CRITICAL: READ THIS FILE FIRST before making any changes to the project.**
> This file contains the complete project context for quick reference.

**Generated:** 2026-02-14 (regenerated) | **Version:** 1.0.7

---

## Quick Reference

| Item | Value |
|------|-------|
| Plugin Name | Cart Quote WooCommerce & Email |
| Current Version | 1.0.7 |
| PHP Version | >= 7.4 |
| WordPress | >= 5.8 |
| WooCommerce | >= 6.0 |
| MySQL | >= 5.7 |
| Namespace | `CartQuoteWooCommerce` |
| Text Domain | `cart-quote-woocommerce-email` |
| Main File | `plugin/cart-quote-woocommerce-email.php` |

---

## File Structure

```
plugin/
├── cart-quote-woocommerce-email.php    # Main plugin file, constants, autoloader
├── composer.json                        # Composer configuration
├── readme.txt                           # WordPress.org readme
├── uninstall.php                       # Uninstall handler
├── assets/
│   ├── css/
│   │   ├── admin.css                   # Admin panel styles
│   │   └── frontend.css                # Frontend styles
│   ├── js/
│   │   ├── admin.js                    # Admin AJAX, status updates, calendar
│   │   └── frontend.js                 # Cart AJAX, form submission
│   └── images/
│       └── placeholder.png
├── src/
│   ├── Admin/
│   │   ├── Admin_Manager.php           # Admin UI, quote list/detail, CSV export
│   │   └── Settings.php                # Option getters/setters, encryption
│   ├── Core/
│   │   ├── Activator.php               # DB tables, default options, cron jobs
│   │   ├── Deactivator.php             # Cleanup on deactivation
│   │   ├── Plugin.php                  # Service container, singleton, AJAX routing
│   │   └── Uninstaller.php             # Delete tables/options on uninstall
│   ├── Database/
│   │   └── Quote_Repository.php        # CRUD operations, logging, statistics
│   ├── Elementor/
│   │   ├── Cart_Widget.php             # Elementor full cart widget
│   │   ├── Mini_Cart_Widget.php        # Elementor mini cart dropdown
│   │   └── Quote_Form_Widget.php       # Elementor quote form widget
│   ├── Emails/
│   │   └── Email_Service.php           # Send admin/client emails
│   ├── Frontend/
│   │   └── Frontend_Manager.php        # Shortcodes, cart AJAX handlers
│   ├── Google/
│   │   └── Google_Calendar_Service.php # OAuth 2.0, event creation
│   └── WooCommerce/
│       └── Checkout_Replacement.php    # Replace checkout with quote form
└── templates/
    ├── admin/
    │   ├── google-calendar.php         # Google OAuth settings page
    │   ├── quote-detail.php            # Single quote view
    │   ├── quotes-list.php             # Quotes table view
    │   └── settings.php                # General settings page
    ├── emails/
    │   ├── admin-notification.php      # Email to admin on new quote
    │   ├── client-confirmation.php     # Email to customer
    │   └── email-wrapper.php           # Email HTML wrapper
    └── frontend/
        ├── cart-display.php            # Full cart display template
        ├── mini-cart.php               # Mini cart dropdown template
        └── quote-form.php              # Quote submission form

tests/
├── phpunit/
│   ├── bootstrap.php                   # Test environment setup, WP mocks
│   └── ...
├── cypress/
│   ├── e2e/
│   │   ├── admin-workflow.cy.js       # Admin quote management tests
│   │   ├── quote-submission.cy.js     # Frontend submission tests
│   │   └── security-tests.cy.js       # XSS, CSRF, SQL injection tests
│   └── ...
└── security/                           # Security scan configurations
```

---

## Core Architecture

### Design Patterns

| Pattern | Implementation |
|---------|----------------|
| **Singleton** | `Plugin::get_instance()` - single plugin instance |
| **Service Container** | `Plugin::$services` array with dependency injection |
| **Repository** | `Quote_Repository` for database abstraction |
| **PSR-4 Autoloading** | `CartQuoteWooCommerce\` namespace maps to `src/` |

### Service Registration (Plugin.php:86-96)

```php
$this->services = [
    'settings'            => new Settings(),
    'repository'          => new Quote_Repository(),
    'google_calendar'     => new Google_Calendar_Service(),
    'email_service'       => new Email_Service(),
    'checkout_replacement'=> new Checkout_Replacement(),
    'admin_manager'       => new Admin_Manager(),
    'frontend_manager'    => new Frontend_Manager(),
];
```

### Plugin Constants (cart-quote-woocommerce-email.php:29-34)

| Constant | Value |
|----------|-------|
| `CART_QUOTE_WC_VERSION` | '1.0.7' |
| `CART_QUOTE_WC_PLUGIN_FILE` | `__FILE__` |
| `CART_QUOTE_WC_PLUGIN_DIR` | `plugin_dir_path(__FILE__)` |
| `CART_QUOTE_WC_PLUGIN_URL` | `plugin_dir_url(__FILE__)` |
| `CART_QUOTE_WC_PLUGIN_BASENAME` | `plugin_basename(__FILE__)` |
| `CART_QUOTE_WC_TABLE_SUBMISSIONS` | 'cart_quote_submissions' |

---

## Classes & Methods

### Core\Plugin

Main service container and AJAX router.

| Method | Signature | Purpose |
|--------|-----------|---------|
| `get_instance()` | `public static: Plugin` | Get singleton instance |
| `init()` | `public void` | Register services, setup hooks |
| `get_service($name)` | `public ?object` | Get service by name from container |
| `activation_redirect()` | `public void` | Redirect to admin after activation |
| `enqueue_admin_assets($hook)` | `public void` | Load admin CSS/JS on plugin pages |
| `enqueue_frontend_assets()` | `public void` | Load frontend CSS/JS everywhere |
| `add_body_class($classes)` | `public array` | Add 'cart-quote-wc-active' body class |
| `handle_quote_submission()` | `public void` | AJAX: Submit quote form |
| `handle_cart_update()` | `public void` | AJAX: Update cart item quantity |
| `handle_remove_item()` | `public void` | AJAX: Remove cart item |
| `handle_get_cart()` | `public void` | AJAX: Get cart data JSON |
| `handle_admin_update_status()` | `public void` | AJAX: Update quote status |
| `handle_admin_create_event()` | `public void` | AJAX: Create Google Calendar event |
| `handle_admin_resend_email()` | `public void` | AJAX: Resend quote emails |
| `handle_admin_save_notes()` | `public void` | AJAX: Save admin notes |
| `handle_admin_export_csv()` | `public void` | AJAX: Export quotes to CSV |
| `handle_google_oauth_callback()` | `public void` | AJAX: Google OAuth callback |
| `handle_google_disconnect()` | `public void` | AJAX: Disconnect Google Calendar |

### Core\Activator

| Method | Signature | Purpose |
|--------|-----------|---------|
| `activate()` | `public void` | Run all activation tasks |
| `create_tables()` | `private void` | Create submissions and logs tables |
| `create_logs_table()` | `private void` | Create quote logs table |
| `create_default_options()` | `private void` | Set default option values |
| `schedule_cron_jobs()` | `private void` | Schedule daily cleanup, hourly token refresh |
| `set_version()` | `private void` | Store plugin version in options |

### Core\Deactivator

| Method | Signature | Purpose |
|--------|-----------|---------|
| `deactivate()` | `public void` | Clear scheduled cron jobs |

### Core\Uninstaller

| Method | Signature | Purpose |
|--------|-----------|---------|
| `uninstall()` | `public static void` | Drop tables, delete options if configured |

### Admin\Settings

| Method | Signature | Purpose |
|--------|-----------|---------|
| `init()` | `public void` | Register settings with WordPress |
| `get_quote_prefix()` | `public static string` | Get quote ID prefix (default: 'Q') |
| `get_quote_start_number()` | `public static int` | Get starting quote number (default: 1001) |
| `get_admin_email()` | `public static string` | Get admin email for notifications |
| `send_to_admin()` | `public static bool` | Should send email to admin? |
| `send_to_client()` | `public static bool` | Should send email to client? |
| `is_pdf_enabled()` | `public static bool` | Is PDF attachment enabled? |
| `get_meeting_duration()` | `public static int` | Get meeting duration in minutes |
| `get_time_slots()` | `public static array` | Get available time slots |
| `get_email_subject_admin()` | `public static string` | Get admin email subject template |
| `get_email_subject_client()` | `public static string` | Get client email subject template |
| `auto_create_event()` | `public static bool` | Auto-create Google Calendar event? |
| `get_google_client_id()` | `public static string` | Get Google OAuth client ID |
| `get_google_client_secret()` | `public static string` | Get Google OAuth client secret |
| `get_google_access_token()` | `public static string` | Get decrypted access token |
| `get_google_refresh_token()` | `public static string` | Get decrypted refresh token |
| `get_google_calendar_id()` | `public static string` | Get target calendar ID |
| `is_google_connected()` | `public static bool` | Is Google Calendar connected? |
| `encrypt($data)` | `public static string` | AES-256-CBC encrypt sensitive data |
| `decrypt($data)` | `public static string` | Decrypt sensitive data |
| `save_google_tokens($tokens)` | `public static void` | Save and encrypt OAuth tokens |
| `clear_google_tokens()` | `public static void` | Delete all Google tokens |
| `google_token_needs_refresh()` | `public static bool` | Check if token expires in 5 min |

### Admin\Admin_Manager

| Method | Signature | Purpose |
|--------|-----------|---------|
| `init()` | `public void` | Add admin menu pages |
| `handle_update_status()` | `public void` | AJAX: Update quote status |
| `handle_save_notes()` | `public void` | AJAX: Save admin notes |
| `handle_export_csv()` | `public void` | AJAX: Export quotes to CSV |

### Database\Quote_Repository

| Method | Signature | Purpose |
|--------|-----------|---------|
| `generate_quote_id()` | `public string` | Generate unique quote ID (e.g., Q1001) |
| `insert($data)` | `public int\|false` | Insert new quote, return ID |
| `update($id, $data)` | `public bool` | Update quote fields |
| `find($id)` | `public ?object` | Get quote by database ID |
| `find_by_quote_id($quote_id)` | `public ?object` | Get quote by quote ID string |
| `get_all($args)` | `public array` | Get quotes with pagination/filters |
| `get_total($args)` | `public int` | Get total count with filters |
| `delete($id)` | `public bool` | Delete quote and log |
| `update_status($id, $status)` | `public bool` | Update quote status |
| `save_google_event($id, $event_id)` | `public bool` | Save Google event ID |
| `log($quote_id, $action, $details, $user_id)` | `public bool` | Log an action |
| `get_logs($quote_id)` | `public array` | Get logs for a quote |
| `get_statistics()` | `public array` | Get quote counts by status |
| `export_csv($args)` | `public string` | Export quotes to CSV string |

### Emails\Email_Service

| Method | Signature | Purpose |
|--------|-----------|---------|
| `init()` | `public void` | Add email headers filter |
| `send_quote_emails($quote_id)` | `public void` | Send both admin and client emails |
| `send_admin_email($quote)` | `public bool` | Send admin notification |
| `send_client_email($quote)` | `public bool` | Send client confirmation |
| `handle_resend_email()` | `public void` | AJAX: Resend emails |
| `format_price($amount)` | `public static string` | Format price for display |
| `format_date($date)` | `public static string` | Format date for display |
| `format_time($time)` | `public static string` | Format time for display |
| `format_duration($duration)` | `public static string` | Format contract duration |

### Google\Google_Calendar_Service

| Method | Signature | Purpose |
|--------|-----------|---------|
| `init()` | `public void` | Setup cron handlers |
| `is_configured()` | `public bool` | Has client ID and secret? |
| `is_connected()` | `public bool` | Has valid access token? |
| `get_auth_url()` | `public string` | Get OAuth authorization URL |
| `get_redirect_uri()` | `public string` | Get OAuth callback URL |
| `exchange_code($code)` | `public array\|false` | Exchange code for tokens |
| `refresh_access_token()` | `public array\|false` | Refresh expired token |
| `create_event($quote_id)` | `public array\|false` | Create calendar event |
| `handle_create_event()` | `public void` | AJAX: Create event from admin |
| `handle_oauth_callback()` | `public void` | AJAX: Handle OAuth redirect |
| `handle_disconnect()` | `public void` | AJAX: Disconnect Google |
| `refresh_token_cron()` | `public void` | Cron: Auto-refresh token |

### WooCommerce\Checkout_Replacement

| Method | Signature | Purpose |
|--------|-----------|---------|
| `init()` | `public void` | Setup checkout hooks |
| `disable_payment_gateways($gateways)` | `public array` | Remove payment gateways |
| `custom_checkout_fields($fields)` | `public array` | Customize billing fields |
| `change_checkout_button_text()` | `public string` | Return 'Submit Quote Request' |
| `intercept_checkout()` | `public void` | Hook during checkout process |
| `redirect_checkout()` | `public void` | Optionally redirect checkout |
| `prevent_order_creation($order_id, $checkout)` | `public int\|null` | Prevent WC order creation |
| `override_checkout_form()` | `public void` | Add hidden field to checkout |
| `add_quote_fields()` | `public void` | Add quote-specific form fields |
| `handle_quote_submission()` | `public void` | AJAX: Process quote submission |
| `sanitize_form_data($data)` | `private array` | Sanitize all form inputs |
| `validate_form_data($data)` | `private WP_Error\|true` | Validate required fields |
| `prepare_cart_data()` | `private array` | Format cart for storage |
| `get_item_meta($cart_item)` | `private array` | Get variation meta |
| `clear_cart_after_submission($insert_id)` | `public void` | Empty cart after submit |

### Frontend\Frontend_Manager

| Method | Signature | Purpose |
|--------|-----------|---------|
| `init()` | `public void` | Register shortcodes |
| `render_quote_form_shortcode($atts)` | `public string` | Render [cart_quote_form] |
| `render_cart_shortcode($atts)` | `public string` | Render [cart_quote_cart] |
| `render_mini_cart_shortcode($atts)` | `public string` | Render [cart_quote_mini_cart] |
| `show_submission_success()` | `public void` | Show success message on cart |
| `handle_cart_update()` | `public void` | AJAX: Update item quantity |
| `handle_remove_item()` | `public void` | AJAX: Remove cart item |
| `handle_get_cart()` | `public void` | AJAX: Get cart JSON |
| `get_cart_data()` | `public static array` | Get formatted cart data |

### Elementor Widgets

| Class | Purpose |
|-------|---------|
| `Cart_Widget` | Full cart display with quantity controls |
| `Mini_Cart_Widget` | Compact dropdown cart |
| `Quote_Form_Widget` | Standalone quote submission form |

---

## Database Schema

### Table: `wp_cart_quote_submissions`

```sql
CREATE TABLE wp_cart_quote_submissions (
    id                BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    quote_id          VARCHAR(20) NOT NULL,           -- e.g., Q1001
    customer_name     VARCHAR(255) NOT NULL,
    email             VARCHAR(255) NOT NULL,
    phone             VARCHAR(50) DEFAULT NULL,
    company_name      VARCHAR(255) DEFAULT NULL,
    preferred_date    DATE DEFAULT NULL,
    preferred_time    VARCHAR(20) DEFAULT NULL,
    contract_duration VARCHAR(100) DEFAULT NULL,
    meeting_requested TINYINT(1) DEFAULT 0,
    cart_data         LONGTEXT NOT NULL,              -- JSON encoded cart
    subtotal          DECIMAL(10,2) DEFAULT 0.00,
    status            VARCHAR(20) DEFAULT 'pending',  -- pending, contacted, closed, canceled
    admin_notes       TEXT DEFAULT NULL,
    google_event_id   VARCHAR(255) DEFAULT NULL,
    calendar_synced   TINYINT(1) DEFAULT 0,
    created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY quote_id (quote_id),
    KEY email (email),
    KEY status (status),
    KEY created_at (created_at)
);
```

### Table: `wp_cart_quote_logs`

```sql
CREATE TABLE wp_cart_quote_logs (
    id         BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
    quote_id   VARCHAR(20) NOT NULL,
    action     VARCHAR(100) NOT NULL,      -- created, status_changed, admin_email_sent, etc.
    details    LONGTEXT DEFAULT NULL,
    user_id    BIGINT(20) UNSIGNED DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY quote_id (quote_id),
    KEY action (action),
    KEY created_at (created_at)
);
```

---

## AJAX Endpoints

### Public Endpoints (No Authentication)

| Action | Handler | Nonce | Request Params | Response |
|--------|---------|-------|----------------|----------|
| `cart_quote_submit` | Checkout_Replacement::handle_quote_submission | `cart_quote_frontend_nonce` | billing_first_name, billing_last_name, billing_email, billing_phone, billing_company, preferred_date, preferred_time, contract_duration, meeting_requested | `{success, quote_id, redirect_url}` |
| `cart_quote_update_cart` | Frontend_Manager::handle_cart_update | `cart_quote_frontend_nonce` | `cart_item_key`, `quantity` | `{success, cart_count, subtotal, items}` |
| `cart_quote_remove_item` | Frontend_Manager::handle_remove_item | `cart_quote_frontend_nonce` | `cart_item_key` | `{success, cart_count, subtotal}` |
| `cart_quote_get_cart` | Frontend_Manager::handle_get_cart | `cart_quote_frontend_nonce` | none | `{success, items, count, subtotal}` |

### Admin Endpoints (Capability: manage_woocommerce)

| Action | Handler | Nonce | Request Params | Response |
|--------|---------|-------|----------------|----------|
| `cart_quote_admin_update_status` | Admin_Manager::handle_update_status | `cart_quote_admin_nonce` | `id`, `status` | `{success, message}` |
| `cart_quote_admin_create_event` | Google_Calendar_Service::handle_create_event | `cart_quote_admin_nonce` | `id` | `{success, event_id, html_link}` |
| `cart_quote_admin_resend_email` | Email_Service::handle_resend_email | `cart_quote_admin_nonce` | `id`, `email_type` | `{success, sent}` |
| `cart_quote_admin_save_notes` | Admin_Manager::handle_save_notes | `cart_quote_admin_nonce` | `id`, `admin_notes` | `{success}` |
| `cart_quote_admin_export_csv` | Admin_Manager::handle_export_csv | `cart_quote_admin_nonce` | `status`, `date_from`, `date_to` | `{success, csv}` |

### Admin Endpoints (Capability: manage_options)

| Action | Handler | Nonce | Request Params | Response |
|--------|---------|-------|----------------|----------|
| `cart_quote_google_oauth_callback` | Google_Calendar_Service::handle_oauth_callback | `cart_quote_admin_nonce` | `code`, `state` | `{success}` or redirect |
| `cart_quote_google_disconnect` | Google_Calendar_Service::handle_disconnect | `cart_quote_admin_nonce` | none | `{success}` |

---

## Shortcodes

| Shortcode | Attributes | Handler | Purpose |
|-----------|------------|---------|---------|
| `[cart_quote_form]` | `show_cart="true"` | Frontend_Manager::render_quote_form_shortcode | Quote submission form with cart |
| `[cart_quote_cart]` | `show_button="true"` | Frontend_Manager::render_cart_shortcode | Full cart display |
| `[cart_quote_mini_cart]` | `show_subtotal="true"`, `show_count="true"` | Frontend_Manager::render_mini_cart_shortcode | Compact dropdown cart |

---

## Hooks

### Actions

| Hook | Parameters | When Fired |
|------|------------|------------|
| `cart_quote_after_submission` | `$insert_id`, `$quote_id`, `$insert_data` | After successful quote submission |
| `cart_quote_wc_daily_cleanup` | none | Daily cron for transient cleanup |
| `cart_quote_wc_refresh_google_token` | none | Hourly cron for Google token refresh |
| `cart_quote_auto_create_event` | `$quote_id` | When auto-create event is enabled |

### Filters

| Hook | Parameters | Purpose |
|------|------------|---------|
| `cart_quote_email_headers` | `$headers`, `$type` | Modify email headers (admin/client) |
| `woocommerce_payment_gateways` | `$gateways` | Disable payment on quote checkout |
| `woocommerce_checkout_fields` | `$fields` | Customize checkout fields |
| `woocommerce_order_button_text` | none | Change button to "Submit Quote Request" |
| `woocommerce_create_order` | `$order_id`, `$checkout` | Prevent order creation |
| `body_class` | `$classes` | Add 'cart-quote-wc-active' class |

---

## Options/Settings

### General Settings

| Option Key | Type | Default | Description |
|------------|------|---------|-------------|
| `cart_quote_wc_quote_prefix` | string | 'Q' | Quote ID prefix |
| `cart_quote_wc_quote_start_number` | string | '1001' | Starting quote number |
| `cart_quote_wc_version` | string | '1.0.7' | Installed plugin version |
| `cart_quote_wc_default_status` | string | 'pending' | Default status for new quotes |
| `cart_quote_wc_delete_on_uninstall` | bool | false | Delete data on uninstall |

### Email Settings

| Option Key | Type | Default | Description |
|------------|------|---------|-------------|
| `cart_quote_wc_send_to_admin` | bool | true | Send admin notification |
| `cart_quote_wc_send_to_client` | bool | true | Send client confirmation |
| `cart_quote_wc_admin_email` | string | admin_email | Admin notification email |
| `cart_quote_wc_email_subject_admin` | string | 'New Quote Submission #{quote_id}' | Admin email subject |
| `cart_quote_wc_email_subject_client` | string | 'Thank you for your quote request #{quote_id}' | Client email subject |
| `cart_quote_wc_enable_pdf` | bool | false | Attach PDF to emails |

### Meeting/Time Settings

| Option Key | Type | Default | Description |
|------------|------|---------|-------------|
| `cart_quote_wc_meeting_duration` | string | '60' | Meeting duration in minutes |
| `cart_quote_wc_time_slots` | array | ['09:00', '11:00', '14:00', '16:00'] | Available time slots |

### Google Calendar Settings

| Option Key | Type | Default | Description |
|------------|------|---------|-------------|
| `cart_quote_wc_google_client_id` | string | '' | OAuth client ID |
| `cart_quote_wc_google_client_secret` | string | '' | OAuth client secret |
| `cart_quote_wc_google_access_token` | string | '' | Encrypted access token |
| `cart_quote_wc_google_refresh_token` | string | '' | Encrypted refresh token |
| `cart_quote_wc_google_token_expires` | int | 0 | Token expiration timestamp |
| `cart_quote_wc_google_calendar_id` | string | 'primary' | Target calendar ID |
| `cart_quote_wc_google_connected` | bool | false | Is Google connected? |
| `cart_quote_wc_auto_create_event` | bool | false | Auto-create events for quotes |
| `cart_quote_wc_encryption_key` | string | generated | AES encryption key |

---

## Templates

### Admin Templates

| File | Purpose | Variables |
|------|---------|-----------|
| `templates/admin/quotes-list.php` | Quote list table | `$quotes`, `$total`, `$page`, `$statuses` |
| `templates/admin/quote-detail.php` | Single quote view | `$quote`, `$logs`, `$statistics` |
| `templates/admin/settings.php` | Settings form | `$settings` |
| `templates/admin/google-calendar.php` | Google OAuth setup | `$is_connected`, `$auth_url` |

### Email Templates

| File | Purpose | Variables |
|------|---------|-----------|
| `templates/emails/email-wrapper.php` | HTML email wrapper | `$content`, `$title` |
| `templates/emails/admin-notification.php` | Admin email body | `$quote` |
| `templates/emails/client-confirmation.php` | Client email body | `$quote` |

### Frontend Templates

| File | Purpose | Variables |
|------|---------|-----------|
| `templates/frontend/quote-form.php` | Quote submission form | `$time_slots`, `$atts` |
| `templates/frontend/cart-display.php` | Full cart display | `$atts` |
| `templates/frontend/mini-cart.php` | Mini cart dropdown | `$atts` |

---

## Version Update Locations

When updating the plugin version, update these 3 locations:

| File | Line | Pattern |
|------|------|---------|
| `plugin/cart-quote-woocommerce-email.php` | 6 | ` * Version: 1.0.7` |
| `plugin/cart-quote-woocommerce-email.php` | 29 | `define('CART_QUOTE_WC_VERSION', '1.0.7');` |
| `tests/phpunit/bootstrap.php` | 27 | `define('CART_QUOTE_WC_VERSION', '1.0.7');` |

---

## Code Patterns & Examples

### Adding a New AJAX Handler

```php
// 1. Add hook in Plugin.php setup_hooks() around line 130:
add_action('wp_ajax_my_new_action', [$this, 'handle_my_new_action']);
add_action('wp_ajax_nopriv_my_new_action', [$this, 'handle_my_new_action']);

// 2. Add handler method in Plugin.php:
public function handle_my_new_action() {
    // Verify nonce (use cart_quote_admin_nonce for admin, cart_quote_frontend_nonce for public)
    check_ajax_referer('cart_quote_frontend_nonce', 'nonce');
    
    // Check capability for admin actions
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => __('Unauthorized', 'cart-quote-woocommerce-email')]);
    }
    
    // Sanitize input
    $param = sanitize_text_field($_POST['param'] ?? '');
    
    // Call appropriate service
    $service = $this->get_service('repository');
    $result = $service->do_something($param);
    
    // Return response
    wp_send_json_success([
        'message' => __('Success!', 'cart-quote-woocommerce-email'),
        'data' => $result,
    ]);
}
```

### Adding a New Setting

```php
// 1. Add default in Activator.php create_default_options():
add_option('cart_quote_wc_my_new_setting', 'default_value');

// 2. Register in Settings.php init():
register_setting('cart_quote_wc_settings', 'cart_quote_wc_my_new_setting');

// 3. Add getter in Settings.php:
public static function get_my_new_setting(): string {
    return get_option('cart_quote_wc_my_new_setting', 'default_value');
}
```

### Adding a New Shortcode

```php
// In Frontend_Manager.php init():
add_shortcode('cart_quote_my_shortcode', [$this, 'render_my_shortcode']);

// Add render method:
public function render_my_shortcode($atts = []) {
    $atts = shortcode_atts([
        'param1' => 'default1',
        'param2' => 'default2',
    ], $atts);
    
    // Check dependencies
    if (WC()->cart->is_empty()) {
        return '<div class="cart-quote-empty">' . 
            __('Cart is empty', 'cart-quote-woocommerce-email') . '</div>';
    }
    
    ob_start();
    include CART_QUOTE_WC_PLUGIN_DIR . 'templates/frontend/my-template.php';
    return ob_get_clean();
}
```

### Querying Quotes

```php
$repository = new \CartQuoteWooCommerce\Database\Quote_Repository();

// Get single quote by ID
$quote = $repository->find(123);
echo $quote->customer_name;
echo $quote->cart_data['items'][0]['product_name'];

// Get single quote by quote ID string
$quote = $repository->find_by_quote_id('Q1001');

// Get quotes with filters
$quotes = $repository->get_all([
    'status' => 'pending',
    'search' => 'company name',
    'date_from' => '2026-01-01',
    'date_to' => '2026-02-14',
    'orderby' => 'created_at',
    'order' => 'DESC',
    'per_page' => 20,
    'page' => 1,
]);

// Get total count with same filters
$total = $repository->get_total(['status' => 'pending']);

// Update status
$repository->update_status(123, 'contacted');

// Save admin notes
$repository->update(123, ['admin_notes' => 'Customer called back']);

// Get statistics
$stats = $repository->get_statistics();
// Returns: ['total', 'pending', 'contacted', 'closed', 'canceled', 'meetings_requested', 'meetings_scheduled']
```

### Sending Emails

```php
$email_service = new \CartQuoteWooCommerce\Emails\Email_Service();

// Send both admin and client emails
$email_service->send_quote_emails($quote_id);

// Send only admin email
$email_service->send_admin_email($quote);

// Send only client email  
$email_service->send_client_email($quote);
```

### Working with Google Calendar

```php
$google = new \CartQuoteWooCommerce\Google\Google_Calendar_Service();

// Check connection
if ($google->is_connected()) {
    // Create event
    $result = $google->create_event($quote_id);
    if ($result) {
        echo 'Event created: ' . $result['html_link'];
    }
}

// Get OAuth URL for setup
$auth_url = $google->get_auth_url();
```

---

## JavaScript Localized Data

### Frontend (cartQuoteFrontend)

```javascript
cartQuoteFrontend = {
    ajaxUrl: '/wp-admin/admin-ajax.php',
    nonce: 'abc123...',
    cartUrl: '/cart/',
    i18n: {
        processing: 'Processing...',
        success: 'Quote submitted successfully!',
        error: 'An error occurred.',
        emptyCart: 'Your cart is empty.',
        requiredField: 'This field is required.',
        invalidEmail: 'Please enter a valid email.'
    }
};
```

### Admin (cartQuoteAdmin)

```javascript
cartQuoteAdmin = {
    ajaxUrl: '/wp-admin/admin-ajax.php',
    nonce: 'xyz789...',
    i18n: {
        confirmDelete: 'Are you sure you want to delete this quote?',
        confirmStatusChange: 'Are you sure you want to change the status?',
        saving: 'Saving...',
        saved: 'Saved!',
        error: 'An error occurred.',
        creatingEvent: 'Creating Google Calendar event...',
        eventCreated: 'Google Calendar event created!',
        resendingEmail: 'Resending email...',
        emailSent: 'Email sent successfully!'
    }
};
```

---

## Security Considerations

1. **Nonce Verification**: All AJAX handlers verify nonces via `check_ajax_referer()`
2. **Capability Checks**: Admin actions check `manage_woocommerce` or `manage_options`
3. **Input Sanitization**: All user input uses `sanitize_text_field()`, `sanitize_email()`, etc.
4. **Token Encryption**: Google OAuth tokens encrypted with AES-256-CBC
5. **SQL Injection Prevention**: All queries use `$wpdb->prepare()`
6. **XSS Prevention**: Output escaped with `esc_html()`, `esc_attr()`, `esc_url()`

---

## Testing

### PHPUnit

```bash
cd tests/phpunit
composer install
vendor/bin/phpunit
```

### Cypress E2E

```bash
cd tests/cypress
npm install
npm run cypress:run
```

### Build Distribution

```bash
./build.sh 1.0.7
```

---

## Common Tasks Quick Reference

| Task | File to Edit |
|------|--------------|
| Add new setting | `Activator.php`, `Settings.php` |
| Add AJAX endpoint | `Plugin.php` (hook + handler) |
| Modify checkout fields | `Checkout_Replacement.php` |
| Add email content | `templates/emails/*.php` |
| Add shortcode | `Frontend_Manager.php` |
| Add Elementor widget | `src/Elementor/*.php`, register in main file |
| Change quote statuses | `Quote_Repository.php` (update_status method) |
| Modify database schema | `Activator.php` (create_tables) |
