# Cart Quote WooCommerce & Email

Transform WooCommerce checkout into a quote submission system with Google Calendar integration and email notifications.

## Installation

Upload the `cart-quote-woocommerce-email` folder to `/wp-content/plugins/` and activate through the WordPress admin.

## Security Features

This plugin implements robust security measures:

- **SQL Injection Protection**: Parameterized queries prevent SQL injection attacks
- **XSS Protection**: Input sanitization prevents cross-site scripting
- **CSRF Protection**: Nonce tokens validate form submissions
- **Role-Based Access Control**: Proper permission checks
- **Input Validation**: Data type and format validation

## Developer Information

### Test Suite

Run security tests:
```bash
cd tests
php test-security.php
```

### Architecture

- PSR-4 Autoloading
- Service Container Pattern
- Repository Pattern for Database
- Modular Architecture
- Secure OAuth Token Storage

## License

GPL-2.0-or-later
