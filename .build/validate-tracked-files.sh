#!/bin/bash
#
# Validate that only plugin files are tracked in Git
# Development files should be local-only
#

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================${NC}"
echo -e "${BLUE}  Git Repository Validation${NC}"
echo -e "${BLUE}========================================${NC}"
echo ""

# Define blocked patterns (dev files that should NOT be on GitHub)
BLOCKED_PATTERNS=(
  "^\\.github/"
  "^\\.gitignore$"
  "^\\.others/"
  "^tests/"
  "^build-tests\\.sh$"
  "^\\.build/"
  ".*\\.zip$"
  ".*\\.py[co]?$"
  "DEPLOYMENT.*\\.md$"
  "RELEASE_WORKFLOW\\.md$"
  "wiki-temp/"
  ".*\\.bak$"
)

# Get all tracked files
TRACKED_FILES=$(git ls-files)
TOTAL_FILES=$(echo "$TRACKED_FILES" | wc -l)

echo "📊 Total tracked files: $TOTAL_FILES"
echo ""

# Check for blocked files
VIOLATIONS=()
while IFS= read -r file; do
    for pattern in "${BLOCKED_PATTERNS[@]}"; do
        if echo "$file" | grep -qE "$pattern"; then
            VIOLATIONS+=("$file")
            break
        fi
    done
done <<< "$TRACKED_FILES"

# Report results
if [ ${#VIOLATIONS[@]} -eq 0 ]; then
    echo -e "${GREEN}✓ VALIDATION PASSED!${NC}"
    echo ""
    echo "All tracked files are valid plugin files."
    echo "No development files found in Git tracking."
    echo ""
    echo "Plugin files tracked: $TOTAL_FILES"
    exit 0
else
    echo -e "${RED}✗ VALIDATION FAILED!${NC}"
    echo ""
    echo -e "${YELLOW}Development files are tracked in Git:${NC}"
    for file in "${VIOLATIONS[@]}"; do
        echo -e "  ${RED}✗${NC} $file"
    done
    echo ""
    echo "These files should be local-only (not on GitHub)."
    echo ""
    echo -e "${YELLOW}To fix, run:${NC}"
    echo ""
    echo "  # Remove individual files:"
    for file in "${VIOLATIONS[@]}"; do
        if [[ "$file" == *"/" ]]; then
            echo "  git rm -r --cached \"$file\""
        else
            echo "  git rm --cached \"$file\""
        fi
    done
    echo ""
    echo "  # Then commit the changes:"
    echo "  git commit -m \"Remove development files from Git tracking\""
    echo ""
    exit 1
fi
