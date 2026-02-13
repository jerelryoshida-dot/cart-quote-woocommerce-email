#!/bin/bash
# SQLMap Security Testing Script
# ===============================
# Automated SQL injection testing for Cart Quote WooCommerce & Email plugin
#
# Usage: ./run-sqlmap.sh [target_url]
# Example: ./run-sqlmap.sh http://localhost

set -e

TARGET="${1:-http://localhost}"
AJAX_URL="${TARGET}/wp-admin/admin-ajax.php"
OUTPUT_DIR="./sqlmap-results"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)

echo "=========================================="
echo "SQLMap Security Testing"
echo "Cart Quote WooCommerce & Email Plugin"
echo "=========================================="
echo "Target: ${TARGET}"
echo "AJAX URL: ${AJAX_URL}"
echo "Output: ${OUTPUT_DIR}"
echo "Timestamp: ${TIMESTAMP}"
echo "=========================================="

mkdir -p "${OUTPUT_DIR}"

# Check if sqlmap is available
if ! command -v sqlmap &> /dev/null; then
    echo "Error: sqlmap is not installed."
    echo "Install with: pip install sqlmap"
    exit 1
fi

echo ""
echo "[1/7] Testing quote submission - billing_first_name..."
sqlmap -u "${AJAX_URL}" \
    --data="action=cart_quote_submit&nonce=test&billing_first_name=INJECT&billing_email=test@test.com" \
    -p "billing_first_name" \
    --level=3 --risk=2 --batch --random-agent \
    --output-dir="${OUTPUT_DIR}/${TIMESTAMP}_quote_submit_firstname" \
    2>&1 | tee "${OUTPUT_DIR}/${TIMESTAMP}_01_firstname.log"

echo ""
echo "[2/7] Testing quote submission - billing_email..."
sqlmap -u "${AJAX_URL}" \
    --data="action=cart_quote_submit&nonce=test&billing_first_name=Test&billing_email=INJECT" \
    -p "billing_email" \
    --level=3 --risk=2 --batch --random-agent \
    --output-dir="${OUTPUT_DIR}/${TIMESTAMP}_quote_submit_email" \
    2>&1 | tee "${OUTPUT_DIR}/${TIMESTAMP}_02_email.log"

echo ""
echo "[3/7] Testing cart update - cart_item_key..."
sqlmap -u "${AJAX_URL}" \
    --data="action=cart_quote_update_cart&nonce=test&cart_item_key=INJECT&quantity=1" \
    -p "cart_item_key" \
    --level=3 --risk=2 --batch --random-agent \
    --output-dir="${OUTPUT_DIR}/${TIMESTAMP}_cart_update" \
    2>&1 | tee "${OUTPUT_DIR}/${TIMESTAMP}_03_cart_update.log"

echo ""
echo "[4/7] Testing admin status update - id..."
sqlmap -u "${AJAX_URL}" \
    --data="action=cart_quote_admin_update_status&nonce=test&id=INJECT&status=pending" \
    -p "id" \
    --level=3 --risk=2 --batch --random-agent \
    --output-dir="${OUTPUT_DIR}/${TIMESTAMP}_admin_status" \
    2>&1 | tee "${OUTPUT_DIR}/${TIMESTAMP}_04_admin_status.log"

echo ""
echo "[5/7] Testing admin save notes - notes..."
sqlmap -u "${AJAX_URL}" \
    --data="action=cart_quote_admin_save_notes&nonce=test&id=1&notes=INJECT" \
    -p "notes" \
    --level=3 --risk=2 --batch --random-agent \
    --output-dir="${OUTPUT_DIR}/${TIMESTAMP}_save_notes" \
    2>&1 | tee "${OUTPUT_DIR}/${TIMESTAMP}_05_notes.log"

echo ""
echo "[6/7] Testing CSV export - status filter..."
sqlmap -u "${AJAX_URL}?action=cart_quote_admin_export_csv&nonce=test&status=INJECT" \
    -p "status" \
    --level=3 --risk=2 --batch --random-agent \
    --output-dir="${OUTPUT_DIR}/${TIMESTAMP}_csv_status" \
    2>&1 | tee "${OUTPUT_DIR}/${TIMESTAMP}_06_csv_status.log"

echo ""
echo "[7/7] Testing CSV export - date filters..."
sqlmap -u "${AJAX_URL}?action=cart_quote_admin_export_csv&nonce=test&date_from=INJECT&date_to=2024-12-31" \
    -p "date_from" \
    --level=3 --risk=2 --batch --random-agent \
    --output-dir="${OUTPUT_DIR}/${TIMESTAMP}_csv_date" \
    2>&1 | tee "${OUTPUT_DIR}/${TIMESTAMP}_07_csv_date.log"

echo ""
echo "=========================================="
echo "SQLMap Testing Complete!"
echo "Results saved to: ${OUTPUT_DIR}"
echo "=========================================="
echo ""
echo "Review the log files for any detected vulnerabilities."
echo "Files with 'vulnerable' in the name indicate issues found."
