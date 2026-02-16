# WordPress Plugin Builder Template

A comprehensive, reusable template for building professional WordPress plugins with AI assistance.

## Quick Start

### Option 1: Use the Generator (Recommended)

```bash
cd AI_Builder_Template
python generate-plugin.py --interactive
```

### Option 2: Manual Setup

1. Copy the `templates/` folder to your new plugin location
2. Rename files and folders to match your plugin
3. Find and replace placeholders:
   - `PLUGIN_NAME` - Human-readable name
   - `PLUGIN_SLUG` - Folder name (lowercase, hyphens)
   - `PLUGIN_NAMESPACE` - PHP namespace (PascalCase)
   - `PLUGIN_PREFIX` - Function/constant prefix (uppercase, underscores)
   - `TEXT_DOMAIN` - Translation text domain

## Folder Structure

```
AI_Builder_Template/
├── generate-plugin.py       # Scaffolding generator
├── plugin-config.json       # Generator configuration
├── AI_PLUGIN_BUILDER_GUIDE.md  # Master knowledge base
│
├── templates/               # Reference files with comments
│   ├── core/                # Core PHP classes
│   ├── admin/               # Admin interface
│   ├── database/            # Repository pattern
│   ├── frontend/            # Shortcodes, assets
│   ├── services/            # Email, API, caching
│   ├── elementor/           # Elementor widgets
│   ├── woocommerce/         # WooCommerce integration
│   ├── ajax/                # AJAX handlers
│   ├── templates/           # View templates
│   └── tests/               # PHPUnit templates
│
├── build/                   # Build & deploy scripts
│   ├── build-zip.py
│   ├── deploy.py
│   └── hooks/
│
├── docs/                    # Documentation templates
│   ├── AI_AGENT_CACHE_TEMPLATE.md
│   ├── AGENTS_TEMPLATE.md
│   └── README_TEMPLATE.md
│
└── checklists/              # Development checklists
    ├── pre-development.md
    ├── pre-release.md
    └── security-audit.md
```

## What's Included

### Core Architecture
- **Service Container** - Dependency injection pattern
- **Singleton Pattern** - Single plugin instance
- **PSR-4 Autoloader** - Modern class loading
- **Repository Pattern** - Clean database abstraction

### Admin Features
- Settings API integration
- Custom admin pages
- WP_List_Table implementation
- CSV export with chunking

### Frontend Features
- Shortcode development
- Elementor widgets
- AJAX interactions
- Asset management

### Security
- Nonce verification
- Input sanitization
- Output escaping
- Capability checks
- SQL prepared statements
- AES-256-CBC encryption

### Services
- Email service (HTML emails)
- External API integration (OAuth)
- Debug logging
- Rate limiting
- Caching

### Build & Deploy
- ZIP builder with validation
- Automated deployment
- Git hooks
- Version management
- GitHub release automation

## Documentation

- `AI_PLUGIN_BUILDER_GUIDE.md` - Complete reference guide
- `docs/AI_AGENT_CACHE_TEMPLATE.md` - AI context cache
- `docs/AGENTS_TEMPLATE.md` - AI agent instructions

## Requirements

- PHP >= 7.4
- WordPress >= 5.8
- Python 3.x (for generator and build scripts)

## License

GPL v2 or later
