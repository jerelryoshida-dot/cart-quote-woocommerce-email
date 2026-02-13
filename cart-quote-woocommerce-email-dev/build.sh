#!/bin/bash
# Build Distribution Package for Cart Quote WooCommerce & Email
# ==============================================================
# Usage: ./build.sh [version]
# Example: ./build.sh 1.0.6

set -e

VERSION=${1:-"1.0.6"}
PLUGIN_NAME="cart-quote-woocommerce-email"
DIST_DIR="dist"
PLUGIN_DIR="plugin"

echo "=========================================="
echo "Building ${PLUGIN_NAME} v${VERSION}"
echo "=========================================="

# Get script directory
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$SCRIPT_DIR"

# Clean previous build
echo "Cleaning previous build..."
rm -rf ${DIST_DIR}/
rm -f ${PLUGIN_NAME}-*.zip

# Create distribution directory
echo "Creating distribution..."
mkdir -p ${DIST_DIR}/${PLUGIN_NAME}

# Copy plugin files
echo "Copying plugin files..."
cp -r ${PLUGIN_DIR}/* ${DIST_DIR}/${PLUGIN_NAME}/

# Remove unnecessary files from distribution
echo "Cleaning distribution..."
rm -f ${DIST_DIR}/${PLUGIN_NAME}/.gitignore 2>/dev/null || true
rm -f ${DIST_DIR}/${PLUGIN_NAME}/.gitattributes 2>/dev/null || true
rm -rf ${DIST_DIR}/${PLUGIN_NAME}/vendor/ 2>/dev/null || true
rm -rf ${DIST_DIR}/${PLUGIN_NAME}/node_modules/ 2>/dev/null || true
rm -f ${DIST_DIR}/${PLUGIN_NAME}/composer.json 2>/dev/null || true
rm -f ${DIST_DIR}/${PLUGIN_NAME}/composer.lock 2>/dev/null || true
rm -f ${DIST_DIR}/${PLUGIN_NAME}/package.json 2>/dev/null || true
rm -f ${DIST_DIR}/${PLUGIN_NAME}/package-lock.json 2>/dev/null || true
rm -f ${DIST_DIR}/${PLUGIN_NAME}/*.log 2>/dev/null || true
rm -f ${DIST_DIR}/${PLUGIN_NAME}/*.cache 2>/dev/null || true

# Create zip archive
echo "Creating zip archive..."
cd ${DIST_DIR}
zip -r ../${PLUGIN_NAME}-${VERSION}.zip ${PLUGIN_NAME} -x "*.DS_Store" -x "*__MACOSX*"
cd ..

# Calculate checksum
CHECKSUM=$(sha256sum ${PLUGIN_NAME}-${VERSION}.zip 2>/dev/null || sha256 ${PLUGIN_NAME}-${VERSION}.zip 2>/dev/null || echo "N/A")

# Cleanup
echo "Cleaning up..."
rm -rf ${DIST_DIR}/

# Get file size
FILE_SIZE=$(ls -lh ${PLUGIN_NAME}-${VERSION}.zip | awk '{print $5}')

echo "=========================================="
echo "Build Complete!"
echo "=========================================="
echo "File:     ${PLUGIN_NAME}-${VERSION}.zip"
echo "Size:     ${FILE_SIZE}"
echo "SHA256:   ${CHECKSUM}"
echo "=========================================="
