# Pre-Development Checklist

Complete this checklist BEFORE starting development on a new WordPress plugin.

## Planning

- [ ] **Define the problem** - What specific problem does this plugin solve?
- [ ] **Research existing solutions** - Are there plugins that already do this? How will yours be different?
- [ ] **Define target audience** - Who will use this plugin?
- [ ] **List core features** - What features are essential for version 1.0?
- [ ] **Create feature roadmap** - What features can wait for future versions?

## Technical Requirements

- [ ] **WordPress version** - Minimum WordPress version required
- [ ] **PHP version** - Minimum PHP version (recommend 7.4+)
- [ ] **Required plugins** - Does it depend on WooCommerce, ACF, Elementor, etc.?
- [ ] **Database needs** - What data needs to be stored? Design schema.
- [ ] **API integrations** - Will it connect to external services?
- [ ] **Performance considerations** - Any caching, batch processing needs?

## Security Planning

- [ ] **User input** - List all user input points (forms, settings, AJAX)
- [ ] **Capabilities** - Define who can access what (admin, editor, subscriber)
- [ ] **Data sensitivity** - Identify sensitive data (passwords, tokens, PII)
- [ ] **AJAX endpoints** - List all AJAX actions and their security needs

## File Structure

- [ ] Create plugin folder with proper naming (lowercase, hyphens)
- [ ] Create main plugin file with proper header
- [ ] Set up PSR-4 autoloader
- [ ] Create folder structure:
  - [ ] `src/Core/` - Core classes
  - [ ] `src/Admin/` - Admin functionality
  - [ ] `src/Frontend/` - Frontend functionality
  - [ ] `src/Database/` - Repository classes
  - [ ] `src/Services/` - Service classes (optional)
  - [ ] `assets/css/` - Stylesheets
  - [ ] `assets/js/` - JavaScript
  - [ ] `templates/admin/` - Admin templates
  - [ ] `templates/frontend/` - Frontend templates
  - [ ] `templates/emails/` - Email templates (optional)

## Constants & Configuration

- [ ] Define plugin version constant
- [ ] Define plugin file constant
- [ ] Define plugin directory constant
- [ ] Define plugin URL constant
- [ ] Define plugin basename constant
- [ ] Define database table constants

## Initial Setup

- [ ] Set up version control (Git)
- [ ] Create `.gitignore` file
- [ ] Create `readme.txt` for WordPress.org
- [ ] Create `README.md` for GitHub
- [ ] Set up development environment
- [ ] Configure debugging (WP_DEBUG, WP_DEBUG_LOG)

---

**Generate Plugin Command:**
```bash
cd AI_Builder_Template
python generate-plugin.py --interactive
```
