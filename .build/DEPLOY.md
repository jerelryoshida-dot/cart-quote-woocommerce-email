# 🚀 Automated Deployment Guide

## Quick Start

### One-Command Deployment

```bash
cd "D:\Projects\Plugin Builder\.build"
python deploy.py
```

That's it! The script will:
1. ✅ Ask you what changed
2. ✅ Auto-increment version (1.0.16 → 1.0.17)
3. ✅ Build plugin ZIP
4. ✅ Update README.md
5. ✅ Commit & push to dev
6. ✅ Merge dev → master
7. ✅ Create GitHub release
8. ✅ Update wiki
9. ✅ Cleanup temp files

---

## Interactive Prompts

The script will ask you 3 questions:

### 1. What changed?
```
❓ What changed in this release?
   > Fixed AJAX syntax error in frontend.js
```

### 2. Change type?
```
❓ Change type:
   [1] 🐛 fix       - Bug fixes
   [2] ✨ feature   - New features
   [3] 🔧 enhancement - Improvements
   [4] 🚀 performance - Optimizations
   [5] 📝 documentation - Docs updates
   [6] 🔒 security  - Security fixes
   > 1
```

### 3. Additional details? (optional)
```
❓ Additional details (optional, press Enter to skip):
   > Added 5 missing closing braces, error handling, quantity rollback
```

The script generates a professional changelog entry:
```
✅ Changelog generated:
   "🐛 Fixed AJAX syntax error in frontend.js: Added 5 missing closing braces..."
```

---

## Command Options

### Full Deployment (Default)
```bash
python deploy.py
```
**Does:** dev → master → release → wiki

---

### Dry-Run Mode (Preview Only)
```bash
python deploy.py --dry-run
```
**Shows what would happen without making any changes**

**Output:**
```
🔍 DRY-RUN MODE - No changes will be made

Would execute:
  ✓ Update version: 1.0.16 → 1.0.17
  ✓ Build ZIP: cart-quote-woocommerce-email-v1.0.17.zip
  ✓ Update README.md
  ✓ Commit: "v1.0.17: Fixed AJAX error"
  ✓ Push to dev
  ✓ Merge to master
  ✓ Create release v1.0.17
  ✓ Update wiki
```

---

### Push to Dev Only
```bash
python deploy.py --dev-only
```
**Does:** Update → Build → Commit → Push to dev
**Skips:** Merge to master, release, wiki

**Use case:** Testing changes on dev branch before deploying to production

---

### Skip Wiki Update
```bash
python deploy.py --no-wiki
```
**Does:** Everything except wiki update
**Useful:** When wiki is temporarily unavailable

---

### Skip Release Creation
```bash
python deploy.py --no-release
```
**Does:** Push to master but don't create GitHub release
**Useful:** For minor commits that don't warrant a release

---

### Update Docs Only
```bash
python deploy.py --docs-only
```
**Does:** Update README.md, version files
**Skips:** Git operations, build, release

**Use case:** Fixing documentation typos

---

## Deployment Workflow

### Visual Flow

```
YOU RUN:
  python deploy.py

SCRIPT EXECUTES:

┌─────────────────────────────────────┐
│ [1/10] Validate environment         │
│ ✅ Git, GitHub CLI, Python          │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ [2/10] Gather information           │
│ Current: 1.0.16 → New: 1.0.17       │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ [3/10] Update versions              │
│ ✅ 4 files updated                  │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ [4/10] Build plugin ZIP             │
│ ✅ ZIP created & validated          │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ [5/10] Update documentation         │
│ ✅ README.md updated                │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ [6/10] Commit & push to dev         │
│ ✅ Changes pushed to origin/dev     │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ [7/10] Merge dev → master           │
│ ✅ Merged & pushed to master        │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ [8/10] Create GitHub release        │
│ ✅ Release v1.0.17 created          │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ [9/10] Update wiki                  │
│ ✅ Wiki updated & pushed            │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│ [10/10] Cleanup                     │
│ ✅ Temp files deleted               │
└─────────────────────────────────────┘
              ↓
          ✅ DONE!
```

---

## Files Updated Automatically

### Version Files (4 files)
- `cart-quote-woocommerce-email.php` (header + constant)
- `src/Core/Plugin.php` (class property)
- `tests/phpunit/bootstrap.php` (test constant)
- `README.md` (version badge)

### Documentation Files
- `README.md` (Releases table)
- GitHub Wiki: `Update-Log.md`

### Build Artifacts
- `output/cart-quote-woocommerce-email-v{version}.zip`
- `release-notes-v{version}.md` (temporary)

---

## Configuration

Edit `.build/deploy-config.json` to customize behavior:

```json
{
  "repository": {
    "dev_branch": "dev",
    "master_branch": "master"
  },
  "version": {
    "auto_increment": true,
    "increment_type": "patch"
  },
  "prompts": {
    "confirm_before_push": true
  }
}
```

---

## Example Output

### Successful Deployment

```
🚀 Cart Quote WooCommerce - Automated Deployment
═══════════════════════════════════════════════

[1/10] 🔍 Validating environment...
       ✅ Git available
       ✅ GitHub CLI available
       ✅ Python 3.14.3 detected
       ✅ Current branch: master

[2/10] 📝 Gathering information...
       Current version: 1.0.16
       New version: 1.0.17

📋 Deployment Information
════════════════════════

❓ What changed in this release?
   > Fixed critical AJAX error

❓ Change type:
   [1] 🐛 fix       - Bug fixes
   > 1

❓ Additional details (optional):
   > Added missing closing braces, error handling

✅ Changelog generated:
   "🐛 Fixed critical AJAX error: Added missing closing braces..."

🔄 Deployment Plan
   ├─ Update version: 1.0.16 → 1.0.17
   ├─ Build ZIP: cart-quote-woocommerce-email-v1.0.17.zip
   ├─ Update README.md
   ├─ Commit & push to dev
   ├─ Merge dev → master
   ├─ Create GitHub release v1.0.17
   ├─ Update wiki
   └─ Cleanup temp files

⚠️  This will push changes to GitHub. Continue? (y/n): y

🚀 Starting deployment...

[3/10] 🔢 Updating versions to 1.0.17...
       ✅ cart-quote-woocommerce-email.php updated
       ✅ Plugin.php updated
       ✅ bootstrap.php updated

[4/10] 📦 Building plugin ZIP...
       ✅ ZIP created: cart-quote-woocommerce-email-v1.0.17.zip
       ✅ Validation passed

[5/10] 📄 Updating documentation...
       ✅ README.md Releases table updated
       ✅ README.md version badge updated

[6/10] 💾 Committing and pushing to dev...
       ✅ Committed: v1.0.17: Fixed critical AJAX error
       ✅ Pushed to origin/dev

[7/10] 🔀 Merging dev → master...
       ✅ Checked out master
       ✅ Merged dev → master
       ✅ Pushed to origin/master

[8/10] 🎉 Creating GitHub release...
       ✅ Release v1.0.17 created
       ✅ ZIP attached to release

[9/10] 📚 Updating wiki...
       ✅ Wiki updated
       ✅ Update-Log.md updated
       ✅ Wiki pushed

[10/10] 🧹 Cleaning up...
        ✅ Deleted release-notes-v1.0.17.md

═══════════════════════════════════════════════
✅ DEPLOYMENT SUCCESSFUL!
═══════════════════════════════════════════════

📊 Summary:
   Version: 1.0.16 → 1.0.17
   Branch: dev → master
   Release: v1.0.17
   Wiki: Updated
   Total time: 45 seconds

🔗 Links:
   Release: https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v1.0.17
   Wiki: https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/wiki/Update-Log
```

---

## Troubleshooting

### Error: "Git not found"
**Solution:** Install Git for Windows from https://git-scm.com

---

### Error: "GitHub CLI not found"
**Solution:** Install GitHub CLI from https://cli.github.com

---

### Error: "Failed to push to dev"
**Cause:** Network issue or authentication problem

**Solution:**
```bash
gh auth login
```

---

### Error: "Failed to merge dev → master"
**Cause:** Merge conflict

**Solution:**
```bash
# Manually resolve conflict
git checkout master
git merge dev
# Resolve conflicts
git add .
git commit
git push origin master
```

---

### Error: "Wiki update failed"
**Solution:** Wiki update is non-critical, you can skip it:
```bash
python deploy.py --no-wiki
```

Then manually update wiki later.

---

## Advanced Usage

### Custom Version Number

Instead of auto-increment, you can specify a version:

1. Edit `deploy-config.json`:
```json
{
  "version": {
    "auto_increment": false
  }
}
```

2. Manually update version before running deploy.py

---

### Skip Confirmation Prompt

Edit `deploy-config.json`:
```json
{
  "prompts": {
    "confirm_before_push": false
  }
}
```

---

### Keep Build Artifacts

Edit `deploy-config.json`:
```json
{
  "cleanup": {
    "delete_temp_zip": false,
    "delete_release_notes": false
  }
}
```

---

## Best Practices

### 1. Always Test on Dev First
```bash
# Push to dev and test
python deploy.py --dev-only

# After testing, deploy to master
python deploy.py --no-release
```

---

### 2. Use Dry-Run Before Important Releases
```bash
# Preview changes
python deploy.py --dry-run

# If everything looks good, run for real
python deploy.py
```

---

### 3. Write Clear Changelog Messages
**Good:**
- "Fixed AJAX syntax error causing cart updates to fail"
- "Added Google Calendar OAuth token refresh"
- "Improved performance with database caching"

**Bad:**
- "Fixed bug"
- "Updates"
- "Changes"

---

### 4. Group Related Changes
Don't deploy after every tiny change. Group related changes into logical releases:

```bash
# Make multiple small fixes
# Then deploy once with comprehensive changelog
python deploy.py
> Fixed AJAX error, updated validation, improved error messages
```

---

## Quick Reference Card

| Command | What It Does |
|---------|--------------|
| `python deploy.py` | Full deployment (dev → master → release → wiki) |
| `python deploy.py --dry-run` | Preview without changes |
| `python deploy.py --dev-only` | Push to dev only |
| `python deploy.py --no-wiki` | Skip wiki update |
| `python deploy.py --no-release` | Skip GitHub release |
| `python deploy.py --docs-only` | Update docs only |

---

## Support

**Issues?** Check:
1. GitHub repo: https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email
2. AGENTS.md for detailed workflow documentation
3. deploy-config.json for configuration options

**Questions?** The script provides helpful error messages and suggestions.
