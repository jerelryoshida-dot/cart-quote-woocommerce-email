# Cart Quote WooCommerce & Email

[![Version](https://img.shields.io/badge/version-1.0.8-blue.svg)](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases)
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

### Quote Form Fields

The quote form collects:

| Field | Required | Description |
|-------|----------|-------------|
| First Name | Yes | Customer first name |
| Last Name | Yes | Customer last name |
| Email | Yes | Customer email address |
| Phone | Yes | Customer phone number |
| Company | Yes | Company/organization name |
| Preferred Date | Yes | Preferred start/meeting date |
| Preferred Time | No | Preferred meeting time slot |
| Contract Duration | Yes | 1 month, 3 months, 6 months, or custom |
| Meeting Request | No | Checkbox to request a meeting |
| Additional Notes | No | Any extra information |

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

## License

GPL-2.0-or-later. See [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html).

---

## Support

- **Bug Reports**: [GitHub Issues](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/issues)
- **Author**: [Jerel Yoshida](https://github.com/jerelryoshida-dot)

---

## Changelog

### 1.0.8
* Fixed version mismatch in Plugin.php
* Removed duplicate cart clearing in Checkout_Replacement.php
* Added `additional_notes` column to database schema
* Added `additional_notes` field handling in Quote_Repository.php
* Removed duplicate contract duration script in quote-form.php

### 1.0.0
* Initial release
