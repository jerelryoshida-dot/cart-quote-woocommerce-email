=== Cart Quote WooCommerce & Email ===
Contributors: jerelyoshida
Tags: woocommerce, quote, cart, email, google calendar, elementor
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Transform WooCommerce checkout into a quote submission system with Google Calendar integration and email notifications.

== Description ==

**Cart Quote WooCommerce & Email** transforms your WooCommerce store into a powerful quote submission system. Perfect for businesses that provide services, agents, talents, or custom packages where payment isn't collected upfront.

= Key Features =

* **No Payment Processing** - Replace checkout with quote submission
* **Google Calendar Integration** - Automatically create meeting events with OAuth 2.0
* **Custom Email System** - Beautiful email notifications for admins and clients
* **Elementor Widgets** - 3 custom widgets for seamless page building
* **Enterprise Architecture** - PSR-4 autoloading, service container, repository pattern
* **Admin Dashboard** - Complete quote management with status tracking

= How It Works =

1. Customers add products/services to cart
2. Proceed to "checkout" which is now a quote form
3. Fill in quote details (name, company, preferred dates, etc.)
4. Submit quote request (no payment required)
5. Admin receives notification and reviews quote
6. If meeting requested, admin creates Google Calendar event
7. Client receives meeting invitation automatically

= Elementor Widgets =

* **Cart Widget** - Full cart display with quantity controls
* **Mini Cart Widget** - Compact cart with dropdown
* **Quote Form Widget** - Standalone quote submission form

= Perfect For =

* Talent/Agent booking services
* B2B service providers
* Custom package pricing
* Consulting services
* Any business requiring quotes before payment

== Installation ==

= Minimum Requirements =

* WordPress 5.8 or greater
* PHP version 7.4 or greater
* WooCommerce 6.0 or greater
* MySQL 5.7 or greater

= Automatic Installation =

1. Go to Plugins > Add New in your WordPress admin
2. Search for "Cart Quote WooCommerce & Email"
3. Click Install Now
4. Activate the plugin

= Manual Installation =

1. Upload the plugin folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu
3. Configure settings at Cart Quotes > Settings

= Setup =

1. **Configure General Settings** - Set quote ID prefix and starting number
2. **Email Settings** - Configure admin and client email options
3. **Meeting Settings** - Set default duration and available time slots
4. **Google Calendar** - Connect your Google account for meeting scheduling
5. **Elementor Widgets** - Add widgets to your pages

== Frequently Asked Questions ==

= Does this work with any WooCommerce product? =

Yes! The plugin works with all WooCommerce product types - simple, variable, grouped, etc.

= Does this process payments? =

No. This plugin completely replaces the payment checkout with a quote submission system. No orders are created in WooCommerce.

= What happens to the cart after submission? =

The cart is automatically cleared after a successful quote submission.

= Can I use this without Elementor? =

Yes. Shortcodes are available: [cart_quote_form], [cart_quote_cart], [cart_quote_mini_cart]

= How do I set up Google Calendar? =

1. Go to Google Cloud Console
2. Create OAuth 2.0 credentials
3. Enable Google Calendar API
4. Add the redirect URI shown in plugin settings
5. Enter Client ID and Secret in plugin settings
6. Click "Connect Google Calendar"

= Does this store sensitive data securely? =

Yes. Google OAuth tokens are encrypted before storage. All database queries use prepared statements.

== Screenshots ==

1. Admin dashboard with quote statistics
2. Quote list with status management
3. Quote detail view with customer info
4. Settings page
5. Google Calendar connection
6. Elementor widgets

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release of Cart Quote WooCommerce & Email.

== Additional Info ==

= Developer Information =

* PSR-4 Autoloading
* Service Container Pattern
* Repository Pattern for Database
* Modular Architecture
* Secure OAuth Token Storage

= Hooks and Filters =

The plugin provides various hooks for developers:

**Actions:**
* `cart_quote_after_submission` - Fires after quote submission
* `cart_quote_auto_create_event` - Fires for auto event creation

**Filters:**
* `cart_quote_email_headers` - Filter email headers

= Contributing =

Contributions are welcome! Please submit pull requests to the GitHub repository.


