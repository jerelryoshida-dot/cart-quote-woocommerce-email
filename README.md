# Cart Quote WooCommerce & Email

[![Version](https://img.shields.io/badge/version-1.0.7-blue.svg)](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases)
[![License](https://img.shields.io/badge/license-GPL--2.0-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF.svg)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-%3E%3D5.8-21759B.svg)](https://wordpress.org)

Transform WooCommerce checkout into a quote submission system with Google Calendar integration.

---

## About

**Cart Quote WooCommerce & Email** replaces the traditional WooCommerce checkout with a quote submission form. Perfect for businesses that provide services, custom packages, or require quotes before payment.

### Key Features

- **Quote System** - Replace checkout with quote submission
- **Google Calendar** - OAuth 2.0 integration for meeting scheduling
- **Email Notifications** - Admin and client email templates
- **Elementor Widgets** - 3 custom widgets (Cart, Mini Cart, Quote Form)
- **Admin Dashboard** - Quote management with status tracking
- **Enterprise Code** - PSR-4 autoloading, service container, repository pattern

### Perfect For

| Use Case | Description |
|----------|-------------|
| Talent/Agent Booking | Artists, speakers, performers |
| B2B Services | Consulting, agencies, contractors |
| Custom Packages | Event planning, catering, construction |
| Wholesale | Bulk orders requiring quotes |

---

## Installation

### Requirements

| Requirement | Version |
|-------------|---------|
| WordPress | >= 5.8 |
| PHP | >= 7.4 |
| WooCommerce | >= 6.0 |
| MySQL | >= 5.7 |

### Quick Install

1. Download the [latest release](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases)
2. Upload to `/wp-content/plugins/`
3. Activate in WordPress Admin > Plugins
4. Configure at **Cart Quotes > Settings**

See [Installation Guide](../../wiki/Installation-Guide) for detailed setup.

---

## Usage

### Elementor Widgets

| Widget | Description |
|--------|-------------|
| **Cart Widget** | Full cart display with quantity controls |
| **Mini Cart Widget** | Compact dropdown cart |
| **Quote Form Widget** | Standalone quote submission form |

### Shortcodes

```
[cart_quote_form]      // Quote submission form
[cart_quote_cart]      // Full cart display
[cart_quote_mini_cart] // Mini cart dropdown
```

### Admin Features

- Quote list with filtering and search
- Quote detail view with customer info
- Status management (pending, reviewed, accepted, rejected)
- Google Calendar event creation
- Email notifications

---

## Releases

| Version | Date | Notes |
|---------|------|-------|
| [1.0.7](../../releases/tag/1.0.7) | 2026-02-14 | Initial release with CI workflows |

[View all releases](../../releases)

---

## Package Contents

```
cart-quote-woocommerce-email/
├── plugin/
│   ├── cart-quote-woocommerce-email.php  // Main plugin file
│   ├── src/                              // PHP classes (PSR-4)
│   │   ├── Admin/                        // Admin panel
│   │   ├── Core/                         // Plugin core
│   │   ├── Database/                     // Repository pattern
│   │   ├── Elementor/                    // Widgets
│   │   ├── Emails/                       // Email service
│   │   ├── Frontend/                     // Frontend logic
│   │   ├── Google/                       // Calendar API
│   │   └── WooCommerce/                  // Checkout replacement
│   ├── assets/                           // CSS, JS, images
│   └── templates/                        // Email & frontend templates
├── tests/
│   ├── phpunit/                          // Unit/Integration tests
│   ├── cypress/                          // E2E tests
│   └── security/                         // Security scan configs
├── .github/workflows/                    // CI/CD pipelines
├── build.sh                              // Distribution builder
└── README.md
```

---

## Documentation

| Resource | Link |
|----------|------|
| Installation Guide | [Wiki: Installation](../../wiki/Installation-Guide) |
| Configuration | [Wiki: Configuration](../../wiki/Configuration) |
| Elementor Widgets | [Wiki: Elementor Widgets](../../wiki/Elementor-Widgets) |
| Shortcodes | [Wiki: Shortcodes](../../wiki/Shortcodes) |
| Hooks & Filters | [Wiki: Hooks and Filters](../../wiki/Hooks-and-Filters) |
| Google Calendar Setup | [Wiki: Google Calendar](../../wiki/Google-Calendar-Setup) |
| Update Log | [Wiki: Update Log](../../wiki/Update-Log) |

---

## Development

### Setup

```bash
# PHPUnit tests
cd tests/phpunit
composer install
vendor/bin/phpunit

# Cypress E2E tests
cd tests/cypress
npm install
npm run cypress:run
```

### Build Distribution

```bash
./build.sh 1.0.7
```

---

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests
5. Submit a pull request

---

## License

GPL-2.0-or-later. See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html).

---

## Support

- **Issues**: [GitHub Issues](../../issues)
- **Author**: [Jerel Yoshida](https://github.com/jerelryoshida-dot)
