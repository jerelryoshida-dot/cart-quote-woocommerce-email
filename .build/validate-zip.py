#!/usr/bin/env python3
"""
Validate ZIP script for Cart Quote WooCommerce & Email plugin

Validates that a ZIP file is properly formatted for WordPress plugin distribution.
Checks for:
- No backslashes in file paths (critical for Linux compatibility)
- Required plugin files exist
- Correct folder structure

Usage:
    python validate-zip.py <path-to-zip>
    
Example:
    python validate-zip.py ../cart-quote-woocommerce-email-v1.0.36.zip

Exit Codes:
    0 - Validation passed
    1 - Validation failed
"""

import zipfile
import sys
from pathlib import Path


def validate_zip(zip_path):
    """Validate the ZIP file structure and paths"""
    print("=" * 60)
    print("  ZIP Validation")
    print("=" * 60)
    print(f"\nFile: {zip_path}")
    print()
    
    errors = []
    warnings = []
    
    if not zip_path.exists():
        print(f"[ERROR] File does not exist: {zip_path}")
        return False
    
    try:
        with zipfile.ZipFile(zip_path, 'r') as zipf:
            entries = zipf.namelist()
            
            if not entries:
                errors.append("ZIP file is empty")
                return False
            
            print(f"Total entries: {len(entries)}")
            print()
            
            # Check 1: No backslashes in paths
            print("Checking for backslashes in paths...")
            backslash_count = 0
            for entry in entries:
                if '\\' in entry:
                    backslash_count += 1
                    if backslash_count <= 5:  # Show first 5
                        errors.append(f"Backslash in path: {entry}")
            
            if backslash_count > 5:
                errors.append(f"... and {backslash_count - 5} more backslash errors")
            
            if backslash_count == 0:
                print("  [OK] No backslashes found")
            else:
                print(f"  [FAILED] Found {backslash_count} paths with backslashes")
            
            # Check 2: Required files
            print("\nChecking required files...")
            required_files = [
                'cart-quote-woocommerce-email/cart-quote-woocommerce-email.php',
                'cart-quote-woocommerce-email/readme.txt',
                'cart-quote-woocommerce-email/uninstall.php',
            ]
            
            for req_file in required_files:
                if req_file in entries:
                    print(f"  [OK] {req_file}")
                else:
                    errors.append(f"Missing required file: {req_file}")
                    print(f"  [FAILED] Missing: {req_file}")
            
            # Check 3: Folder structure
            print("\nChecking folder structure...")
            root_folder = entries[0].split('/')[0] if '/' in entries[0] else entries[0]
            
            if root_folder == 'cart-quote-woocommerce-email':
                print(f"  [OK] Root folder: {root_folder}")
            else:
                errors.append(f"Wrong root folder: {root_folder} (expected: cart-quote-woocommerce-email)")
                print(f"  [FAILED] Wrong root folder: {root_folder}")
            
            # Check 4: Critical directories
            print("\nChecking critical directories...")
            required_dirs = [
                'cart-quote-woocommerce-email/src/',
                'cart-quote-woocommerce-email/assets/',
                'cart-quote-woocommerce-email/templates/',
            ]
            
            for req_dir in required_dirs:
                dir_entries = [e for e in entries if e.startswith(req_dir)]
                if dir_entries:
                    print(f"  [OK] {req_dir} ({len(dir_entries)} files)")
                else:
                    warnings.append(f"Empty or missing directory: {req_dir}")
                    print(f"  [WARN] Empty or missing: {req_dir}")
            
            # Check 5: File type summary
            print("\nFile type summary:")
            php_files = [e for e in entries if e.endswith('.php')]
            css_files = [e for e in entries if e.endswith('.css')]
            js_files = [e for e in entries if e.endswith('.js')]
            
            print(f"  PHP files: {len(php_files)}")
            print(f"  CSS files: {len(css_files)}")
            print(f"  JS files: {len(js_files)}")
            
            # Check for expected minimum file counts
            if len(php_files) < 15:
                warnings.append(f"Low PHP file count: {len(php_files)} (expected 20+)")
            
    except zipfile.BadZipFile:
        errors.append("Invalid ZIP file format")
    except Exception as e:
        errors.append(f"Error reading ZIP: {str(e)}")
    
    # Print results
    print("\n" + "=" * 60)
    
    if errors:
        print("[FAILED] VALIDATION FAILED")
        print()
        print("Errors:")
        for error in errors:
            print(f"  - {error}")
        return False
    
    if warnings:
        print("[WARN] VALIDATION PASSED WITH WARNINGS")
        print()
        print("Warnings:")
        for warning in warnings:
            print(f"  - {warning}")
        return True
    
    print("[OK] VALIDATION PASSED")
    print()
    print("The ZIP file is properly formatted for WordPress plugin distribution.")
    return True


def main():
    """Main entry point"""
    if len(sys.argv) < 2:
        print("Usage: python validate-zip.py <path-to-zip>")
        print("Example: python validate-zip.py ../cart-quote-woocommerce-email-v1.0.36.zip")
        sys.exit(1)
    
    zip_path = Path(sys.argv[1])
    
    if validate_zip(zip_path):
        sys.exit(0)
    else:
        sys.exit(1)


if __name__ == '__main__':
    main()
