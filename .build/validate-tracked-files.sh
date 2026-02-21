#!/bin/bash

#
# Pre-Push Validation Script
#
# Checks that no development files or build artifacts are tracked in git
#

REPO_ROOT="$(git rev-parse --show-toplevel)"

echo "Running pre-push validation..."

# Files/patterns that should NOT be tracked
UNTRACKED_PATTERNS=(
    ".build/"
    "*.zip"
    "*.tmp"
    "*.temp"
    "*.bak"
    "*.new"
    "*~"
    ".DS_Store"
    "Thumbs.db"
    "ehthumbs.db"
    "__pycache__/"
    "*.pyc"
    "*.pyo"
    ".vscode/"
    ".idea/"
    "*.swp"
)

# Files/patterns that should be tracked
REQUIRED_FILES=(
    "cart-quote-woocommerce-email.php"
    "uninstall.php"
    "readme.txt"
    "README.md"
    "src/"
    "templates/"
    "assets/"
)

# Check for untracked files
ERROR_FOUND=false
for pattern in "${UNTRACKED_PATTERNS[@]}"; do
    if git ls-files | grep -q "$pattern"; then
        echo "❌ Found tracked file matching: $pattern"
        ERROR_FOUND=true
    fi
done

# Check for required files
REQUIRED_MISSING=false
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$REPO_ROOT/$file" ] && [ ! -d "$REPO_ROOT/$file" ]; then
        echo "⚠️  Missing required file: $file"
        # Don't block for missing directories (they might not exist yet)
        if [[ ! "$file" =~ /$ ]]; then
            REQUIRED_MISSING=true
        fi
    fi
done

# Output result
echo ""
echo "========================================"
echo "  Validation Result"
echo "========================================"
echo ""

if [ "$ERROR_FOUND" = true ]; then
    echo "❌ PUSH BLOCKED!"
    echo ""
    echo "Untracked or development files found in git."
    echo "Please remove or add to .gitignore before pushing."
    echo ""
    echo "To bypass this check (NOT recommended):"
    echo "  git push --no-verify"
    echo ""
    exit 1
fi

if [ "$REQUIRED_MISSING" = true ]; then
    echo "⚠️  Validation passed with warnings"
    echo ""
    echo "Some required files are missing."
    echo "Proceeding with push..."
    echo ""
fi

if [ "$ERROR_FOUND" = false ] && [ "$REQUIRED_MISSING" = false ]; then
    echo "✓ Validation passed. Proceeding with push..."
    echo ""
fi

exit 0
