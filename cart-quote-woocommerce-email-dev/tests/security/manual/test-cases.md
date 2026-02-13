# Security Test Cases - Manual Testing Guide
# ===========================================
# Cart Quote WooCommerce & Email Plugin
# Version: 1.0.0

## Overview
This document contains manual security test cases for the Cart Quote WooCommerce & Email plugin. These tests should be performed regularly during development and before releases.

---

## Test Environment Setup

1. Fresh WordPress installation (latest version)
2. WooCommerce installed and configured
3. Cart Quote plugin installed and activated
4. Test products added to WooCommerce
5. Administrator and subscriber test accounts

---

## Test Cases

### SEC-001: XSS in Customer Name Field
**Severity:** HIGH  
**Category:** Cross-Site Scripting  
**Location:** Quote submission form

**Steps:**
1. Add product to cart
2. Navigate to quote form
3. Enter `<script>alert('XSS')</script>` in First Name field
4. Submit form
5. Check if script executes in admin panel when viewing quote

**Expected Result:** Script tags are stripped/escaped, no alert appears  
**Attack Vectors:**
- `<script>alert(1)</script>`
- `<img src=x onerror=alert(1)>`
- `<svg onload=alert(1)>`
- `javascript:alert(1)`

---

### SEC-002: SQL Injection in Search Field
**Severity:** CRITICAL  
**Category:** SQL Injection  
**Location:** Admin quotes list page

**Steps:**
1. Login as admin
2. Navigate to Cart Quotes > All Quotes
3. Enter `' OR '1'='1` in search field
4. Check if all records are returned or error occurs

**Expected Result:** Search input sanitized, no SQL error displayed  
**Attack Vectors:**
- `' OR '1'='1`
- `'; DROP TABLE wp_cart_quote_submissions;--`
- `' UNION SELECT NULL,NULL,NULL--`

---

### SEC-003: CSRF on Status Update
**Severity:** HIGH  
**Category:** Cross-Site Request Forgery  
**Location:** Admin AJAX - update status

**Steps:**
1. Login as admin
2. Open browser dev tools network tab
3. Update a quote status
4. Copy the AJAX request
5. Create external HTML page with same request (without nonce)
6. Load page as logged-in admin

**Expected Result:** Request rejected due to invalid/missing nonce

---

### SEC-004: Unauthorized Admin Access
**Severity:** HIGH  
**Category:** Broken Access Control  
**Location:** All admin AJAX endpoints

**Steps:**
1. Logout or use incognito window
2. Attempt to access admin-ajax.php with admin actions
3. Try: `POST /wp-admin/admin-ajax.php?action=cart_quote_admin_update_status&id=1&status=closed`

**Expected Result:** 403 Forbidden or redirect to login  
**Test Actions:**
- `cart_quote_admin_update_status`
- `cart_quote_admin_create_event`
- `cart_quote_admin_resend_email`
- `cart_quote_admin_save_notes`
- `cart_quote_admin_export_csv`

---

### SEC-005: CSV Injection in Company Name
**Severity:** MEDIUM  
**Category:** CSV Formula Injection  
**Location:** CSV export functionality

**Steps:**
1. Submit quote with company name: `=SUM(A1:A10)`
2. Submit quote with company name: `+cmd|' /C calc'!A0`
3. Submit quote with company name: `@test.com`
4. Export quotes to CSV
5. Open CSV in Excel/Google Sheets

**Expected Result:** Dangerous characters escaped or quoted  
**Attack Vectors:**
- `=SUM(A1:A10)`
- `+cmd|' /C calc'!A0`
- `-DDE`
- `@test.com`

---

### SEC-006: OAuth State Manipulation
**Severity:** HIGH  
**Category:** OAuth Security  
**Location:** Google Calendar OAuth callback

**Steps:**
1. Navigate to Google Calendar settings page
2. Initiate OAuth flow
3. Intercept callback and modify state parameter
4. Observe error handling

**Expected Result:** Invalid state rejected, no token stored

---

### SEC-007: Token Exposure in Logs
**Severity:** HIGH  
**Category:** Information Disclosure  
**Location:** Google OAuth token storage

**Steps:**
1. Connect Google Calendar
2. Check database options table for token storage
3. Check PHP error logs for token leakage
4. Check browser console for token in responses

**Expected Result:** Tokens encrypted in database, not visible in logs

---

### SEC-008: Mass Assignment in Update
**Severity:** MEDIUM  
**Category:** Mass Assignment  
**Location:** Quote update endpoint

**Steps:**
1. Intercept quote update AJAX request
2. Add unauthorized fields: `is_admin=1`, `role=administrator`
3. Submit modified request
4. Check if unauthorized fields were saved

**Expected Result:** Only allowed fields updated, others ignored

---

### SEC-009: IDOR in Quote Viewing
**Severity:** HIGH  
**Category:** Insecure Direct Object Reference  
**Location:** Quote detail page

**Steps:**
1. Login as Shop Manager (not admin)
2. Access quote detail page with sequential IDs
3. Try: `/wp-admin/admin.php?page=cart-quote-manager&action=view&id=1`
4. Try: `/wp-admin/admin.php?page=cart-quote-manager&action=view&id=2`
5. Continue through all IDs

**Expected Result:** Capability check passes/fails appropriately

---

### SEC-010: XSS in Email Templates
**Severity:** HIGH  
**Category:** Cross-Site Scripting  
**Location:** Email notifications

**Steps:**
1. Submit quote with XSS in all fields
2. Receive email notification
3. View email in client (Gmail, Outlook, etc.)
4. Check if scripts execute

**Expected Result:** All content escaped in HTML email

---

### SEC-011: Rate Limiting on Submission
**Severity:** MEDIUM  
**Category:** Denial of Service  
**Location:** Quote submission endpoint

**Steps:**
1. Create script to submit quotes rapidly
2. Submit 100 requests in quick succession
3. Monitor server resources
4. Check database for duplicate entries

**Expected Result:** Rate limiting prevents abuse

---

### SEC-012: File Upload Security
**Severity:** N/A (not applicable)  
**Category:** File Upload  
**Location:** Plugin doesn't handle file uploads

**Notes:** This plugin does not include file upload functionality.

---

### SEC-013: Information Disclosure in Errors
**Severity:** MEDIUM  
**Category:** Information Disclosure  
**Location:** AJAX error responses

**Steps:**
1. Trigger various errors (invalid nonce, missing params)
2. Check error messages for sensitive info
3. Check for stack traces, database errors

**Expected Result:** Generic error messages, no internal details

---

### SEC-014: Session Security
**Severity:** MEDIUM  
**Category:** Session Management  
**Location:** All admin pages

**Steps:**
1. Check cookie flags (HttpOnly, Secure, SameSite)
2. Test session fixation
3. Check session timeout behavior

**Expected Result:** Secure session configuration

---

### SEC-015: API Endpoint Protection
**Severity:** HIGH  
**Category:** API Security  
**Location:** All AJAX endpoints

**Steps:**
1. Test each AJAX endpoint without authentication
2. Test with invalid nonce
3. Test with expired nonce
4. Test with manipulated nonce

**Expected Result:** All sensitive endpoints require valid nonce/auth

---

## Test Results Template

| Test ID | Date | Tester | Result | Notes |
|---------|------|--------|--------|-------|
| SEC-001 | | | PASS/FAIL | |
| SEC-002 | | | PASS/FAIL | |
| SEC-003 | | | PASS/FAIL | |
| SEC-004 | | | PASS/FAIL | |
| SEC-005 | | | PASS/FAIL | |
| SEC-006 | | | PASS/FAIL | |
| SEC-007 | | | PASS/FAIL | |
| SEC-008 | | | PASS/FAIL | |
| SEC-009 | | | PASS/FAIL | |
| SEC-010 | | | PASS/FAIL | |
| SEC-011 | | | PASS/FAIL | |
| SEC-012 | | | N/A | |
| SEC-013 | | | PASS/FAIL | |
| SEC-014 | | | PASS/FAIL | |
| SEC-015 | | | PASS/FAIL | |

---

## Severity Definitions

- **CRITICAL:** Immediate risk of data breach or system compromise
- **HIGH:** Significant security impact, should be fixed before release
- **MEDIUM:** Moderate impact, should be addressed in near-term
- **LOW:** Minor issue, low risk of exploitation

---

## Reporting Vulnerabilities

If you discover a security vulnerability:

1. Do NOT disclose publicly
2. Email: security@example.com
3. Include: Test ID, steps to reproduce, potential impact
4. Allow 90 days for response before disclosure

---

## References

- [OWASP Top 10](https://owasp.org/Top10/)
- [WordPress Plugin Security Guidelines](https://developer.wordpress.org/plugins/security/)
- [WooCommerce Security Best Practices](https://woocommerce.com/document/security-best-practices/)
