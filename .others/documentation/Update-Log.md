# Cart Quote WooCommerce & Email - Update Log

## Release History

| Version | Date | Changes |
|---------|------|---------|
| [v1.0.14](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.14) | 2026-02-15 | 🎨 **Enhancements**: Professional visual design with gradients, enhanced checkbox with focus states, improved error field highlighting, smooth animations, auto-focus on date field, advanced validation (date, email, phone), ARIA accessibility attributes |
| [v1.0.13](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.13) | 2026-02-15 | 🛠️ **Critical Fix**: Deployment validation system, ZIP validation script, enhanced build script, WordPress Site Health integration, comprehensive deployment documentation |
| [v1.0.12-dev](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.12-dev) | 2026-02-15 | 🔧 **Build System**: Build infrastructure organization, updated build scripts, simplified .gitignore |
| [v1.0.10](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.10) | 2026-02-15 | 🚀 **Performance**: Caching optimization, query monitoring, rate limiting, database indexes, chunked CSV export |
| [v1.0.9](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.9) | 2026-02-15 | 🧹 **Code Cleanup**: Syntax fixes, checkbox handling, IP validation, division by zero protection |
| [v1.0.8](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.8) | 2026-02-14 | 🐛 **Bug Fixes**: Version mismatch, duplicate cart clearing, additional_notes field |
| [v1.0.7](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.7) | 2026-02-14 | 🎉 **Initial Release** |

---

## Changelog

### Version 1.0.14 (February 15, 2026)

**Visual & UX Enhancements:**
- Professional gradient styling for meeting fields
- Enhanced checkbox with focus states and visual feedback
- Improved error field highlighting with red borders and backgrounds
- Smooth slide animations for field visibility toggle
- Custom dropdown arrows for time slot selection
- Auto-focus on date field when meeting is requested
- Smooth transitions throughout the user interface
- Professional error messages with specific guidance

**Advanced Validation:**
- Date validation to prevent past date selection
- Email format validation with specific error messages
- Phone number format validation
- Comprehensive error message container with scroll-to functionality
- Field shaking animation for attention
- Reset error states when fixing issues

**Accessibility Improvements:**
- Proper ARIA attributes (aria-hidden, aria-required, aria-describedby)
- Semantic HTML with role="region" for meeting fields
- Screen reader friendly error announcements
- Full keyboard navigation support
- Proper focus management and accessibility
- Color contrast improvements

**Technical Improvements:**
- Enhanced code maintainability and readability
- Improved error handling and validation logic
- Better integration with Elementor editor
- Optimized animation performance
- Comprehensive utility functions for error handling

**Files Modified:**
- `assets/css/frontend.css` (+250 lines)
- `assets/js/frontend.js` (+214 lines, -62 lines)
- `src/Elementor/Quote_Form_Widget.php` (+34 lines, -4 lines)
- `templates/frontend/quote-form.php` (+32 lines, -6 lines)

### Version 1.0.13 (February 15, 2026)

**Deployment Validation:**
- Added validation system to prevent missing file errors
- ZIP validation script with backslash detection
- Enhanced build script with auto-validation
- WordPress Site Health integration for plugin integrity
- Comprehensive deployment documentation (DEPLOYMENT.md)
- Unit tests for activation
- Integration tests for ZIP structure

**Build System Improvements:**
- Organized build infrastructure into `.build/` folder
- Simplified .gitignore configuration
- Added output directory for build artifacts

### Version 1.0.12-dev (February 15, 2026)

**Build System:**
- Organized all build infrastructure into `.build/` folder (local-only)
- Updated build script for parent directory paths
- Simplified .gitignore
- Added output directory for build artifacts

### Version 1.0.10 (February 15, 2026)

**Performance Optimizations:**
- Implemented caching system (40-50% DB reduction)
- Query monitoring with slow query detection
- Rate limiting (IP-based 5/min)
- Database indexes (60-80% faster)
- Chunked CSV export

### Version 1.0.9 (February 15, 2026)

**Code Cleanup & Bug Fixes:**
- Fixed syntax errors
- Improved checkbox handling
- Removed unused code
- Enhanced IP validation
- Division by zero protection

### Version 1.0.8 (February 14, 2026)

**Bug Fixes:**
- Fixed version mismatch
- Fixed duplicate cart clearing
- Added additional_notes field

### Version 1.0.7 (February 14, 2026)

**Initial Release**
