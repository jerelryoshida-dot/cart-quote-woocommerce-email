# Agent Instructions

> **CRITICAL: Read `AI_AGENT_CACHE.md` FIRST before doing anything.**
> This file contains the complete project context including all classes, methods,
> AJAX endpoints, database schema, hooks, filters, and code examples for quick reference.

---

## AI Context Cache

The `AI_AGENT_CACHE.md` file is a comprehensive snapshot of the entire project structure.
It contains all the information needed to understand and work with this plugin.

### When to Regenerate Cache

Regenerate `AI_AGENT_CACHE.md` when any of the following changes are made:
- New PHP class added or removed
- New AJAX endpoint added
- Database schema modified
- New shortcode or widget added
- New hook or filter added
- New setting/option added
- Template structure changed
- Class methods significantly changed

### /regenerate-cache Command

When asked to "regenerate cache", "update AI cache", or "refresh cache":

1. Read all PHP files in `plugin/src/` directories recursively
2. Extract class names, method signatures, and their purposes
3. Read `plugin/src/Core/Activator.php` for current database schema
4. Read `plugin/src/Admin/Settings.php` for all options and defaults
5. Read `plugin/src/Core/Plugin.php` for AJAX handlers and hooks
6. Read `plugin/src/Frontend/Frontend_Manager.php` for shortcodes
7. Read main plugin file for constants and service registration
8. Update `AI_AGENT_CACHE.md` with current project state
9. Update the "Generated" date at the top of the file

---

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
| 1.0.7 | 2026-02-14 | Created AI_AGENT_CACHE.md with full project context, updated AGENTS.md with cache reading instructions and /regenerate-cache command |

## Wiki Update Process

When making any changes to the plugin:

1. **Update Version** (if applicable) following the version update process above
2. **Add Entry to Update Log** table in this file
3. **Update GitHub Wiki** - Mirror the entry to the [Update-Log](../../wiki/Update-Log) wiki page
4. **Update Related Wiki Pages** - If the change affects features, update relevant wiki documentation

---

## Release Process

When changes are pushed to `master` branch, create a GitHub release:

1. **Create Plugin Zip:**
   ```bash
   powershell "Compress-Archive -Path 'plugin/*' -DestinationPath 'cart-quote-woocommerce-email-vX.X.X.zip' -Force"
   ```

2. **Create Release:**
   ```bash
   gh release create vX.X.X \
     --title "vX.X.X - <Release Title>" \
     --notes-file release-notes.md \
     ./cart-quote-woocommerce-email-vX.X.X.zip
   ```

3. **Update Wiki** - Clone wiki, add Update-Log entry, push

4. **Cleanup** - Remove temporary zip and notes files

### Release Notes Template

```markdown
## Cart Quote WooCommerce & Email vX.X.X

### Changes
- <List of changes>

### Installation
1. Download the zip file
2. Go to WordPress Admin > Plugins > Add New > Upload Plugin
3. Activate the plugin
```

---

## Branch Structure

| Branch | Purpose |
|--------|---------|
| `master` | Production - releases are created from here |
| `dev` | Development - feature branches merge here first |

**Note:** The `main` branch has been removed. Use `master` for production.

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
