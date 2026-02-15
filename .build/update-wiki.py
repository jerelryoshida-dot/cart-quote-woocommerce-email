#!/usr/bin/env python3
"""
Wiki Update Helper

Standalone script to update GitHub wiki without running full build.

Usage:
    python update-wiki.py --version "1.0.13" \
        --changelog "Critical fix" \
        --type "fix" \
        --details version-details.md
"""

import subprocess
import sys
import argparse
from pathlib import Path


def clone_wiki(wiki_path):
    """Clone wiki repository if not exists"""
    if wiki_path.exists():
        print(f"✓ Wiki already cloned at: {wiki_path}")
        return True

    print(f"Cloning wiki to: {wiki_path}")
    result = subprocess.run(
        ['gh', 'repo', 'clone', 'jerelryoshida-dot/cart-quote-woocommerce-email.wiki', str(wiki_path)],
        capture_output=True,
        text=True
    )

    if result.returncode == 0:
        print("✓ Wiki cloned successfully")
        return True
    else:
        print(f"✗ Failed to clone wiki: {result.stderr}")
        return False


def update_updatelog(wiki_path, version, changelog, change_type, details_file=None):
    """Update Update-Log.md file"""
    updatelog_path = wiki_path / 'Update-Log.md'

    if not updatelog_path.exists():
        print(f"✗ Update-Log.md not found at: {updatelog_path}")
        return False

    # Read current content
    with open(updatelog_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find Release History table
    release_history_marker = "## Release History"
    table_start_marker = "| Version | Date | Changes |"
    table_end_marker = "---"

    # Generate changelog entry (inline function to avoid import)
    def generate_changelog_entry(version, changelog, change_type):
        """Generate changelog entry for Release History table"""
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

        entry = f"| [v{version}](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v{version}) | {release_date} | {icon} **{change_type}**: {changelog} |"

        return entry

    changelog_entry = generate_changelog_entry(version, changelog, change_type)

    # Generate version details section
    if details_file and Path(details_file).exists():
        with open(details_file, 'r', encoding='utf-8') as f:
            version_details = f.read()
    else:
        version_details = f"""### v{version} - {changelog}

**Overview:**
[Release overview]

**Changes:**
- Change 1
- Change 2

**Fixes:**
- Fix 1
- Fix 2

**Technical Details:**
[Technical details]
"""

    # Insert changelog entry into Release History table
    lines = content.split('\n')
    new_lines = []
    inserted_table = False
    inserted_details = False

    for i, line in enumerate(lines):
        new_lines.append(line)

        # Insert after Release History header (before first table row)
        if not inserted_table and table_start_marker in line:
            # Find the table end marker and insert before it
            for j in range(i + 1, min(i + 10, len(lines))):
                if table_end_marker in lines[j]:
                    # Insert at this position
                    new_lines.insert(j, changelog_entry)
                    inserted_table = True
                    break

        # Insert version details section
        if not inserted_details and line.startswith("### v1.0."):
            # Insert new version details before existing versions
            # Find where version details section starts
            if "## Version Details" in content:
                # Insert after Version Details header
                new_lines.append("")
                new_lines.append(version_details)
                inserted_details = True
                break

    # Write updated content
    with open(updatelog_path, 'w', encoding='utf-8') as f:
        f.write('\n'.join(new_lines))

    print("✓ Update-Log.md updated")
    return True


def commit_and_show_instructions(wiki_path, version):
    """Commit changes and show push instructions"""
    print("\n" + "=" * 50)
    print("NEXT STEPS:")
    print("=" * 50)
    print("\nTo push wiki updates, run:")
    print(f"  cd {wiki_path}")
    print("  git add Update-Log.md")
    print(f'  git commit -m "Add v{version} to Update Log"')
    print("  git push origin master")
    print("\n" + "=" * 50)


def main():
    parser = argparse.ArgumentParser(description='Update GitHub wiki with release information')
    parser.add_argument('--version', required=True, help='Version number (e.g., 1.0.13)')
    parser.add_argument('--changelog', required=True, help='Brief changelog message')
    parser.add_argument('--type', required=True,
                       choices=['fix', 'feature', 'enhancement', 'performance', 'documentation', 'security'],
                       help='Type of change')
    parser.add_argument('--details', help='Path to version details markdown file')
    parser.add_argument('--wiki-path', default='D:/Projects/cart-quote-woocommerce-email-wiki',
                       help='Path to wiki repository')
    parser.add_argument('--no-clone', action='store_true',
                       help='Skip cloning if wiki already exists')

    args = parser.parse_args()

    wiki_path = Path(args.wiki_path)

    # Clone wiki if needed
    if not args.no_clone:
        if not clone_wiki(wiki_path):
            sys.exit(1)

    # Update Update-Log
    if not update_updatelog(wiki_path, args.version, args.changelog, args.type, args.details):
        sys.exit(1)

    # Show push instructions
    commit_and_show_instructions(wiki_path, args.version)


if __name__ == '__main__':
    main()
