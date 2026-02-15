#!/bin/bash

# Quote Form Widget Enhancement Verification Script
# This script verifies that all enhancements are properly implemented

echo "======================================================"
echo "Quote Form Widget Enhancement Verification"
echo "======================================================"
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the Plugin Builder directory
if [ ! -f "Plugin Builder/assets/css/frontend.css" ]; then
    echo -e "${RED}Error: Plugin Builder directory not found${NC}"
    exit 1
fi

cd "Plugin Builder"

# Test 1: CSS File Contains Meeting Fields Styles
echo -e "${YELLOW}Test 1: Checking CSS file for meeting fields styling...${NC}"
if grep -q "\.cart-quote-meeting-fields" assets/css/frontend.css; then
    echo -e "${GREEN}✓ Meeting fields CSS found${NC}"
else
    echo -e "${RED}✗ Meeting fields CSS missing${NC}"
fi

if grep -q "\.slide-down" assets/css/frontend.css; then
    echo -e "${GREEN}✓ Slide down animation CSS found${NC}"
else
    echo -e "${RED}✗ Slide down animation CSS missing${NC}"
fi

if grep -q "\.slide-up" assets/css/frontend.css; then
    echo -e "${GREEN}✓ Slide up animation CSS found${NC}"
else
    echo -e "${RED}✗ Slide up animation CSS missing${NC}"
fi

if grep -q "\.error-message-container" assets/css/frontend.css; then
    echo -e "${GREEN}✓ Error message container CSS found${NC}"
else
    echo -e "${RED}✗ Error message container CSS missing${NC}"
fi

echo ""

# Test 2: JavaScript File Contains Enhanced Functions
echo -e "${YELLOW}Test 2: Checking JavaScript for enhanced functions...${NC}"
if grep -q "function showError" assets/js/frontend.js; then
    echo -e "${GREEN}✓ showError function found${NC}"
else
    echo -e "${RED}✗ showError function missing${NC}"
fi

if grep -q "function hideError" assets/js/frontend.js; then
    echo -e "${GREEN}✓ hideError function found${NC}"
else
    echo -e "${RED}✗ hideError function missing${NC}"
fi

if grep -q "function isValidEmail" assets/js/frontend.js; then
    echo -e "${GREEN}✓ isValidEmail function found${NC}"
else
    echo -e "${RED}✗ isValidEmail function missing${NC}"
fi

if grep -q "\.stop\(true, true\)" assets/js/frontend.js; then
    echo -e "${GREEN}✓ Animation stop method found${NC}"
else
    echo -e "${RED}✗ Animation stop method missing${NC}"
fi

echo ""

# Test 3: Quote_Form_Widget.php Contains ARIA Attributes
echo -e "${YELLOW}Test 3: Checking Quote_Form_Widget.php for ARIA attributes...${NC}"
if grep -q 'aria-hidden="false"' src/Elementor/Quote_Form_Widget.php; then
    echo -e "${GREEN}✓ aria-hidden attribute found${NC}"
else
    echo -e "${RED}✗ aria-hidden attribute missing${NC}"
fi

if grep -q 'aria-required="true"' src/Elementor/Quote_Form_Widget.php; then
    echo -e "${GREEN}✓ aria-required attribute found${NC}"
else
    echo -e "${RED}✗ aria-required attribute missing${NC}"
fi

if grep -q "role=\"region\"" src/Elementor/Quote_Form_Widget.php; then
    echo -e "${GREEN}✓ role=\"region\" found${NC}"
else
    echo -e "${RED}✗ role=\"region\" missing${NC}"
fi

if grep -q "field-hint" src/Elementor/Quote_Form_Widget.php; then
    echo -e "${GREEN}✓ Field hint found${NC}"
else
    echo -e "${RED}✗ Field hint missing${NC}"
fi

echo ""

# Test 4: Template File Contains Consistent Enhancements
echo -e "${YELLOW}Test 4: Checking template file for enhancements...${NC}"
if grep -q "aria-hidden=\"false\"" templates/frontend/quote-form.php; then
    echo -e "${GREEN}✓ Template has aria-hidden attribute${NC}"
else
    echo -e "${RED}✗ Template missing aria-hidden attribute${NC}"
fi

if grep -q "aria-required" templates/frontend/quote-form.php; then
    echo -e "${GREEN}✓ Template has aria-required attribute${NC}"
else
    echo -e "${RED}✗ Template missing aria-required attribute${NC}"
fi

if grep -q "field-hint" templates/frontend/quote-form.php; then
    echo -e "${GREEN}✓ Template has field hint${NC}"
else
    echo -e "${RED}✗ Template missing field hint${NC}"
fi

echo ""

# Test 5: Enhanced Validation Logic
echo -e "${YELLOW}Test 5: Checking enhanced validation logic...${NC}"
if grep -q "Date Error:" assets/js/frontend.js; then
    echo -e "${GREEN}✓ Date error message found${NC}"
else
    echo -e "${RED}✗ Date error message missing${NC}"
fi

if grep -q "Email Error:" assets/js/frontend.js; then
    echo -e "${GREEN}✓ Email error message found${NC}"
else
    echo -e "${RED}✗ Email error message missing${NC}"
fi

if grep -q "Phone Error:" assets/js/frontend.js; then
    echo -e "${GREEN}✓ Phone error message found${NC}"
else
    echo -e "${RED}✗ Phone error message missing${NC}"
fi

if grep -q "selectedDate < today" assets/js/frontend.js; then
    echo -e "${GREEN}✓ Future date validation found${NC}"
else
    echo -e "${RED}✗ Future date validation missing${NC}"
fi

echo ""

# Test 6: Enhanced Animations
echo -e "${YELLOW}Test 6: Checking enhanced animations...${NC}"
if grep -q "easeInOutCubic" assets/js/frontend.js; then
    echo -e "${GREEN}✓ EaseInOutCubic animation found${NC}"
else
    echo -e "${RED}✗ EaseInOutCubic animation missing${NC}"
fi

if grep -q "scrollTop()" assets/js/frontend.js; then
    echo -e "${GREEN}✓ Scroll to error functionality found${NC}"
else
    echo -e "${RED}✗ Scroll to error functionality missing${NC}"
fi

if grep -q "shake" assets/css/frontend.css; then
    echo -e "${GREEN}✓ Shake animation found${NC}"
else
    echo -e "${RED}✗ Shake animation missing${NC}"
fi

echo ""

# Test 7: Focus Management
echo -e "${YELLOW}Test 7: Checking focus management...${NC}"
if grep -q "\.focus()" assets/js/frontend.js; then
    echo -e "${GREEN}✓ Focus functionality found${NC}"
else
    echo -e "${RED}✗ Focus functionality missing${NC}"
fi

if grep -q "focused-field" assets/css/frontend.css; then
    echo -e "${GREEN}✓ Focused field styling found${NC}"
else
    echo -e "${RED}✗ Focused field styling missing${NC}"
fi

echo ""

# Summary
echo "======================================================"
echo -e "${GREEN}Verification Complete!${NC}"
echo "======================================================"
echo ""
echo "All tests completed. Review the results above for any failures."
echo ""
echo "Files modified:"
echo "  - assets/css/frontend.css (Enhanced styles)"
echo "  - assets/js/frontend.js (Enhanced validation and animations)"
echo "  - src/Elementor/Quote_Form_Widget.php (ARIA attributes)"
echo "  - templates/frontend/quote-form.php (Template consistency)"
echo ""
echo "Documentation: Plugin Builder/MEETING_FEATURE_ENHANCEMENT_SUMMARY.md"
echo "======================================================"
