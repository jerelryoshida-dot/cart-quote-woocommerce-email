# Deployment Guide for Cart Quote WooCommerce & Email

## Overview

This plugin requires proper deployment to ensure all files are correctly installed on production servers.

## Prerequisites

- SSH access to server
- WP-CLI installed (recommended)
- ZIP file built with validated structure

## Automated Deployment (Recommended)

### Step 1: Build Validated ZIP

```bash
cd "D:\Projects\Plugin Builder\.build"
python build-zip.py X.X.X
```

The build script now automatically validates:
- ✅ Forward slash paths (Linux compatible)
- ✅ All required files present
- ✅ No empty critical files
- ✅ Correct directory structure

### Step 2: Validate ZIP (Manual Check)

```bash
python validate-zip.py output/cart-quote-woocommerce-email-vX.X.X.zip
```

### Step 3: Deploy to Production

```bash
# Upload ZIP to server
scp output/cart-quote-woocommerce-email-vX.X.X.zip user@server:/tmp/

# SSH into server
ssh user@server

# Navigate to plugins directory
cd /home/user/domains/example.com/public_html/wp-content/plugins/

# Backup existing installation
if [ -d "cart-quote-woocommerce-email" ]; then
    mv cart-quote-woocommerce-email cart-quote-woocommerce-email.backup-$(date +%Y%m%d-%H%M%S)
fi

# Extract ZIP
unzip /tmp/cart-quote-woocommerce-email-vX.X.X.zip

# Set permissions
chmod -R 755 cart-quote-woocommerce-email
find cart-quote-woocommerce-email -type f -exec chmod 644 {} \;

# Activate plugin
wp plugin activate cart-quote-woocommerce-email --path=/home/user/domains/example.com/public_html/

# Cleanup
rm /tmp/cart-quote-woocommerce-email-vX.X.X.zip
```

### Step 4: Verify Deployment

```bash
# Check directory structure
ls -la cart-quote-woocommerce-email/src/Core/

# Should show:
# Activator.php
# Deactivator.php
# Plugin.php
# (and other files)

# Run health check
wp eval 'print_r(CartQuoteWooCommerce\Admin\Health_Check::check_plugin_integrity());'
```

## Manual Deployment (Alternative)

### Using FTP/SFTP

1. **Build ZIP locally** and validate it
2. **Upload ZIP** to server via FTP/SFTP
3. **Extract ZIP on server** (do not extract locally first)
4. **Verify directory structure** before activating
5. **Activate plugin** via WordPress admin

### Manual File Upload (NOT RECOMMENDED)

If you must upload files individually:

1. Create directory structure on server:
   ```
   cart-quote-woocommerce-email/
   ├── src/
   │   ├── Core/
   │   ├── Admin/
   │   ├── Frontend/
   │   └── ...
   ├── templates/
   ├── assets/
   └── cart-quote-woocommerce-email.php
   ```

2. Upload files to correct directories
3. **IMPORTANT:** Verify all files are uploaded
4. Set permissions: 755 for directories, 644 for files
5. Activate plugin

⚠️ **Warning:** Manual upload is error-prone and not recommended

## Troubleshooting

### Error: "Failed opening required src/Core/Activator.php"

**Cause:** Missing or incomplete directory structure

**Solution:**
```bash
# Check what's missing
ls -la wp-content/plugins/cart-quote-woocommerce-email/src/Core/

# If directory doesn't exist, extract ZIP again or upload missing files
```

### Error: "Backslash in file path"

**Cause:** ZIP created with Windows paths

**Solution:**
- Rebuild ZIP using Python build script (v1.0.9+)
- Do not use PowerShell Compress-Archive
- Validate ZIP before deployment

### Error: "Permission denied"

**Cause:** Incorrect file permissions

**Solution:**
```bash
chmod -R 755 cart-quote-woocommerce-email
find cart-quote-woocommerce-email -type f -exec chmod 644 {} \;
```

## Rollback

If deployment fails:

```bash
cd wp-content/plugins/
rm -rf cart-quote-woocommerce-email
mv cart-quote-woocommerce-email.backup-* cart-quote-woocommerce-email
wp plugin activate cart-quote-woocommerce-email
```

## Health Check

View plugin health in WordPress Admin:
- Go to **Tools > Site Health**
- Look for "Cart Quote Plugin Integrity" test
- Should show green badge if healthy

## Pre-Deployment Checklist

- [ ] ZIP built with `python build-zip.py`
- [ ] ZIP validated with `python validate-zip.py`
- [ ] Version number correct
- [ ] Backup of existing installation created
- [ ] Deployed to staging first (if available)
- [ ] Directory structure verified
- [ ] File permissions set correctly
- [ ] Plugin activated successfully
- [ ] Health check passes
- [ ] Functionality tested
