# Cart Quote WooCommerce & Email v1.0.15

## 🔒 Repository Cleanup & Automated Validation System

This release focuses on repository infrastructure improvements and automated protection systems.

---

## ✨ What's New

### Automated Local-Only Files Verification System

- **Pre-push Git hook** automatically validates tracked files before every push
- **Validation script** for manual verification anytime
- **Configuration-based** pattern matching for allowed/blocked files
- **Documentation** with comprehensive usage instructions

### Repository Cleanup

- **Removed 38 development files** from Git tracking
- **Reduced repository size**: 80 → 42 files (plugin files only)
- **Clean public repository**: Only WordPress plugin files visible on GitHub
- **Local development preserved**: All dev infrastructure remains functional locally

### Protection Layers

1. ✅ **Enhanced .gitignore** - Prevents accidental staging of dev files
2. ✅ **Pre-push Git hook** - Automatically blocks invalid pushes
3. ✅ **Validation script** - Manual verification tool (`.build/validate-tracked-files.sh`)
4. ✅ **Configuration file** - Centralized pattern management (`.build/allowed-files-config.json`)
5. ✅ **Documentation** - Clear guidelines in AGENTS.md

---

## 📦 What's Included

This release contains **42 production-ready plugin files**:

- ✅ Main plugin file (`cart-quote-woocommerce-email.php`)
- ✅ Documentation (`README.md`, `readme.txt`)
- ✅ Uninstall handler (`uninstall.php`)
- ✅ **20 PHP classes** in `src/` directory
- ✅ **5 asset files** (CSS, JavaScript, images)
- ✅ **15 template files** (admin, emails, frontend)

---

## 🔧 Technical Details

### Files Removed from Git Tracking

**Development Infrastructure (Local-Only):**
- `.github/workflows/` - GitHub Actions workflows
- `.gitignore` - Local ignore patterns
- `.others/` - Documentation (5 files)
- `tests/` - Unit/integration tests (31 files)
- `build-tests.sh` - Development script

**Total removed:** 38 files

These files remain on the local development environment but are no longer tracked in the public repository.

### New Validation System Files (Local-Only)

- `.build/validate-tracked-files.sh` - Bash validation script
- `.build/install-hooks.sh` - Hook installer
- `.build/hooks/pre-push` - Pre-push Git hook
- `.build/allowed-files-config.json` - Configuration with patterns

---

## 📋 Installation

1. **Download** `cart-quote-woocommerce-email-v1.0.15.zip`
2. **WordPress Admin** → Plugins → Add New → Upload Plugin
3. **Upload** the ZIP file
4. **Activate** the plugin

---

## 🔄 Upgrade Notes

This is a **non-breaking release** focusing on repository infrastructure. No code changes affecting plugin functionality.

**Safe to upgrade from any previous version.**

---

## 🛠️ For Developers

### Using the Validation System

**First-time setup:**
```bash
cd "D:\Projects\Plugin Builder"
bash .build/install-hooks.sh
```

**Manual validation:**
```bash
bash .build/validate-tracked-files.sh
```

**How it works:**
- Pre-push hook automatically runs before every `git push`
- Validates tracked files against allowed patterns
- Blocks push if development files are detected
- Shows clear error messages with fix instructions

**Emergency bypass (use with caution):**
```bash
git push --no-verify
```

---

## 📊 Repository Statistics

| Metric | Before | After |
|--------|--------|-------|
| **Tracked files** | 80 | 42 |
| **Dev files on GitHub** | 38 | 0 |
| **Plugin files** | 42 | 42 |
| **Validation** | Manual | Automated |

---

## 🔐 Security & Quality

- ✅ Only production files in public repository
- ✅ Automated validation before every push
- ✅ Development infrastructure stays local
- ✅ No sensitive files exposed
- ✅ Clean separation of concerns

---

## 📝 Changelog

**Infrastructure:**
- Implemented automated local-only files verification system
- Created pre-push Git hook for automatic validation
- Added validation script for manual checks
- Enhanced .gitignore with comprehensive patterns
- Updated AGENTS.md with automated validation documentation

**Repository Cleanup:**
- Removed 38 development files from Git tracking
- Cleaned up `.github/workflows/` from public repo
- Removed `.gitignore` from Git tracking (local-only now)
- Removed `.others/` documentation directory from Git
- Removed `tests/` directory from Git tracking
- Removed `build-tests.sh` from Git tracking

**Documentation:**
- Added "Automated GitHub Push Validation" section to AGENTS.md
- Updated Pre-Push Checklist with automated verification
- Added Update Log entry for v1.0.15

---

## 🆘 Support

- **Issues**: [GitHub Issues](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/issues)
- **Documentation**: [GitHub Wiki](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/wiki)

---

## 📜 License

GPL v2 or later

---

## 🙏 Credits

**Author:** Jerel Yoshida  
**Company:** AllOutsourcing  
**Repository:** [cart-quote-woocommerce-email](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email)

---

**Full Changelog**: https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/compare/v1.0.13...v1.0.15
