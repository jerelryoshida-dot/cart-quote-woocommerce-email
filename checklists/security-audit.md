# Security Audit Checklist

Complete this checklist for every release to ensure plugin security.

## Input Validation & Sanitization

### Forms
- [ ] All form inputs use appropriate sanitize functions
- [ ] Required fields are validated before processing
- [ ] File uploads are validated (type, size)
- [ ] Maximum input lengths are enforced

### AJAX
- [ ] All POST data is sanitized
- [ ] All GET parameters are sanitized
- [ ] Array inputs are properly handled
- [ ] JSON input is validated

### Settings
- [ ] Settings values are validated before saving
- [ ] Settings callbacks use sanitize callback
- [ ] Checkbox values are properly handled

### Sanitization Functions Used
| Input Type | Function | Used? |
|------------|----------|-------|
| Text | `sanitize_text_field()` | [ ] |
| Email | `sanitize_email()` | [ ] |
| URL | `esc_url_raw()` | [ ] |
| Textarea | `sanitize_textarea_field()` | [ ] |
| Integer | `intval()` / `(int)` | [ ] |
| Float | `floatval()` / `(float)` | [ ] |
| Array | `array_map()` with sanitize | [ ] |
| HTML (allowed) | `wp_kses()` | [ ] |
| File path | `sanitize_file_name()` | [ ] |

## Output Escaping

### Templates
- [ ] All variables in HTML are escaped with `esc_html()`
- [ ] All attributes use `esc_attr()`
- [ ] All URLs use `esc_url()`
- [ ] JavaScript data uses `esc_js()`
- [ ] HTML content uses `wp_kses_post()` (if HTML allowed)

### AJAX Responses
- [ ] JSON responses use `wp_send_json_success()` / `wp_send_json_error()`
- [ ] Error messages are escaped before sending

### Escaping Functions Used
| Context | Function | Used? |
|---------|----------|-------|
| HTML content | `esc_html()` | [ ] |
| Attributes | `esc_attr()` | [ ] |
| URLs | `esc_url()` | [ ] |
| JavaScript | `esc_js()` | [ ] |
| Allowed HTML | `wp_kses_post()` | [ ] |
| textarea | `esc_textarea()` | [ ] |

## Nonce Verification

### Forms
- [ ] All forms include nonce field: `wp_nonce_field()`
- [ ] Form processing verifies nonce: `check_admin_referer()`

### AJAX
- [ ] All AJAX requests include nonce in data
- [ ] All AJAX handlers verify nonce: `check_ajax_referer()`
- [ ] Nonce action names are unique to plugin

### URLs
- [ ] Admin URLs include nonce: `wp_nonce_url()`
- [ ] Nonce is verified before processing: `wp_verify_nonce()`

## Capability Checks

### Admin Pages
- [ ] Menu pages check capability: `manage_options`, `manage_woocommerce`
- [ ] Page callbacks verify capability

### AJAX Handlers
| Handler | Capability | Checked? |
|---------|------------|----------|
| Public | None | N/A |
| Admin basic | `manage_options` | [ ] |
| Admin WC | `manage_woocommerce` | [ ] |

### Settings
- [ ] Settings registered with proper capability
- [ ] Settings form checks capability

## SQL Injection Prevention

- [ ] All queries use `$wpdb->prepare()`
- [ ] No direct variable interpolation in SQL
- [ ] Table names use `$wpdb->prefix`
- [ ] User input in ORDER BY is validated against whitelist
- [ ] LIMIT values are cast to integers

### Query Examples

**CORRECT:**
```php
$wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", $id)
```

**WRONG:**
```php
"SELECT * FROM {$table} WHERE id = {$id}"
```

## File Operations

- [ ] No direct file access (ABSPATH check)
- [ ] File paths are validated
- [ ] File types are whitelisted
- [ ] File operations use WordPress functions
- [ ] Upload directories are outside web root when possible

## Data Protection

### Sensitive Data Storage
- [ ] Passwords are hashed, not encrypted
- [ ] API keys are encrypted before storage
- [ ] OAuth tokens are encrypted
- [ ] Personal data is minimized

### Encryption
- [ ] AES-256-CBC for sensitive data
- [ ] Unique encryption key per installation
- [ ] Keys stored securely in options

### Logging
- [ ] Sensitive data is redacted from logs
- [ ] Debug mode required for verbose logging
- [ ] Log files are not web-accessible

## Cross-Site Scripting (XSS)

- [ ] All output is escaped
- [ ] User-generated content is sanitized
- [ ] JavaScript doesn't directly insert user content
- [ ] Content Security Policy headers considered

## Cross-Site Request Forgery (CSRF)

- [ ] All state-changing actions verify nonces
- [ ] Nonces are unique per action
- [ ] Nonces have appropriate expiration

## Authentication & Authorization

- [ ] User authentication uses WordPress functions
- [ ] Custom capabilities follow naming convention
- [ ] Role checks use `current_user_can()`
- [ ] Super admin checks consider multisite

## API Security

### REST API (if used)
- [ ] Endpoints check permissions
- [ ] Input is validated
- [ ] Output is filtered appropriately
- [ ] Rate limiting considered

### External APIs
- [ ] HTTPS used for all requests
- [ ] API keys not exposed in client-side code
- [ ] Response data is validated
- [ ] Timeouts are set appropriately

## WordPress-Specific Security

- [ ] Direct file access prevented
- [ ] Plugin activates without errors
- [ ] No PHP errors on deactivation
- [ ] Uninstall removes data (if configured)
- [ ] Transients are properly named

## Security Testing

- [ ] Tested with `WP_DEBUG` enabled
- [ ] No PHP warnings/notices
- [ ] Tested with invalid input
- [ ] Tested with missing nonces
- [ ] Tested with wrong capabilities
- [ ] SQL queries tested for injection attempts

---

## Security Scan Tools

Run these tools before release:

```bash
# PHPStan (static analysis)
vendor/bin/phpstan analyse

# Psalm (static analysis)
vendor/bin/psalm

# WordPress Coding Standards
vendor/bin/phpcs --standard=WordPress

# Security-specific scanner
vendor/bin/phpcs --standard=WordPress-VIP-Go
```

---

## Common Vulnerabilities to Check

| Vulnerability | Prevention | Checked? |
|---------------|------------|----------|
| SQL Injection | `$wpdb->prepare()` | [ ] |
| XSS | `esc_*()` functions | [ ] |
| CSRF | Nonce verification | [ ] |
| File Inclusion | Validate paths | [ ] |
| Authorization Bypass | Capability checks | [ ] |
| Information Disclosure | Debug mode off | [ ] |
| CSRF via XSS | Nonce in headers | [ ] |
