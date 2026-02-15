#!/bin/bash
#
# Install Git hooks for plugin repository validation
#

echo "========================================"
echo "  Git Hooks Installer"
echo "========================================"
echo ""

# Ensure we're in the Plugin Builder directory
if [ ! -d ".git" ]; then
    echo "❌ Error: Not in a Git repository"
    echo "Run this script from the Plugin Builder root directory"
    exit 1
fi

# Create hooks directory if it doesn't exist
mkdir -p .git/hooks

# Copy pre-push hook
if [ -f ".build/hooks/pre-push" ]; then
    cp .build/hooks/pre-push .git/hooks/pre-push
    chmod +x .git/hooks/pre-push
    echo "✓ Installed pre-push hook"
else
    echo "❌ Error: .build/hooks/pre-push not found"
    exit 1
fi

echo ""
echo "✅ Git hooks installed successfully!"
echo ""
echo "Active hooks:"
echo "  - pre-push: Validates tracked files before every push"
echo ""
echo "The hook will automatically run before 'git push'."
echo "Only plugin files will be allowed on GitHub."
echo ""
