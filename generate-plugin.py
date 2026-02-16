#!/usr/bin/env python3
"""
WordPress Plugin Generator

Generates a new WordPress plugin from the AI_Builder_Template.

Usage:
    python generate-plugin.py --interactive          # Full interactive mode
    python generate-plugin.py --config my.json       # From config file
    python generate-plugin.py --minimal              # Minimal structure

Author: YOUR_NAME
Version: 1.0.0
"""

import os
import sys
import json
import shutil
import argparse
import re
from pathlib import Path
from datetime import datetime

# ANSI colors
class Colors:
    HEADER = '\033[95m'
    BLUE = '\033[94m'
    CYAN = '\033[96m'
    GREEN = '\033[92m'
    YELLOW = '\033[93m'
    RED = '\033[91m'
    ENDC = '\033[0m'
    BOLD = '\033[1m'


def print_header(text):
    print(f"\n{Colors.BOLD}{Colors.CYAN}{text}{Colors.ENDC}")
    print("=" * len(text))


def print_success(text):
    print(f"  {Colors.GREEN}✅ {text}{Colors.ENDC}")


def print_info(text):
    print(f"  {Colors.BLUE}ℹ️  {text}{Colors.ENDC}")


def print_warning(text):
    print(f"  {Colors.YELLOW}⚠️  {text}{Colors.ENDC}")


def prompt_input(label, default=None, required=True):
    """Prompt for user input with optional default"""
    prompt_text = f"  {Colors.BOLD}{label}{Colors.ENDC}"
    if default:
        prompt_text += f" [{default}]"
    prompt_text += ": "
    
    value = input(prompt_text).strip()
    
    if not value and default:
        return default
    if not value and required:
        print_warning("This field is required!")
        return prompt_input(label, default, required)
    
    return value


def prompt_yes_no(label, default=False):
    """Prompt for yes/no input"""
    default_str = "Y/n" if default else "y/N"
    prompt_text = f"  {Colors.BOLD}{label}{Colors.ENDC} [{default_str}]: "
    
    value = input(prompt_text).strip().lower()
    
    if not value:
        return default
    return value in ['y', 'yes', 'true', '1']


def slugify(text):
    """Convert text to URL-safe slug"""
    text = text.lower()
    text = re.sub(r'[^a-z0-9\s-]', '', text)
    text = re.sub(r'[\s_]+', '-', text)
    text = re.sub(r'-+', '-', text)
    return text.strip('-')


def to_pascal_case(text):
    """Convert text to PascalCase"""
    words = re.split(r'[\s-_]+', text)
    return ''.join(word.capitalize() for word in words)


def to_snake_case(text):
    """Convert text to snake_case"""
    text = text.lower()
    text = re.sub(r'[^a-z0-9\s]', '', text)
    text = re.sub(r'\s+', '_', text)
    return text


def to_upper_snake(text):
    """Convert text to UPPER_SNAKE_CASE"""
    return to_snake_case(text).upper()


def collect_plugin_info(interactive=True, config_path=None):
    """Collect plugin information from user or config file"""
    
    if config_path and Path(config_path).exists():
        with open(config_path, 'r', encoding='utf-8') as f:
            return json.load(f)
    
    if not interactive:
        return {
            'name': 'My Plugin',
            'slug': 'my-plugin',
            'namespace': 'MyPlugin',
            'prefix': 'MY_PLUGIN',
            'text_domain': 'my-plugin',
            'author': 'Your Name',
            'author_url': 'https://your-website.com',
            'description': 'A WordPress plugin',
            'include_woocommerce': False,
            'include_elementor': False,
            'include_services': True,
        }
    
    print_header("📋 Plugin Information")
    
    # Basic info
    name = prompt_input("Plugin Name (human readable)", "My Plugin")
    slug = prompt_input("Plugin Slug (folder name)", slugify(name))
    namespace = prompt_input("PHP Namespace (PascalCase)", to_pascal_case(name))
    prefix = prompt_input("Constant Prefix (UPPER_SNAKE)", to_upper_snake(name))
    text_domain = prompt_input("Text Domain", slug)
    
    print()  # Spacer
    
    # Author info
    author = prompt_input("Author Name", "Your Name", required=False)
    author_url = prompt_input("Author URL", "https://your-website.com", required=False)
    description = prompt_input("Description", "A WordPress plugin", required=False)
    
    print()  # Spacer
    
    # Features
    print(f"{Colors.BOLD}Features to include:{Colors.ENDC}")
    include_woocommerce = prompt_yes_no("Include WooCommerce integration?", False)
    include_elementor = prompt_yes_no("Include Elementor widgets?", False)
    include_services = prompt_yes_no("Include advanced services (email, API)?", True)
    
    return {
        'name': name,
        'slug': slug,
        'namespace': namespace,
        'prefix': prefix,
        'text_domain': text_domain,
        'author': author or 'Your Name',
        'author_url': author_url or 'https://your-website.com',
        'description': description or 'A WordPress plugin',
        'include_woocommerce': include_woocommerce,
        'include_elementor': include_elementor,
        'include_services': include_services,
    }


def process_file_content(content, info):
    """Replace placeholders in file content"""
    
    replacements = {
        'PLUGIN_NAME': info['name'],
        'PLUGIN_SLUG': info['slug'],
        'PLUGIN_NAMESPACE': info['namespace'],
        'PLUGIN_PREFIX': info['prefix'],
        'TEXT_DOMAIN': info['text_domain'],
        'YOUR_NAME': info['author'],
        'YOUR_WEBSITE': info['author_url'],
        'Brief description of what your plugin does.': info['description'],
        'https://your-website.com/plugin': f"{info['author_url']}/{info['slug']}",
        'https://your-website.com': info['author_url'],
    }
    
    for placeholder, value in replacements.items():
        content = content.replace(placeholder, value)
    
    return content


def create_plugin(info, output_dir, template_dir):
    """Create the plugin directory structure"""
    
    plugin_dir = Path(output_dir) / info['slug']
    
    # Check if directory exists
    if plugin_dir.exists():
        print_warning(f"Directory already exists: {plugin_dir}")
        if not prompt_yes_no("Overwrite?", False):
            print_info("Aborted.")
            return None
    
    print_header(f"🚀 Creating Plugin: {info['name']}")
    
    # Create directory structure
    dirs_to_create = [
        plugin_dir,
        plugin_dir / 'src' / 'Core',
        plugin_dir / 'src' / 'Admin',
        plugin_dir / 'src' / 'Database',
        plugin_dir / 'src' / 'Frontend',
        plugin_dir / 'assets' / 'css',
        plugin_dir / 'assets' / 'js',
        plugin_dir / 'templates' / 'admin',
        plugin_dir / 'templates' / 'frontend',
        plugin_dir / 'templates' / 'emails',
    ]
    
    if info['include_services']:
        dirs_to_create.append(plugin_dir / 'src' / 'Services')
    
    if info['include_woocommerce']:
        dirs_to_create.append(plugin_dir / 'src' / 'WooCommerce')
    
    if info['include_elementor']:
        dirs_to_create.append(plugin_dir / 'src' / 'Elementor')
    
    for dir_path in dirs_to_create:
        dir_path.mkdir(parents=True, exist_ok=True)
        print_success(f"Created: {dir_path.relative_to(plugin_dir.parent)}")
    
    # Copy and process template files
    files_to_copy = [
        # Core files
        ('templates/core/main-plugin-file.php', f"{info['slug']}.php"),
        ('templates/core/Plugin.php', 'src/Core/Plugin.php'),
        ('templates/core/Activator.php', 'src/Core/Activator.php'),
        ('templates/core/Deactivator.php', 'src/Core/Deactivator.php'),
        ('templates/core/Uninstaller.php', 'src/Core/Uninstaller.php'),
        ('templates/core/Debug_Logger.php', 'src/Core/Debug_Logger.php'),
        ('templates/core/uninstall.php', 'uninstall.php'),
        
        # Admin files
        ('templates/admin/Admin_Manager.php', 'src/Admin/Admin_Manager.php'),
        ('templates/admin/Settings.php', 'src/Admin/Settings.php'),
        
        # Database files
        ('templates/database/Repository.php', 'src/Database/Repository.php'),
        
        # Frontend files
        ('templates/frontend/Frontend_Manager.php', 'src/Frontend/Frontend_Manager.php'),
    ]
    
    # Add optional files
    if info['include_services']:
        files_to_copy.append(('templates/services/Email_Service.php', 'src/Services/Email_Service.php'))
    
    print()  # Spacer
    print_header("📝 Processing Files")
    
    for src_rel, dest_rel in files_to_copy:
        src_path = template_dir / src_rel
        dest_path = plugin_dir / dest_rel
        
        if src_path.exists():
            # Read template
            with open(src_path, 'r', encoding='utf-8') as f:
                content = f.read()
            
            # Process placeholders
            content = process_file_content(content, info)
            
            # Write to destination
            with open(dest_path, 'w', encoding='utf-8') as f:
                f.write(content)
            
            print_success(f"Created: {dest_rel}")
        else:
            print_warning(f"Template not found: {src_rel}")
    
    # Create CSS files
    css_content = f"""/**
 * {info['name']} - Admin Styles
 *
 * @package {info['namespace']}
 */

.{info['slug']}-container {{
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
}}

.{info['slug']}-header {{
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ccc;
}}

.{info['slug']}-table {{
    width: 100%;
    border-collapse: collapse;
}}

.{info['slug']}-table th,
.{info['slug']}-table td {{
    padding: 10px;
    text-align: left;
    border-bottom: 1px solid #eee;
}}
"""
    
    with open(plugin_dir / 'assets' / 'css' / 'admin.css', 'w', encoding='utf-8') as f:
        f.write(css_content)
    print_success("Created: assets/css/admin.css")
    
    with open(plugin_dir / 'assets' / 'css' / 'frontend.css', 'w', encoding='utf-8') as f:
        f.write(css_content.replace('Admin', 'Frontend'))
    print_success("Created: assets/css/frontend.css")
    
    # Create JS files
    js_admin_content = f"""/**
 * {info['name']} - Admin JavaScript
 *
 * @package {info['namespace']}
 */

(function($) {{
    'use strict';

    // Initialize on DOM ready
    $(document).ready(function() {{
        console.log('{info['name']} Admin loaded');

        // Example: Form submission handler
        $('.{info['slug']}-form').on('submit', function(e) {{
            e.preventDefault();
            
            var $form = $(this);
            var formData = $form.serialize();
            
            $.ajax({{
                url: {info['slug'].replace('-', '_')}Admin.ajaxUrl,
                type: 'POST',
                data: formData,
                success: function(response) {{
                    if (response.success) {{
                        alert(response.data.message);
                    }} else {{
                        alert(response.data.message || 'Error');
                    }}
                }},
                error: function() {{
                    alert('Server error. Please try again.');
                }}
            }});
        }});
    }});

}})(jQuery);
"""
    
    with open(plugin_dir / 'assets' / 'js' / 'admin.js', 'w', encoding='utf-8') as f:
        f.write(js_admin_content)
    print_success("Created: assets/js/admin.js")
    
    js_frontend_content = f"""/**
 * {info['name']} - Frontend JavaScript
 *
 * @package {info['namespace']}
 */

(function($) {{
    'use strict';

    $(document).ready(function() {{
        console.log('{info['name']} Frontend loaded');
    }});

}})(jQuery);
"""
    
    with open(plugin_dir / 'assets' / 'js' / 'frontend.js', 'w', encoding='utf-8') as f:
        f.write(js_frontend_content)
    print_success("Created: assets/js/frontend.js")
    
    # Create readme.txt for WordPress.org
    readme_content = f"""=== {info['name']} ===
Contributors: {info['author'].lower().replace(' ', '')}
Tags: wordpress, plugin
Requires at least: 5.8
Tested up to: 6.4
Stable tag: 1.0.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

{info['description']}

== Description ==

{info['description']}

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/{info['slug']}`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure settings under Settings > {info['name']}

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release
"""
    
    with open(plugin_dir / 'readme.txt', 'w', encoding='utf-8') as f:
        f.write(readme_content)
    print_success("Created: readme.txt")
    
    return plugin_dir


def main():
    parser = argparse.ArgumentParser(description='Generate WordPress plugin from template')
    parser.add_argument('--interactive', '-i', action='store_true', help='Interactive mode')
    parser.add_argument('--config', '-c', help='Path to config JSON file')
    parser.add_argument('--output', '-o', default='.', help='Output directory')
    parser.add_argument('--minimal', '-m', action='store_true', help='Minimal structure only')
    
    args = parser.parse_args()
    
    print_header("🚀 WordPress Plugin Generator")
    
    # Determine template directory
    template_dir = Path(__file__).parent
    
    # Collect plugin info
    info = collect_plugin_info(
        interactive=args.interactive or (not args.config),
        config_path=args.config
    )
    
    # Create plugin
    plugin_dir = create_plugin(info, args.output, template_dir)
    
    if plugin_dir:
        print_header("✅ Plugin Created Successfully!")
        
        print(f"\n{Colors.BOLD}📁 Location:{Colors.ENDC}")
        print(f"   {plugin_dir.absolute()}")
        
        print(f"\n{Colors.BOLD}📝 Next Steps:{Colors.ENDC}")
        print(f"   1. cd {info['slug']}")
        print(f"   2. Open in your IDE")
        print(f"   3. Review and customize the generated files")
        print(f"   4. Create your database tables in Activator.php")
        print(f"   5. Add your plugin's functionality")
        
        print(f"\n{Colors.BOLD}🔧 Development:{Colors.ENDC}")
        print(f"   - Main file: {info['slug']}.php")
        print(f"   - Services: src/Core/Plugin.php")
        print(f"   - Database: src/Database/Repository.php")
        print(f"   - Admin: src/Admin/Admin_Manager.php")
        print(f"   - Frontend: src/Frontend/Frontend_Manager.php")
        
        # Save config for future reference
        config_path = plugin_dir / '.generator-config.json'
        with open(config_path, 'w', encoding='utf-8') as f:
            json.dump(info, f, indent=2)
        
        print(f"\n{Colors.GREEN}Generator config saved to: {config_path.name}{Colors.ENDC}")


if __name__ == '__main__':
    main()
