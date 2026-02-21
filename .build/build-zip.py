#!/usr/bin/env python3
"""
Build ZIP script for Cart Quote WooCommerce & Email plugin

Creates a properly formatted ZIP file for WordPress plugin distribution.
Ensures all paths use forward slashes for cross-platform compatibility.

Usage:
    python build-zip.py <version>
    
Example:
    python build-zip.py 1.0.36

Output:
    Creates cart-quote-woocommerce-email-v<version>.zip in parent directory
"""

import zipfile
import os
import sys
from pathlib import Path


def get_plugin_dir():
    """Get the plugin root directory (parent of .build)"""
    return Path(__file__).parent.parent.resolve()


def get_output_dir():
    """Get the output directory (parent of plugin dir)"""
    return get_plugin_dir().parent


def get_include_patterns():
    """Define files and directories to include in the ZIP"""
    return [
        'cart-quote-woocommerce-email.php',
        'README.md',
        'readme.txt',
        'uninstall.php',
        'src/',
        'assets/',
        'templates/',
    ]


def should_exclude(path):
    """Check if a path should be excluded from the ZIP"""
    exclude_patterns = [
        '.git',
        '.github',
        '.build',
        '.gitignore',
        '.DS_Store',
        'Thumbs.db',
        '*.md.bak',
        '*.tmp',
        '*.temp',
        '.vscode',
        '.idea',
        '__pycache__',
        '*.pyc',
        'tests/',
        'vendor/',
        'node_modules/',
    ]
    
    path_str = str(path).replace('\\', '/')
    path_lower = path_str.lower()
    
    for pattern in exclude_patterns:
        if pattern.startswith('*'):
            if path_lower.endswith(pattern[1:]):
                return True
        elif pattern in path_str:
            return True
    
    return False


def collect_files(plugin_dir, patterns):
    """Collect all files to include in the ZIP"""
    files = []
    
    for pattern in patterns:
        full_path = plugin_dir / pattern
        
        if full_path.is_file():
            if not should_exclude(full_path):
                files.append(full_path)
        elif full_path.is_dir():
            for root, dirs, filenames in os.walk(full_path):
                root_path = Path(root)
                
                # Filter out excluded directories
                dirs[:] = [d for d in dirs if not should_exclude(root_path / d)]
                
                for filename in filenames:
                    file_path = root_path / filename
                    if not should_exclude(file_path):
                        files.append(file_path)
    
    return files


def create_zip(version, dry_run=False):
    """Create the ZIP file with proper forward slashes"""
    plugin_dir = get_plugin_dir()
    output_dir = get_output_dir()
    output_file = output_dir / f"cart-quote-woocommerce-email-v{version}.zip"
    
    print("=" * 60)
    print("  Cart Quote WooCommerce - Build ZIP")
    print("=" * 60)
    print(f"\nPlugin Directory: {plugin_dir}")
    print(f"Output File: {output_file}")
    print(f"Version: {version}")
    print()
    
    # Collect files
    patterns = get_include_patterns()
    files = collect_files(plugin_dir, patterns)
    
    print(f"Files to include: {len(files)}")
    print()
    
    if dry_run:
        print("DRY RUN - Files that would be added:")
        for f in sorted(files):
            rel_path = f.relative_to(plugin_dir)
            arcname = f"cart-quote-woocommerce-email/{rel_path}".replace('\\', '/')
            print(f"  {arcname}")
        return None
    
    # Remove existing ZIP if present
    if output_file.exists():
        output_file.unlink()
        print(f"Removed existing ZIP: {output_file}")
    
    # Create ZIP
    added_count = 0
    with zipfile.ZipFile(output_file, 'w', zipfile.ZIP_DEFLATED) as zipf:
        for file_path in sorted(files):
            rel_path = file_path.relative_to(plugin_dir)
            # CRITICAL: Use forward slashes for cross-platform compatibility
            arcname = f"cart-quote-woocommerce-email/{rel_path}".replace('\\', '/')
            
            zipf.write(file_path, arcname)
            added_count += 1
            
            # Print first few and last few files
            if added_count <= 5 or added_count > len(files) - 5:
                print(f"  Added: {arcname}")
            elif added_count == 6:
                print(f"  ... ({len(files) - 10} more files) ...")
    
    print()
    print(f"[OK] Created ZIP with {added_count} files")
    print(f"[OUTPUT] {output_file}")
    print(f"[SIZE] {output_file.stat().st_size / 1024:.1f} KB")
    
    return output_file


def verify_zip(zip_path):
    """Verify the ZIP has correct structure and no backslashes"""
    print("\n" + "=" * 60)
    print("  Verifying ZIP Structure")
    print("=" * 60)
    
    errors = []
    warnings = []
    
    with zipfile.ZipFile(zip_path, 'r') as zipf:
        entries = zipf.namelist()
        
        # Check for backslashes
        for entry in entries:
            if '\\' in entry:
                errors.append(f"Backslash found in path: {entry}")
        
        # Check for required files
        required_files = [
            'cart-quote-woocommerce-email/cart-quote-woocommerce-email.php',
            'cart-quote-woocommerce-email/readme.txt',
            'cart-quote-woocommerce-email/uninstall.php',
        ]
        
        for req_file in required_files:
            if req_file not in entries:
                errors.append(f"Missing required file: {req_file}")
        
        # Check folder structure
        if not entries[0].startswith('cart-quote-woocommerce-email/'):
            errors.append("ZIP must contain cart-quote-woocommerce-email/ folder at root")
        
        # Count by type
        php_count = sum(1 for e in entries if e.endswith('.php'))
        css_count = sum(1 for e in entries if e.endswith('.css'))
        js_count = sum(1 for e in entries if e.endswith('.js'))
        
        print(f"\nFiles by type:")
        print(f"  PHP: {php_count}")
        print(f"  CSS: {css_count}")
        print(f"  JS: {js_count}")
        print(f"  Total: {len(entries)}")
    
    print()
    
    if errors:
        print("[FAILED] VERIFICATION FAILED:")
        for error in errors:
            print(f"  ERROR: {error}")
        return False
    
    if warnings:
        print("[WARN] Warnings:")
        for warning in warnings:
            print(f"  WARNING: {warning}")
    
    print("[OK] ZIP verification passed!")
    return True


def main():
    """Main entry point"""
    if len(sys.argv) < 2:
        print("Usage: python build-zip.py <version>")
        print("Example: python build-zip.py 1.0.36")
        print("\nOptions:")
        print("  --dry-run    Show files that would be added without creating ZIP")
        sys.exit(1)
    
    version = sys.argv[1]
    
    # Validate version format
    if not version.count('.') >= 1:
        print(f"ERROR: Invalid version format: {version}")
        print("Expected format: X.X.X (e.g., 1.0.36)")
        sys.exit(1)
    
    dry_run = '--dry-run' in sys.argv
    
    # Create ZIP
    zip_path = create_zip(version, dry_run=dry_run)
    
    if zip_path is None:
        # Dry run, exit
        sys.exit(0)
    
    # Verify ZIP
    if not verify_zip(zip_path):
        print("\n[FAILED] ZIP verification failed. Removing invalid ZIP.")
        zip_path.unlink()
        sys.exit(1)
    
    print("\n" + "=" * 60)
    print("  [SUCCESS] Build Complete!")
    print("=" * 60)


if __name__ == '__main__':
    main()
