# Automated Release Documentation Workflow

## Overview

This document describes the automated release documentation update system that ensures README.md and Wiki Update-Log are automatically updated during the build process.

## Components

### 1. create-release-notes.py

**Purpose:** Generate changelog and version details markdown

**Usage:**
```bash
python create-release-notes.py \
  --version "1.0.13" \
  --changelog "Critical fix: deployment validation system" \
  --type "fix"
```

**Arguments:**
- `--version` (required): Version number (e.g., 1.0.13)
- `--changelog` (required): Brief changelog message
- `--type` (required): Change type - fix, feature, enhancement, performance, documentation, security
- `--title` (optional): Release title (defaults to changelog message)
- `--overview` (optional): Release overview/description
- `--output-dir` (optional): Output directory (default: current directory)

**Output Files:**
- `changelog-entry.md` - Changelog entry for Release History table
- `version-details.md` - Placeholder for Version Details section

**Change Type Icons:**
| Type | Icon | Usage |
|------|-------|--------|
| fix | 🛠️ | Bug fixes, critical issues |
| feature | ✨ | New functionality |
| enhancement | 🔧 | Improvements, refactoring |
| performance | 🚀 | Performance optimizations |
| documentation | 📝 | Docs, readme |
| security | 🔒 | Security fixes |

### 2. build-zip.py (Enhanced)

**Purpose:** Create plugin ZIP with automatic documentation updates

**New Arguments:**
- `--changelog`: Release changelog message for documentation
- `--type`: Type of change for changelog formatting
- `--no-docs`: Skip documentation updates (preserves backward compatibility)

**Usage:**
```bash
# Basic build (no documentation)
python build-zip.py 1.0.14

# Build with documentation update
python build-zip.py 1.0.14 \
  --changelog "Fix: Authentication flow improvements" \
  --type "fix"

# Production build with docs
python build-zip.py --env prod 1.0.14 \
  --changelog "Feature: Multi-currency support" \
  --type "feature"
```

**New Functions:**
- `generate_changelog_entry()` - Generates formatted changelog entry
- `update_readme()` - Updates README.md Releases table
- `update_wiki_updatelog()` - Updates Wiki Update-Log.md
- `update_documentations()` - Orchestrates documentation updates

**Workflow:**
```
build-zip.py
  ↓
Create ZIP
  ↓
Validate ZIP
  ↓
Update README.md (if --changelog provided)
  ↓
Update Wiki Update-Log.md (if --changelog provided)
  ↓
Show wiki push instructions
```

### 3. update-wiki.py

**Purpose:** Standalone script to update wiki without running full build

**Usage:**
```bash
python update-wiki.py \
  --version "1.0.13" \
  --changelog "Critical fix" \
  --type "fix" \
  --details version-details.md
```

**Arguments:**
- `--version` (required): Version number
- `--changelog` (required): Brief changelog message
- `--type` (required): Change type
- `--details` (optional): Path to version details markdown file
- `--wiki-path` (optional): Path to wiki repository (default: D:/Projects/cart-quote-woocommerce-email-wiki)
- `--no-clone` (optional): Skip cloning if wiki already exists

**Features:**
- Clones wiki if not present
- Updates Update-Log.md
- Commits changes
- Provides push commands (doesn't auto-push to avoid auth issues)

### 4. ReleaseWorkflowTest.py

**Purpose:** Integration tests for release workflow

**Tests:**
1. `test_create_release_notes_generates_valid_markdown()` - Validates markdown generation
2. `test_build_zip_updates_readme()` - Tests README update functionality
3. `test_update_wiki_generates_valid_format()` - Validates wiki format

**Run Tests:**
```bash
cd tests/integration
python ReleaseWorkflowTest.py
```

## Complete Build Command Examples

### Basic Build (No Documentation Updates)
```bash
cd .build
python build-zip.py 1.0.14
```

### Build with Documentation Update (New Feature)
```bash
cd .build
python build-zip.py 1.0.14 \
  --changelog "Add multi-currency support" \
  --type "feature"
```

### Build with Bug Fix
```bash
cd .build
python build-zip.py 1.0.14 \
  --changelog "Fix authentication flow" \
  --type "fix"
```

### Production Build with Documentation
```bash
cd .build
python build-zip.py --env prod 1.0.14 \
  --changelog "Performance optimization" \
  --type "performance"
```

## Build Output

```
==================================================
Plugin ZIP Builder
==================================================
Creating cart-quote-woocommerce-email-v1.0.14.zip...
==================================================
Environment: production
Version: 1.0.14
Output:     D:\Projects\.build\output\cart-quote-woocommerce-email-v1.0.14.zip
==================================================
Adding directory: ../src/
Adding directory: ../templates/
Adding directory: ../assets/
Adding file: ../cart-quote-woocommerce-email.php
Adding file: ../readme.txt
Adding file: ../uninstall.php
==================================================
Files added: 40
==================================================
Build Complete!
==================================================
File:     cart-quote-woocommerce-email-v1.0.14.zip
Location: D:\Projects\.build\output
Size:     109.44 KB (0.11 MB)
==================================================
Validating ZIP structure...
Files in ZIP: 40
All excluded patterns respected
[PASS] All validation checks passed
[PASS] No backslashes in paths
[PASS] All required files present
[PASS] Critical directories exist
==================================================
SUCCESS: Plugin built successfully!

==================================================
Updating Documentation...
==================================================
[PASS] README.md updated (added v1.0.14 to Releases table)
[PASS] Update-Log.md updated (added v1.0.14 to Release History)

==================================================
Wiki Push Instructions:
==================================================
To push wiki updates, run:
  cd D:/Projects/cart-quote-woocommerce-email-wiki
  git add Update-Log.md
  git commit -m "Add v1.0.14 to Update Log"
  git push origin master

Or use single command:
  git -C D:/Projects/cart-quote-woocommerce-email-wiki add Update-Log.md && \
       git -C D:/Projects/cart-quote-woocommerce-email-wiki commit -m 'Add v1.0.14 to Update Log' && \
       git -C D:/Projects/cart-quote-woocommerce-email-wiki push origin master
==================================================
```

## Manual Wiki Update (Alternative)

If build-zip.py doesn't update the wiki (e.g., wiki not cloned), use the standalone script:

```bash
cd .build
python update-wiki.py \
  --version "1.0.14" \
  --changelog "Critical fix" \
  --type "fix"
```

This will clone the wiki, update Update-Log.md, and provide push commands.

## Adding Version Details

After the automated update, you need to manually add the full version details to the wiki:

1. **Open Wiki Update-Log**
2. **Find the placeholder:** `### v{version} - {title}`
3. **Replace with full details** using the template
4. **Add Version Details section** with complete information
5. **Commit and push** using the provided commands

## Version Details Template

```markdown
### v{version} - {title}

**Overview:**
[Brief overview of this release - what problem it solves or feature it adds]

**Changes:**

- **Feature 1:** Description of feature 1
  - Technical details
  - Benefits/impact
- **Feature 2:** Description of feature 2
- **Fix 1:** Description of bug fix 1
  - Root cause
  - Solution

**Fixes:**

- Issue 1 description
- Issue 2 description
- Issue 3 description

**Technical Details:**

**Implementation:**
- Files modified: [list of files]
- Lines of code changed: X additions, Y deletions
- New classes/components: [list]

**Database Changes:**
- New tables: [list]
- Schema changes: [description]
- Migration required: [yes/no]

**API Changes:**
- New endpoints: [list]
- Modified endpoints: [list]
- Breaking changes: [list]

**Performance:**
- Query optimization: [details]
- Caching improvements: [details]
- Memory usage: [before] → [after]

**Testing:**
- Unit tests added: X tests
- Integration tests added: Y tests
- Manual testing performed: [yes/no]

**Benefits:**
- User benefit 1
- User benefit 2
- Technical benefit

**Upgrade Notes:**
- Any special instructions for upgrading
- Data migration required: [yes/no]
- Configuration changes: [list]

**Known Issues:**
- Any known issues with this release
- Workarounds if applicable
```

## Best Practices

### 1. Changelog Messages
- Keep it brief and concise
- Start with action verb (Fix, Add, Improve, etc.)
- Focus on user-facing changes
- Include version numbers for dependencies

**Good examples:**
- "Fix: Authentication flow improvements"
- "Add: Multi-currency support"
- "Improve: Database query performance by 50%"
- "Fix: Missing Activator.php error on production"

**Bad examples:**
- "Bug fixes"
- "Various improvements"
- "Update"
- "Changes made"

### 2. Change Types
Use the correct type to ensure proper icon:
- `fix` - Bug fixes, critical issues, regression fixes
- `feature` - New functionality, new user-facing features
- `enhancement` - Improvements, refactoring, code cleanup
- `performance` - Speed improvements, optimization
- `documentation` - README updates, code comments
- `security` - Security fixes, vulnerability patches

### 3. Version Details
Always add comprehensive version details for major releases:
- Explain what was changed
- List technical details
- Include upgrade notes
- Document breaking changes

### 4. Testing
Run integration tests before release:
```bash
cd tests/integration
python ReleaseWorkflowTest.py
```

## Troubleshooting

### Wiki Not Updated

**Problem:** Wiki Update-Log.md not updated after build

**Solutions:**
1. Check wiki path in `update-wiki.py` default
2. Manually run update-wiki.py script
3. Clone wiki manually and edit file

### README Not Updated

**Problem:** README.md Releases table not updated

**Solutions:**
1. Check if README.md exists
2. Look for "Releases" table marker
3. Manually add changelog entry to table

### Wiki Clone Fails

**Problem:** `update-wiki.py` cannot clone wiki

**Solutions:**
1. Use `gh auth status` to verify authentication
2. Clone manually: `gh repo clone jerelryoshida-dot/cart-quote-woocommerce-email.wiki`
3. Use `--no-clone` flag if wiki already exists

## Rollback

If documentation updates are incorrect:

### Revert README.md
```bash
cd "D:\Projects\Plugin Builder"
git checkout HEAD~1 -- README.md
```

### Revert Wiki
```bash
cd "D:\Projects\cart-quote-woocommerce-email-wiki"
git revert HEAD
```

## Backward Compatibility

The `--no-docs` flag preserves the original behavior:
```bash
python build-zip.py 1.0.14 --no-docs
```

This will build the ZIP without updating any documentation.

## Future Enhancements

- [ ] Auto-create GitHub release with changelog
- [ ] Auto-push wiki updates (requires auth token)
- [ ] Generate detailed version details from code changes
- [ ] Integrate with GitHub Actions workflow
- [ ] Add release notes preview before commit
