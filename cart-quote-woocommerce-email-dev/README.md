# Cart Quote WooCommerce & Email

Transform WooCommerce checkout into a quote submission system with Google Calendar integration.

## Repository Structure

```
cart-quote-woocommerce-email-dev/
├── plugin/                    # Production plugin (for distribution)
│   ├── cart-quote-woocommerce-email.php
│   ├── src/                   # Plugin source code
│   ├── assets/                # CSS, JS, images
│   ├── templates/             # Email and frontend templates
│   ├── readme.txt             # WordPress.org readme
│   └── composer.json          # Production dependencies
│
├── tests/                     # All tests (not distributed)
│   ├── phpunit/               # PHPUnit unit/integration tests
│   │   ├── Unit/
│   │   ├── Integration/
│   │   ├── bootstrap.php
│   │   ├── phpunit.xml
│   │   └── composer.json
│   │
│   ├── cypress/               # Cypress E2E tests
│   │   ├── e2e/
│   │   ├── support/
│   │   └── package.json
│   │
│   └── security/              # Security testing tools
│       ├── sqlmap/
│       ├── zap/
│       └── manual/
│
├── .github/                   # GitHub Actions workflows
│   └── workflows/
│       ├── test.yml
│       ├── security-scan.yml
│       └── release.yml
│
├── build.sh                   # Build distribution script
├── .gitignore
└── README.md
```

## Development

### Requirements

- PHP >= 7.4
- Node.js >= 18 (for Cypress)
- Composer (for PHPUnit)

### Running Tests

**PHPUnit:**
```bash
cd tests/phpunit
composer install
vendor/bin/phpunit
```

**Cypress:**
```bash
cd tests/cypress
npm install
npm run cypress:open    # Interactive mode
npm run cypress:run     # Headless mode
```

### Building Distribution

```bash
./build.sh 1.0.6
```

Creates `cart-quote-woocommerce-email-1.0.6.zip` ready for WordPress.org upload.

## License

GPL-2.0-or-later
