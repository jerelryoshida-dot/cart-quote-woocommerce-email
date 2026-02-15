#!/usr/bin/env python3
"""
Release Notes Generator

Generates changelog and version details markdown for releases.
Used by build-zip.py to automatically update documentation.

Usage:
    python create-release-notes.py --version "1.0.13" \
        --changelog "Critical fix: deployment validation system" \
        --type "fix"
"""

import sys
import argparse
from pathlib import Path
from datetime import datetime


def generate_changelog_entry(version, changelog, change_type):
    """Generate changelog entry for Release History table"""

    # Get today's date
    release_date = datetime.now().strftime('%Y-%m-%d')

    # Determine icon based on change type
    icons = {
        'fix': '🛠️',
        'feature': '✨',
        'enhancement': '🔧',
        'performance': '🚀',
        'documentation': '📝',
        'security': '🔒',
    }

    icon = icons.get(change_type, '📦')

    # Format changelog entry
    entry = f"| [v{version}](https://github.com/jerelryoshida-dot/cart-quote-woocommerce-email/releases/tag/v{version}) | {release_date} | {icon} **{change_type}**: {changelog} |"

    return entry


def generate_version_details_placeholder():
    """Generate placeholder for version details section"""

    placeholder = """### v{{version}} ({{date}}) - {{title}}

**Overview:**
[Provide brief overview of this release]

**Changes:**

[List changes here]

**Technical Details:**

[Technical specifications and implementation details]

"""

    return placeholder


def main():
    parser = argparse.ArgumentParser(description='Generate release notes and documentation')
    parser.add_argument('--version', required=True, help='Version number (e.g., 1.0.13)')
    parser.add_argument('--changelog', required=True, help='Brief changelog message')
    parser.add_argument('--type', required=True,
                       choices=['fix', 'feature', 'enhancement', 'performance', 'documentation', 'security'],
                       help='Type of change')
    parser.add_argument('--title', help='Release title (defaults to changelog message)')
    parser.add_argument('--overview', help='Release overview/description')
    parser.add_argument('--output-dir', default='.', help='Output directory for generated files')

    args = parser.parse_args()

    # Set defaults
    if not args.title:
        # Extract first sentence or use changelog as title
        args.title = args.changelog.split('.')[0] if '.' in args.changelog else args.changelog

    # Determine output directory
    output_dir = Path(args.output_dir)

    # Generate changelog entry
    changelog_entry = generate_changelog_entry(args.version, args.changelog, args.type)
    changelog_file = output_dir / 'changelog-entry.md'

    with open(changelog_file, 'w', encoding='utf-8') as f:
        f.write(changelog_entry)

    # Generate version details placeholder
    version_details = generate_version_details_placeholder()
    details_file = output_dir / 'version-details.md'

    with open(details_file, 'w', encoding='utf-8') as f:
        f.write(version_details)

    # Print summary
    print("=" * 50)
    print("RELEASE NOTES GENERATED")
    print("=" * 50)
    print(f"Version:    {args.version}")
    print(f"Type:       {args.type}")
    print(f"Changelog:   {args.changelog}")
    print(f"Title:      {args.title}")
    print("=" * 50)
    print(f"Output files:")
    print(f"  - {changelog_file.relative_to(Path.cwd())}")
    print(f"  - {details_file.relative_to(Path.cwd())}")
    print("=" * 50)


if __name__ == '__main__':
    main()
