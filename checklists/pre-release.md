# Pre-Release Checklist

Complete this checklist BEFORE releasing a new version of your plugin.

## Code Quality

- [ ] **No debug code** - Remove all `var_dump()`, `print_r()`, `console.log()`
- [ ] **No TODO comments** - Complete or remove all TODO markers
- [ ] **Remove unused code** - Delete commented-out code, unused functions
- [ ] **Consistent formatting** - Run code formatter (PHP-CS-Fixer, Prettier)
- [ ] **No hardcoded values** - Move configuration to settings
- [ ] **No sensitive data** - Remove API keys, passwords, test credentials

## Version Updates

- [ ] **Update version in main file header** - ` * Version: X.X.X`
- [ ] **Update version constant** - `define('PLUGIN_PREFIX_VERSION', 'X.X.X')`
- [ ] **Update version in Plugin class** - `private $version = 'X.X.X'`
- [ ] **Update version in test bootstrap** - `define('PLUGIN_PREFIX_VERSION', 'X.X.X')`
- [ ] **Update README.md version badge** - If applicable

## Security Audit

- [ ] **All AJAX handlers verify nonces** - `check_ajax_referer()`
- [ ] **All admin actions check capabilities** - `current_user_can()`
- [ ] **All input is sanitized** - `sanitize_text_field()`, etc.
- [ ] **All output is escaped** - `esc_html()`, `esc_attr()`, `esc_url()`
- [ ] **All SQL uses prepared statements** - `$wpdb->prepare()`
- [ ] **Sensitive data is encrypted** - API keys, tokens
- [ ] **No direct file access** - `if (!defined('ABSPATH')) exit;`

## Database

- [ ] **Test activation on fresh install** - Tables create correctly
- [ ] **Test upgrade path** - Existing data preserved
- [ ] **Verify database schema** - All columns, indexes present
- [ ] **Test deactivation** - Cron jobs cleared
- [ ] **Test uninstall** - Data removed (if option enabled)

## Testing

- [ ] **Test on PHP 7.4** - Minimum supported version
- [ ] **Test on PHP 8.x** - Latest stable
- [ ] **Test on WordPress 5.8** - Minimum supported version
- [ ] **Test on WordPress 6.x** - Latest stable
- [ ] **Test with WooCommerce** - If applicable
- [ ] **Test AJAX functionality** - Both logged-in and guest
- [ ] **Test email sending** - Check content and formatting
- [ ] **Test admin screens** - All settings save correctly
- [ ] **Test frontend display** - Shortcodes render correctly
- [ ] **Test mobile responsiveness** - CSS works on mobile

## Documentation

- [ ] **Update README.md** - New features, changes
- [ ] **Update readme.txt** - WordPress.org format
- [ ] **Update changelog** - Add release notes
- [ ] **Update AI_AGENT_CACHE.md** - If structure changed
- [ ] **Update inline documentation** - PHPDoc blocks
- [ ] **Add upgrade notes** - If breaking changes

## Build & Release

- [ ] **Run lint/static analysis** - PHPStan, Psalm
- [ ] **Build ZIP file** - `python build-zip.py X.X.X`
- [ ] **Validate ZIP structure** - No backslashes, all files present
- [ ] **Test ZIP installation** - Install on test WordPress
- [ ] **Test activation** - Plugin activates without errors
- [ ] **Create Git tag** - `git tag -a vX.X.X -m "Release vX.X.X"`
- [ ] **Push to repository** - `git push origin master --tags`
- [ ] **Create GitHub release** - Attach ZIP, add notes
- [ ] **Update wiki** - Sync Update-Log.md

## Post-Release

- [ ] **Test auto-update** - WordPress detects new version
- [ ] **Monitor error logs** - Check for activation errors
- [ ] **Support readiness** - Prepare for support questions
- [ ] **Update version tracking** - Document in AGENTS.md

---

## Quick Release Commands

```bash
# Build and validate
cd .build
python build-zip.py 1.0.0

# Create release
gh release create v1.0.0 --title "v1.0.0 - Release Title" ./output/plugin-v1.0.0.zip

# Or use full deploy
python deploy.py
```
