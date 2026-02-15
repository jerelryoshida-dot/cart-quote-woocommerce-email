#!/usr/bin/env python3
"""
Validate plugin ZIP before deployment
Ensures all required files exist with correct structure
"""

import zipfile
import sys
from pathlib import Path

def validate_zip(zip_path):
    """Validate ZIP file structure and contents"""
    required_files = [
        'cart-quote-woocommerce-email/cart-quote-woocommerce-email.php',
        'cart-quote-woocommerce-email/src/Core/Activator.php',
        'cart-quote-woocommerce-email/src/Core/Deactivator.php',
        'cart-quote-woocommerce-email/src/Core/Plugin.php',
        'cart-quote-woocommerce-email/readme.txt',
    ]

    critical_directories = [
        'cart-quote-woocommerce-email/src/Core/',
        'cart-quote-woocommerce-email/src/Admin/',
        'cart-quote-woocommerce-email/src/Frontend/',
        'cart-quote-woocommerce-email/templates/',
    ]

    issues = []

    try:
        with zipfile.ZipFile(zip_path, 'r') as zipf:
            # Check for backslashes (Windows path issue)
            for name in zipf.namelist():
                if '\\' in name:
                    issues.append(f"[ERROR] BACKSLASH FOUND in: {name}")
                    break

            # Check required files
            for required in required_files:
                if required not in zipf.namelist():
                    issues.append(f"[ERROR] MISSING: {required}")

            # Check critical directories exist (check for at least one file in each)
            for directory in critical_directories:
                # Remove trailing slash for matching
                dir_prefix = directory.rstrip('/')
                has_file = any(name.startswith(dir_prefix + '/') for name in zipf.namelist())
                if not has_file:
                    issues.append(f"[ERROR] MISSING DIRECTORY OR FILES IN: {directory}")

            # Check for empty files
            for info in zipf.infolist():
                if info.file_size == 0 and not info.filename.endswith('/'):
                    issues.append(f"[WARNING] EMPTY FILE: {info.filename}")

    except zipfile.BadZipFile:
        issues.append("[ERROR] CORRUPT ZIP FILE")
        return False, issues
    except Exception as e:
        issues.append(f"[ERROR] {str(e)}")
        return False, issues

    return len(issues) == 0, issues

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print("Usage: python validate-zip.py <zip-file>")
        sys.exit(1)

    zip_path = Path(sys.argv[1])

    if not zip_path.exists():
        print(f"[ERROR] ZIP file not found: {zip_path}")
        sys.exit(1)

    print(f"Validating: {zip_path.name}")
    print("=" * 50)

    is_valid, issues = validate_zip(zip_path)

    if issues:
        print("ISSUES FOUND:")
        for issue in issues:
            print(f"  {issue}")
        print("=" * 50)
        print("[FAILED] VALIDATION FAILED")
        sys.exit(1)
    else:
        print("[PASS] All checks passed")
        print("[PASS] ZIP structure is valid")
        print("[PASS] All required files present")
        print("[PASS] No backslashes in paths")
        print("=" * 50)
        print("[SUCCESS] VALIDATION PASSED")
        sys.exit(0)
