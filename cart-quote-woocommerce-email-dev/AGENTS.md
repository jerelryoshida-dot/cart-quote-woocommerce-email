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
