# Deployment Checklist

## Before Deployment

- [ ] Run `python .build/validate-zip.py <zip-file>`
- [ ] Confirm ZIP validation passes
- [ ] Verify version number matches release tag
- [ ] Check changelog for breaking changes
- [ ] Test on staging environment (if available)

## During Deployment

- [ ] Backup existing plugin directory
- [ ] Upload ZIP file to server
- [ ] Extract ZIP on server (not locally)
- [ ] Verify directory structure: `ls -la wp-content/plugins/cart-quote-woocommerce-email/src/Core/`
- [ ] Set correct permissions: `chmod 755` for directories, `chmod 644` for files
- [ ] Remove ZIP file from server

## After Deployment

- [ ] Activate plugin in WordPress admin
- [ ] Check for PHP errors in debug log
- [ ] Verify plugin version in WordPress admin
- [ ] Test critical functionality:
  - [ ] Quote submission form loads
  - [ ] Quote can be submitted
  - [ ] Admin can view quotes
  - [ ] Status updates work
- [ ] Clear any caches
- [ ] Remove backup directory (after testing)

## Rollback Procedure

If deployment fails:

```bash
# Restore from backup
cd wp-content/plugins/
rm -rf cart-quote-woocommerce-email
mv cart-quote-woocommerce-email.backup cart-quote-woocommerce-email
```
