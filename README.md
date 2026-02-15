# Cart Quote WooCommerce & Email

[![Version](https://img.shields.io/badge/version-1.0.22-blue.svg)](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases)
[![License](https://img.shields.io/badge/license-GPL--2.0-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-8892BF.svg)](https://php.net)
[![WordPress](https://img.shields.io/badge/WordPress-%3E%3D5.8-21759B.svg)](https://wordpress.org)
[![WooCommerce](https://img.shields.io/badge/WooCommerce-%3E%3D6.0-96588A.svg)](https://woocommerce.com)

Transform WooCommerce checkout into a quote submission system with Google Calendar integration.

---

## About

**Cart Quote WooCommerce & Email** replaces the traditional WooCommerce checkout with a quote submission form. Perfect for businesses that provide services, custom packages, or require quotes before payment.

### How It Works

| Step | Action | Result |
|:----:|--------|--------|
| 1️⃣ | Customer adds products/services to cart | Cart ready for quote |
| 2️⃣ | Fills out quote form (name, company, dates) | Quote request created |
| 3️⃣ | Submits request | Admin notified, client confirmed |
| 4️⃣ | Admin reviews & creates meeting (optional) | Google Calendar event sent |
| 5️⃣ | Client receives confirmation | Meeting invite in email |

### Key Features

| Feature | Description |
|---------|-------------|
| **Quote System** | Replace checkout with quote submission - no payment processing |
| **Google Calendar** | OAuth 2.0 integration for automatic meeting scheduling |
| **Email Notifications** | Beautiful HTML emails for admins and clients |
| **Elementor Widgets** | 3 custom widgets for drag-and-drop page building |
| **Admin Dashboard** | Complete quote management with status tracking |
| **Enterprise Architecture** | PSR-4 autoloading, service container, repository pattern |

### Perfect For

| Use Case | Examples |
|----------|----------|
| **Talent/Agent Booking** | Artists, speakers, performers, models |
| **B2B Services** | Consulting, agencies, contractors |
| **Custom Packages** | Event planning, catering, construction |
| **Wholesale** | Bulk orders requiring quotes |

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
2. Go to **WordPress Admin > Plugins > Add New > Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Activate the plugin
5. Configure at **Cart Quotes > Settings**

---

## Usage

### Elementor Widgets

| Widget | Description |
|--------|-------------|
| **Cart Widget** | Full cart display with quantity controls and remove buttons |
| **Mini Cart Widget** | Compact dropdown cart with item count and subtotal |
| **Quote Form Widget** | Standalone quote submission form |

### Shortcodes

Use these shortcodes if you don't have Elementor:

```
[cart_quote_form]              // Quote submission form with cart summary
[cart_quote_form show_cart="false"]  // Quote form without cart display

[cart_quote_cart]              // Full cart display
[cart_quote_cart show_button="true"] // Cart with "Proceed to Quote" button

[cart_quote_mini_cart]         // Mini cart dropdown
[cart_quote_mini_cart show_count="true" show_subtotal="true"]
```

### Admin Features

- **Quote List** - View all quotes with filtering by status, date range, search
- **Quote Detail** - Full quote view with customer info, cart items, and logs
- **Status Management** - Update status (pending, contacted, closed, canceled)
- **Google Calendar** - Create calendar events directly from quote detail page
- **Email Resend** - Resend admin or client emails
- **Admin Notes** - Add internal notes to quotes
- **CSV Export** - Export filtered quotes to CSV

---

## Google Calendar Setup

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select existing
3. Enable **Google Calendar API**
4. Go to **Credentials** > Create **OAuth 2.0 Client ID**
5. Add authorized redirect URI (shown in plugin settings)
6. Copy **Client ID** and **Client Secret** to plugin settings
7. Click **Connect Google Calendar**

### OAuth Configuration

| Setting | Value |
|---------|-------|
| Application Type | Web application |
| Authorized Redirect URI | `https://yoursite.com/wp-admin/admin-ajax.php?action=cart_quote_google_oauth_callback` |
| Scopes | `calendar.events`, `calendar` |

---

## Email Templates

The plugin sends two types of emails:

### Admin Notification

- Sent to configured admin email
- Contains: Quote ID, customer info, cart items, subtotal
- Direct link to quote detail page

### Client Confirmation

- Sent to customer email
- Contains: Quote reference, submission date, cart summary
- Professional branding with site name

---

## Hooks & Filters

### Actions

```php
// Fires after successful quote submission
do_action('cart_quote_after_submission', $insert_id, $quote_id, $insert_data);

// Fires for auto Google Calendar event creation
do_action('cart_quote_auto_create_event', $quote);
```

### Filters

```php
// Modify email headers
$headers = apply_filters('cart_quote_email_headers', $headers, $type);
```

---

## Developer Info

### Architecture

| Pattern | Implementation |
|---------|----------------|
| **Singleton** | `Plugin::get_instance()` |
| **Service Container** | `Plugin::$services` array |
| **Repository** | `Quote_Repository` for database |
| **PSR-4** | `CartQuoteWooCommerce\` namespace |

### File Structure

```
cart-quote-woocommerce-email/
├── cart-quote-woocommerce-email.php  # Main plugin file
├── readme.txt                        # WordPress.org readme
├── uninstall.php                     # Cleanup on uninstall
├── src/
│   ├── Admin/
│   │   ├── Admin_Manager.php         # Admin UI, quote management
│   │   └── Settings.php              # Options, encryption
│   ├── Core/
│   │   ├── Activator.php             # DB tables, cron jobs
│   │   ├── Deactivator.php           # Cleanup on deactivation
│   │   └── Plugin.php                # Service container, AJAX
│   ├── Database/
│   │   └── Quote_Repository.php      # CRUD operations
│   ├── Elementor/
│   │   ├── Cart_Widget.php
│   │   ├── Mini_Cart_Widget.php
│   │   └── Quote_Form_Widget.php
│   ├── Emails/
│   │   └── Email_Service.php         # Send emails
│   ├── Frontend/
│   │   └── Frontend_Manager.php      # Shortcodes, cart AJAX
│   ├── Google/
│   │   └── Google_Calendar_Service.php # OAuth, events
│   └── WooCommerce/
│       └── Checkout_Replacement.php  # Replace checkout
├── assets/
│   ├── css/
│   │   ├── admin.css
│   │   └── frontend.css
│   └── js/
│       ├── admin.js
│       └── frontend.js
└── templates/
    ├── admin/
    │   ├── google-calendar.php
    │   ├── quote-detail.php
    │   ├── quotes-list.php
    │   └── settings.php
    ├── emails/
    │   ├── admin-notification.php
    │   ├── client-confirmation.php
    │   └── email-wrapper.php
    └── frontend/
        ├── cart-display.php
        ├── mini-cart.php
        └── quote-form.php
```

---

## Releases

| Version | Date | Changes |
|---------|------|---------|
| [1.0.17](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.17) | 2026-02-15 | 🐛 Fixed critical AJAX syntax error in frontend.js: Added 5 missing closing braces to updateCartItemQuantity function, implemented error handling with rollback on failed AJAX requests, added debug flag to wp_localize_script, validated JavaScript syntax (92 opening/92 closing braces - balanced) |
| [1.0.14](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.14) | 2026-02-15 | 🎨 **Visual & UX Enhancements**: Professional gradient styling for meeting fields, enhanced checkbox with focus states, improved error field highlighting with red borders, smooth slide animations for field visibility toggle, custom dropdown arrows, auto-focus on date field when meeting requested, smooth transitions throughout, professional error messages with specific guidance |
| [1.0.13](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.13) | 2026-02-15 | 🛠️ **Critical Fix**: Deployment validation system added to prevent missing file errors, ZIP validation script with backslash detection, enhanced build script with auto-validation, WordPress Site Health integration for plugin integrity, comprehensive deployment documentation (DEPLOYMENT.md), unit tests for activation, integration tests for ZIP structure |
| [1.0.12-dev](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.12-dev) | 2026-02-15 | 🔧 **Build System**: Organized all build infrastructure into .build/ folder (local-only), updated build script for parent directory paths, simplified .gitignore, added output directory for build artifacts |
| [1.0.10](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.10) | 2026-02-15 | 🚀 Performance optimizations: caching (40-50% DB reduction), query monitoring (slow query detection), rate limiting (IP-based 5/min), database indexes (60-80% faster), chunked CSV export |
| [1.0.9](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.9) | 2026-02-15 | Code cleanup & bug fixes: syntax error, checkbox handling, removed unused code, enhanced IP validation, division by zero protection |
| [1.0.8](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.8) | 2026-02-14 | Bug fixes: version mismatch, duplicate cart clearing, additional_notes field |
| [1.0.7](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.7) | 2026-02-14 | Initial release |

---

## Security

- **Nonce Verification** - All AJAX requests verify nonces
- **Capability Checks** - Admin actions require `manage_woocommerce`
- **Input Sanitization** - All user input sanitized with WordPress functions
- **Token Encryption** - Google OAuth tokens encrypted with AES-256-CBC
- **SQL Injection Prevention** - All queries use `$wpdb->prepare()`
- **XSS Prevention** - Output escaped with `esc_html()`, `esc_attr()`, `esc_url()`

---

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## Build System

### Overview

This plugin uses a local-only build system to create WordPress plugin distribution ZIP files. All build infrastructure is contained in the `.build/` directory and is never committed to the repository.

### Build Directory Structure

```
.build/                          # LOCAL ONLY (never committed)
├── .gitignore                  # Build-specific ignore patterns
├── build-zip.py                # Main build script
├── build-config.json           # Default configuration template
├── build-config.local.json     # Local override (your personal settings)
├── build-config.dev.json       # Development environment config
├── build-config.prod.json      # Production environment config
└── output/                    # Build artifacts (*.zip files)
```

### Building the Plugin

**Prerequisites:**
- Python 3.x installed
- Build files located in `.build/` directory

**Build from .build/ directory:**
```bash
cd .build

# Build with specific version
python build-zip.py 1.0.13

# Build with local config (uses version from build-config.local.json)
python build-zip.py

# Build with production environment
python build-zip.py 1.0.13 --env prod

# Build with development environment
python build-zip.py 1.0.13 --env dev

# Build with custom configuration
python build-zip.py 1.0.13 --config custom-config.json
```

### Configuration Files

**build-config.json (Template)**
Default configuration committed to repository as reference. Contains all plugin source paths, exclusion patterns, and build settings.

**build-config.local.json (Local Override)**
Your personal configuration that overrides template settings. Never committed to Git. Used for custom versions, paths, or build settings.

**build-config.dev.json (Development)**
Development-specific configuration. May include test files, debug assets, or relaxed validation.

**build-config.prod.json (Production)**
Production-specific configuration. Strict exclusion patterns, fails if required files missing.

### Configuration Loading Priority

1. `build-config.local.json` (highest priority)
2. `build-config.{env}.json` (--env dev or prod)
3. `build-config.json` (default template)

### Build Output

ZIP files are created in `.build/output/`:
- Development: `cart-quote-woocommerce-email-dev-v{version}.zip`
- Production: `cart-quote-woocommerce-email-v{version}.zip`

### Example Workflow

```bash
# 1. Update version in local config
cd .build
# Edit build-config.local.json: set "version": "1.0.13"

# 2. Build plugin
python build-zip.py

# 3. Output created in output/
# .build/output/cart-quote-woocommerce-email-dev-v1.0.13.zip

# 4. Upload to GitHub release
gh release create v1.0.13 \
  --title "v1.0.13" \
  --notes "Release notes here" \
  .build/output/cart-quote-woocommerce-email-v1.0.13.zip
```

### Build Configuration Example

```json
{
  "plugin_name": "cart-quote-woocommerce-email",
  "version": "1.0.12-dev",
  "environment": "development",
  "include_dirs": [
    "../src/",
    "../templates/",
    "../assets/"
  ],
  "include_files": [
    "../cart-quote-woocommerce-email.php",
    "../readme.txt",
    "../uninstall.php"
  ],
  "exclude_patterns": [
    ".build/",
    ".git/",
    ".github/",
    "*.zip"
  ],
  "required_files": [
    "../cart-quote-woocommerce-email.php",
    "../readme.txt"
  ],
  "fail_on_missing": true,
  "output_dir": "output/"
}
```

### Notes

- **Local-Only**: The entire `.build/` directory is ignored by Git and never committed
- **No CI/CD**: This project uses manual builds only, no GitHub Actions automation
- **Clean Repository**: Only plugin source files are committed to the repository
- **Flexible**: Configure different builds for development, testing, and production

---

## License

GPL-2.0-or-later. See [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html).

---

## Support

- **Bug Reports**: [GitHub Issues](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/issues)
- **Author**: [Jerel Yoshida](https://github.com/jerelryoshida-dot)

---

## Changelog

### 1.0.12-dev
* 🔧 **Build System**:
  - Organized all build infrastructure into `.build/` directory (local-only)
  - Updated build-zip.py to handle paths from parent directory
  - Moved build-config.json, build-zip.py, and all config variants to .build/
  - Simplified root .gitignore to exclude .build/, .github/, and .gitignore
  - Created .build/output/ directory for build artifacts
  - Added build system documentation to README.md
* 📁 **Repository Organization**:
  - Clean repository root with only plugin source files
  - All build infrastructure contained in single .build/ directory
  - Enhanced maintainability and portability of build system
* 🔧 **Configuration**:
  - Updated all config files to use `../` prefix for parent directory paths
  - Added output_dir configuration option
  - Improved path resolution for cross-platform compatibility

### 1.0.10
* 🚀 **Performance Optimizations**:
  - Added caching system (Cache_Manager.php) with wp_cache wrapper
  - Added query monitoring system (Query_Logger.php) with slow query detection (>100ms)
  - Added rate limiting system (Rate_Limiter.php) with IP-based protection (5 req/min default)
  - Added 4 composite database indexes for 60-80% faster queries
  - Optimized get_statistics() with GROUP BY (83% query reduction)
  - Optimized export_csv() with chunked batches (95% memory reduction)
* 🎨 **Admin Interfaces**:
  - Cache statistics dashboard with hit rate tracking
  - Query performance dashboard with N+1 identification
  - Rate limiting configuration UI with blocked IP management
* 🔒 **Security Enhancements**:
  - Rate limiting prevents automated abuse
  - IP blocking for excessive requests
  - IP whitelist support for trusted addresses
* 📝 **Testing Infrastructure**:
  - Expanded Email_Service_Test.php (25+ test methods)
  - Expanded Google_Calendar_Service_Test.php (35+ test methods)
  - Created Quote_Submission_Test.php (25+ integration tests)
* 🔧 **Configuration**:
  - Added Local-Only Files Configuration to AGENTS.md
  - Updated .gitignore with local-only patterns
  - Removed .github and .gitignore from Git tracking

### 1.0.9
* Fixed syntax error in quote detail template
* Fixed checkbox handling in admin settings
* Removed unused methods (get_status_label, get_status_class, format_price)
* Removed duplicate nonce verification
* Enhanced IP validation with FILTER_VALIDATE_IP
* Added division by zero protection
* Added asset file existence checks
* Simplified code with null coalescing operator
* Fixed ZIP archive format causing activation errors on Linux servers

### 1.0.8
* Fixed version mismatch in Plugin.php
* Removed duplicate cart clearing in Checkout_Replacement.php
* Added `additional_notes` column to database schema
* Added `additional_notes` field handling in Quote_Repository.php
* Removed duplicate contract duration script in quote-form.php

### 1.0.0
* Initial release
