#!/usr/bin/env python3
"""
Cart Quote WooCommerce & Email - ZIP Builder
Creates WordPress plugin distribution ZIP with configuration-based builds
Run from .build/ directory
"""

import os
import sys
import json
import zipfile
import argparse
from pathlib import Path
from copy import deepcopy

def deep_merge(base, override):
    """Deep merge two dictionaries"""
    result = deepcopy(base)
    
    for key, value in override.items():
        if key in result and isinstance(result[key], dict) and isinstance(value, dict):
            result[key] = deep_merge(result[key], value)
        else:
            result[key] = deepcopy(value)
    
    return result

def load_config(args):
    """Load configuration with priority"""
    build_dir = Path.cwd()
    
    configs = {}
    
    # Load default template
    default_config_path = build_dir / "build-config.json"
    if default_config_path.exists():
        with open(default_config_path, 'r') as f:
            configs = json.load(f)
    
    # Load environment config if specified
    if args.env:
        env_config_path = build_dir / f"build-config.{args.env}.json"
        if env_config_path.exists():
            with open(env_config_path, 'r') as f:
                env_config = json.load(f)
                configs = deep_merge(configs, env_config)
    
    # Load local override (highest priority)
    local_config_path = build_dir / "build-config.local.json"
    if local_config_path.exists():
        with open(local_config_path, 'r') as f:
            local_config = json.load(f)
            configs = deep_merge(configs, local_config)
    
    # Override with command-line config if specified
    if args.config:
        custom_config_path = build_dir / args.config
        if custom_config_path.exists():
            with open(custom_config_path, 'r') as f:
                custom_config = json.load(f)
                configs = deep_merge(configs, custom_config)
    
    # Override version from CLI
    if args.version:
        configs['version'] = args.version
    
    return configs

def validate_required_files(config, plugin_dir, plugin_name):
    """Validate that all required files exist"""
    required_files = config.get('required_files', [])
    missing_files = []
    
    for file_name in required_files:
        # File paths in config are relative to build directory (../)
        # We need to resolve them relative to build_dir and then check if they exist
        # Convert "../file.txt" to absolute path relative to plugin_dir
        if file_name.startswith("../"):
            # Remove ../ prefix and append to plugin_dir
            relative_path = file_name.replace("../", "")
            file_path = plugin_dir / relative_path
        else:
            file_path = plugin_dir / file_name
        
        if not file_path.exists():
            missing_files.append(file_name)
    
    return missing_files

def create_zip(config):
    """Create the plugin ZIP file"""
    build_dir = Path.cwd()
    plugin_dir = build_dir.parent
    
    plugin_name = config.get('plugin_name', 'plugin')
    version = config.get('version', '1.0.0')
    environment = config.get('environment', 'production')
    
    # Get output directory from config or default to 'output'
    output_dir_name = config.get('output_dir', 'output')
    output_dir = build_dir / output_dir_name
    
    # Create output directory if it doesn't exist
    if not output_dir.exists():
        output_dir.mkdir(parents=True)
    
    if environment == 'development':
        zip_filename = output_dir / f"{plugin_name}-dev-v{version}.zip"
    else:
        zip_filename = output_dir / f"{plugin_name}-v{version}.zip"
    
    exclude_patterns = config.get('exclude_patterns', [])
    include_dirs = config.get('include_dirs', [])
    include_files = config.get('include_files', [])
    
    print(f"Creating {zip_filename.name}...")
    print("=" * 50)
    print(f"Environment: {environment}")
    print(f"Version: {version}")
    print(f"Output:     {zip_filename}")
    print("=" * 50)
    
    with zipfile.ZipFile(zip_filename, 'w', zipfile.ZIP_DEFLATED) as zipf:
        files_added = 0
        
        for dir_name in include_dirs:
            # Paths in config are relative to build directory (../)
            # Resolve the path to get the actual directory location
            dir_path = (build_dir / dir_name).resolve()
            if dir_path.is_dir():
                # Get the relative path from plugin directory for ZIP structure
                try:
                    relative_dir = dir_path.relative_to(plugin_dir)
                    print(f"Adding directory: {dir_name}")
                    for root, dirs, files in os.walk(dir_path):
                        for file in files:
                            file_path = Path(root) / file
                            relative_path = file_path.relative_to(plugin_dir)
                            if not any(pattern in str(relative_path) for pattern in exclude_patterns):
                                # Normalize path separators (Windows backslashes → Unix forward slashes)
                                arcname = f"{plugin_name}/{relative_path}".replace('\\', '/')
                                zipf.write(file_path, arcname)
                                files_added += 1
                except ValueError:
                    # Directory is not under plugin_dir, skip it
                    print(f"WARNING: Directory {dir_name} is not under plugin directory, skipping")
        
        for file_name in include_files:
            # Paths in config are relative to build directory (../)
            file_path = (build_dir / file_name).resolve()
            if file_path.is_file():
                # Get the relative path from plugin directory for ZIP structure
                try:
                    relative_path = file_path.relative_to(plugin_dir)
                    if not any(pattern in str(relative_path) for pattern in exclude_patterns):
                        print(f"Adding file: {file_name}")
                        # Normalize path separators (Windows backslashes → Unix forward slashes)
                        arcname = f"{plugin_name}/{relative_path}".replace('\\', '/')
                        zipf.write(file_path, arcname)
                        files_added += 1
                except ValueError:
                    # File is not under plugin_dir, skip it
                    print(f"WARNING: File {file_name} is not under plugin directory, skipping")
        
        print("=" * 50)
        print(f"Files added: {files_added}")
    
    file_size = os.path.getsize(zip_filename)
    size_kb = file_size / 1024
    size_mb = file_size / (1024 * 1024)
    
    print("=" * 50)
    print(f"Build Complete!")
    print("=" * 50)
    print(f"File:     {zip_filename.name}")
    print(f"Location: {zip_filename.parent}")
    print(f"Size:     {size_kb:.2f} KB ({size_mb:.2f} MB)")
    print("=" * 50)
    
    return zip_filename

def validate_zip(zip_filename, plugin_name, config):
    """Validate the created ZIP file"""
    print("Validating ZIP structure...")

    issues = []

    with zipfile.ZipFile(zip_filename, 'r') as zipf:
        plugin_files = [name for name in zipf.namelist() if name.startswith(plugin_name + '/')]
        print(f"Files in ZIP: {len(plugin_files)}")

        # Check for backslashes (Windows path issue - CRITICAL)
        backslash_files = [name for name in zipf.namelist() if '\\' in name]
        if backslash_files:
            issues.append("[ERROR] BACKSLASH FOUND in paths (will fail on Linux)")
            for name in backslash_files[:3]:
                issues.append(f"  - {name}")

        # Check required files
        required_files = config.get('required_files', [])
        missing_in_zip = []

        for required in required_files:
            # Remove ../ prefix if present
            clean_required = required.replace("../", "")
            required_path = f"{plugin_name}/{clean_required}"
            if required_path not in plugin_files:
                missing_in_zip.append(required)

        if missing_in_zip:
            issues.append("[ERROR] MISSING REQUIRED FILES:")
            for file in missing_in_zip:
                issues.append(f"  - {file}")

        # Check critical directories (by checking for files in those directories)
        critical_dirs = [
            f"{plugin_name}/src/Core/",
            f"{plugin_name}/src/Admin/",
            f"{plugin_name}/src/Frontend/",
            f"{plugin_name}/templates/",
        ]

        # Check if at least one file exists in each critical directory
        for critical_dir in critical_dirs:
            dir_prefix = critical_dir.rstrip('/')
            has_file = any(name.startswith(dir_prefix + '/') for name in zipf.namelist())
            if not has_file:
                issues.append(f"[ERROR] MISSING DIRECTORY OR FILES IN: {critical_dir}")

        # Check for empty critical files
        critical_file_patterns = [
            f"{plugin_name}/cart-quote-woocommerce-email.php",
            f"{plugin_name}/src/Core/Activator.php",
            f"{plugin_name}/src/Core/Deactivator.php",
            f"{plugin_name}/src/Core/Plugin.php",
        ]

        for file_pattern in critical_file_patterns:
            try:
                info = zipf.getinfo(file_pattern)
                if info.file_size == 0:
                    issues.append(f"[WARNING] EMPTY CRITICAL FILE: {file_pattern}")
            except KeyError:
                pass

        # Check for excluded patterns
        excluded_in_zip = False
        exclude_patterns = config.get('exclude_patterns', [])

        for exclude in exclude_patterns:
            for name in zipf.namelist():
                if exclude in name:
                    excluded_in_zip = True
                    issues.append(f"[WARNING] EXCLUDED PATTERN FOUND IN ZIP: {exclude}")
                    break

        if issues:
            print("VALIDATION ISSUES FOUND:")
            for issue in issues:
                print(f"  {issue}")
            print("=" * 50)
            print("[FAILED] VALIDATION FAILED")
            return False

        if not excluded_in_zip:
            print("All excluded patterns respected")

    print("[PASS] All validation checks passed")
    print("[PASS] No backslashes in paths")
    print("[PASS] All required files present")
    print("[PASS] Critical directories exist")
    print("=" * 50)
    return True


def generate_changelog_entry(version, changelog, change_type):
    """Generate changelog entry for README.md"""
    from datetime import datetime

    release_date = datetime.now().strftime('%Y-%m-%d')

    icons = {
        'fix': '🛠️',
        'feature': '✨',
        'enhancement': '🔧',
        'performance': '🚀',
        'documentation': '📝',
        'security': '🔒',
    }

    icon = icons.get(change_type, '📦')

    return f"- [v{version}](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v{version}) | {release_date} | {icon} **{change_type}**: {changelog} |"


def update_readme(version, changelog, change_type, plugin_dir):
    """Update README.md Releases section"""
    readme_path = plugin_dir / 'README.md'

    if not readme_path.exists():
        print("[WARNING] README.md not found, skipping update")
        return

    # Read current content
    with open(readme_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Generate changelog entry
    changelog_entry = generate_changelog_entry(version, changelog, change_type)

    # Find Releases table marker
    releases_marker = "| Version | Date | Changes |"
    table_end_marker = "---"

    lines = content.split('\n')
    new_lines = []
    inserted = False

    for i, line in enumerate(lines):
        new_lines.append(line)

        # Insert after table header
        if not inserted and releases_marker in line:
            # Find table end marker and insert before it
            for j in range(i + 1, min(i + 10, len(lines))):
                if table_end_marker in lines[j]:
                    new_lines.insert(j, changelog_entry)
                    inserted = True
                    print(f"[PASS] README.md updated (added v{version} to Releases table)")
                    break

    if not inserted:
        print("[WARNING] Could not find Releases table in README.md")

    # Write updated content
    with open(readme_path, 'w', encoding='utf-8') as f:
        f.write('\n'.join(new_lines))


def update_wiki_updatelog(version, changelog, change_type):
    """Update Wiki Update-Log.md (generates placeholder)"""
    wiki_path = Path.cwd().parent.parent / 'cart-quote-woocommerce-email-wiki'

    if not wiki_path.exists():
        print("[WARNING] Wiki directory not found, skipping wiki update")
        print("To update wiki manually, run:")
        print(f"  python .build/update-wiki.py --version {version} --changelog \"{changelog}\" --type {change_type}")
        return

    updatelog_path = wiki_path / 'Update-Log.md'

    if not updatelog_path.exists():
        print(f"[WARNING] Update-Log.md not found at: {updatelog_path}")
        return

    # Read current content
    with open(updatelog_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Generate changelog entry
    changelog_entry = generate_changelog_entry(version, changelog, change_type)

    # Find Release History table
    wiki_table_start_marker = "| Version | Date | Changes |"
    wiki_table_end_marker = "---"

    lines = content.split('\n')
    new_lines = []
    inserted_table = False

    for i, line in enumerate(lines):
        new_lines.append(line)

        # Insert after Release History header (before first table row)
        if not inserted_table and wiki_table_start_marker in line:
            # Find the table end marker and insert before it
            for j in range(i + 1, min(i + 10, len(lines))):
                if wiki_table_end_marker in lines[j]:
                    new_lines.insert(j, changelog_entry)
                    inserted_table = True
                    print(f"[PASS] Update-Log.md updated (added v{version} to Release History)")
                    break

    # Write updated content
    with open(updatelog_path, 'w', encoding='utf-8') as f:
        f.write('\n'.join(new_lines))


def update_documentations(version, changelog, change_type, plugin_dir):
    """Update README.md and Wiki Update-Log.md"""
    print("")
    print("=" * 50)
    print("Updating Documentation...")
    print("=" * 50)

    # Update README.md
    update_readme(version, changelog, change_type, plugin_dir)

    # Update Wiki Update-Log.md
    update_wiki_updatelog(version, changelog, change_type)

    print("")
    print("=" * 50)
    print("Wiki Push Instructions:")
    print("=" * 50)
    print(f"To push wiki updates, run:")
    print(f"  cd {Path.cwd().parent.parent / 'cart-quote-woocommerce-email-wiki'}")
    print(f"  git add Update-Log.md")
    print(f'  git commit -m "Add v{version} to Update Log"')
    print(f"  git push origin master")
    print("")
    print("Or use single command:")
    print(f"  git -C {Path.cwd().parent.parent / 'cart-quote-woocommerce-email-wiki'} add Update-Log.md && \\")
    print(f"       git -C {Path.cwd().parent.parent / 'cart-quote-woocommerce-email-wiki'} commit -m 'Add v{version} to Update Log' && \\")
    print(f"       git -C {Path.cwd().parent.parent / 'cart-quote-woocommerce-email-wiki'} push origin master")
    print("=" * 50)


def main():
    parser = argparse.ArgumentParser(description='Build WordPress plugin ZIP')
    parser.add_argument('version', nargs='?', help='Plugin version')
    parser.add_argument('--config', help='Path to custom config file')
    parser.add_argument('--env', choices=['dev', 'prod'], help='Environment (dev or prod)')
    parser.add_argument('--changelog', help='Release changelog message for documentation')
    parser.add_argument('--type', choices=['fix', 'feature', 'enhancement', 'performance', 'documentation', 'security'],
                       help='Type of change for changelog formatting')
    parser.add_argument('--no-docs', action='store_true',
                       help='Skip documentation updates (README.md and Wiki)')
    
    args = parser.parse_args()
    
    print("=" * 50)
    print("Plugin ZIP Builder")
    print("=" * 50)
    
    build_dir = Path.cwd()
    
    # Verify we're running from .build directory
    if not (build_dir / "build-config.json").exists():
        print("ERROR: build-config.json not found in current directory")
        print("Please run this script from the .build/ directory")
        sys.exit(1)
    
    config = load_config(args)
    
    plugin_dir = build_dir.parent
    plugin_name = config.get('plugin_name', 'plugin')
    
    missing_files = validate_required_files(config, plugin_dir, plugin_name)
    
    if missing_files:
        print("ERROR: Required files missing:")
        for file in missing_files:
            print(f"  - {file}")
        if config.get('fail_on_missing', True):
            sys.exit(1)
    
    zip_filename = create_zip(config)
    
    if not validate_zip(zip_filename, plugin_name, config):
        sys.exit(1)
    
    print("SUCCESS: Plugin built successfully!")
    
    # Update documentation if changelog provided and not --no-docs
    if args.changelog and not args.no_docs:
        change_type = args.type if args.type else 'fix'
        update_documentations(
            version=config.get('version', '1.0.0'),
            changelog=args.changelog,
            change_type=change_type,
            plugin_dir=plugin_dir
        )

# Module entry point (moved outside main() function to fix infinite recursion)
if __name__ == '__main__':
    main()

