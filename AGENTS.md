# Agent Instructions

## Update Plugin Version

When the user says "update the plugin" or asks to bump/increment the version:

1. Find the current version in `plugin/cart-quote-woocommerce-email.php` (line 6: `Version: X.X.X`)
2. Increment the patch version by 0.0.1 (e.g., 1.0.7 → 1.0.8)
3. Update both locations:
   - Plugin header: ` * Version: X.X.X` (line 6)
   - Constant: `define('CART_QUOTE_WC_VERSION', 'X.X.X');` (line 29)
   - Test bootstrap: `define('CART_QUOTE_WC_VERSION', 'X.X.X');` (tests/phpunit/bootstrap.php, line 27)

## Files to Update

| File | Line | Pattern |
|------|------|---------|
| `plugin/cart-quote-woocommerce-email.php` | 6 | ` * Version: X.X.X` |
| `plugin/cart-quote-woocommerce-email.php` | 29 | `define('CART_QUOTE_WC_VERSION', 'X.X.X');` |
| `tests/phpunit/bootstrap.php` | 27 | `define('CART_QUOTE_WC_VERSION', 'X.X.X');` |

---

## Update Log

All changes must be documented here. Mirror changes to GitHub Wiki "Update-Log" page.

| Version | Date | Changes |
|---------|------|---------|
| 1.0.7 | 2026-02-14 | Initial repository setup, CI workflows, security testing infrastructure, enhanced README, GitHub Wiki |

## Wiki Update Process

When making any changes to the plugin:

1. **Update Version** (if applicable) following the version update process above
2. **Add Entry to Update Log** table in this file
3. **Update GitHub Wiki** - Mirror the entry to the [Update-Log](../../wiki/Update-Log) wiki page
4. **Update Related Wiki Pages** - If the change affects features, update relevant wiki documentation

### Wiki Pages to Maintain

| Page | When to Update |
|------|----------------|
| Update-Log | Every change |
| Installation-Guide | Setup changes |
| Configuration | Settings changes |
| Elementor-Widgets | Widget modifications |
| Shortcodes | Shortcode changes |
| Hooks-and-Filters | New/modified hooks |
| Google-Calendar-Setup | OAuth changes |
| Email-Templates | Email changes |
