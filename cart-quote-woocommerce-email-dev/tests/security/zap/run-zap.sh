#!/bin/bash
# OWASP ZAP Security Testing Script
# ==================================
# Automated security scanning for Cart Quote WooCommerce & Email plugin
#
# Usage: ./run-zap.sh [target_url]
# Example: ./run-zap.sh http://localhost

set -e

TARGET="${1:-http://localhost}"
OUTPUT_DIR="./zap-reports"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
ZAP_PORT=8080
ZAP_HOST=localhost

echo "=========================================="
echo "OWASP ZAP Security Testing"
echo "Cart Quote WooCommerce & Email Plugin"
echo "=========================================="
echo "Target: ${TARGET}"
echo "ZAP Host: ${ZAP_HOST}:${ZAP_PORT}"
echo "Output: ${OUTPUT_DIR}"
echo "Timestamp: ${TIMESTAMP}"
echo "=========================================="

mkdir -p "${OUTPUT_DIR}"

# Check if ZAP is running
check_zap() {
    if curl -s "http://${ZAP_HOST}:${ZAP_PORT}" > /dev/null 2>&1; then
        echo "ZAP is running on ${ZAP_HOST}:${ZAP_PORT}"
        return 0
    else
        echo "ZAP is not running. Please start ZAP first:"
        echo "  - GUI: Open OWASP ZAP application"
        echo "  - Daemon: zap.sh -daemon -port ${ZAP_PORT}"
        return 1
    fi
}

# Start ZAP scan
start_scan() {
    local scan_name=$1
    local url=$2
    
    echo "Starting scan: ${scan_name}"
    echo "URL: ${url}"
    
    scan_id=$(curl -s "http://${ZAP_HOST}:${ZAP_PORT}/JSON/ascan/action/scan/" \
        --data-urlencode "url=${url}" \
        --data-urlencode "scanPolicyName=CartQuote-Security-Policy" \
        --data-urlencode "method=POST" \
        --data-urlencode "postData=nonce=test&action=${scan_name}" \
        | jq -r '.scan')
    
    echo "Scan ID: ${scan_id}"
    echo "${scan_id}"
}

# Wait for scan to complete
wait_for_scan() {
    local scan_id=$1
    local status="0"
    
    echo "Waiting for scan to complete..."
    
    while [ "$status" != "100" ]; do
        status=$(curl -s "http://${ZAP_HOST}:${ZAP_PORT}/JSON/ascan/view/status/?scanId=${scan_id}" | jq -r '.status')
        echo "Progress: ${status}%"
        sleep 2
    done
    
    echo "Scan complete!"
}

# Generate report
generate_report() {
    local format=$1
    local filename="${OUTPUT_DIR}/${TIMESTAMP}_report.${format,,}"
    
    echo "Generating ${format} report..."
    
    case $format in
        HTML)
            curl -s "http://${ZAP_HOST}:${ZAP_PORT}/OTHER/core/other/htmlreport/" \
                > "${filename}"
            ;;
        JSON)
            curl -s "http://${ZAP_HOST}:${ZAP_PORT}/OTHER/core/other/jsonreport/" \
                > "${filename}"
            ;;
        XML)
            curl -s "http://${ZAP_HOST}:${ZAP_PORT}/OTHER/core/other/xmlreport/" \
                > "${filename}"
            ;;
    esac
    
    echo "Report saved: ${filename}"
}

# Main execution
if ! check_zap; then
    exit 1
fi

echo ""
echo "Step 1: Spidering target..."
spider_id=$(curl -s "http://${ZAP_HOST}:${ZAP_PORT}/JSON/spider/action/scan/" \
    --data-urlencode "url=${TARGET}" \
    --data-urlencode "maxChildren=10" \
    | jq -r '.scan')

echo "Spider ID: ${spider_id}"

# Wait for spider
sleep 10

echo ""
echo "Step 2: Scanning AJAX endpoints..."

# Define endpoints to scan
declare -a endpoints=(
    "cart_quote_submit"
    "cart_quote_update_cart"
    "cart_quote_remove_item"
    "cart_quote_get_cart"
    "cart_quote_admin_update_status"
    "cart_quote_admin_create_event"
    "cart_quote_admin_resend_email"
    "cart_quote_admin_save_notes"
)

for endpoint in "${endpoints[@]}"; do
    echo ""
    echo "Scanning: ${endpoint}"
    scan_id=$(curl -s "http://${ZAP_HOST}:${ZAP_PORT}/JSON/ascan/action/scan/" \
        --data-urlencode "url=${TARGET}/wp-admin/admin-ajax.php" \
        --data-urlencode "scanPolicyName=" \
        | jq -r '.scan')
    echo "Scan started: ${scan_id}"
    sleep 5
done

echo ""
echo "Step 3: Waiting for all scans to complete..."
sleep 30

echo ""
echo "Step 4: Generating reports..."
generate_report HTML
generate_report JSON
generate_report XML

echo ""
echo "Step 5: Getting alerts summary..."
curl -s "http://${ZAP_HOST}:${ZAP_PORT}/JSON/alert/view/alertsSummary/" \
    | jq '.' > "${OUTPUT_DIR}/${TIMESTAMP}_alerts_summary.json"

# Get alert counts by risk
high_count=$(curl -s "http://${ZAP_HOST}:${ZAP_PORT}/JSON/alert/view/alertsSummary/" | jq -r '.alertsSummary.High // 0')
medium_count=$(curl -s "http://${ZAP_HOST}:${ZAP_PORT}/JSON/alert/view/alertsSummary/" | jq -r '.alertsSummary.Medium // 0')
low_count=$(curl -s "http://${ZAP_HOST}:${ZAP_PORT}/JSON/alert/view/alertsSummary/" | jq -r '.alertsSummary.Low // 0')

echo ""
echo "=========================================="
echo "Scan Complete!"
echo "=========================================="
echo "High Risk Alerts:   ${high_count}"
echo "Medium Risk Alerts: ${medium_count}"
echo "Low Risk Alerts:    ${low_count}"
echo ""
echo "Reports saved to: ${OUTPUT_DIR}"
echo "=========================================="

# Exit with error if high risk alerts found
if [ "$high_count" -gt 0 ]; then
    echo "WARNING: High risk vulnerabilities detected!"
    exit 1
fi
