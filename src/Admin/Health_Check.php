<?php
declare(strict_types=1);

namespace CartQuoteWooCommerce\Admin;

/**
 * Health Check for Plugin Integrity
 *
 * Provides WordPress Site Health integration to verify
 * all required plugin files are present and accessible.
 *
 * @package CartQuoteWooCommerce\Admin
 * @since 1.0.13
 */

class Health_Check
{
    /**
     * List of critical files required for plugin operation
     *
     * @var array
     */
    private const REQUIRED_FILES = [
        'src/Core/Activator.php' => 'Plugin activation',
        'src/Core/Deactivator.php' => 'Plugin deactivation',
        'src/Core/Plugin.php' => 'Main plugin class',
        'src/Frontend/Frontend_Manager.php' => 'Frontend functionality',
        'templates/admin/quotes-list.php' => 'Admin interface',
    ];

    /**
     * Check plugin integrity
     *
     * @return array{status: string, issues: array}
     */
    public static function check_plugin_integrity(): array
    {
        $issues = [];
        $plugin_dir = plugin_dir_path(CART_QUOTE_WC_PLUGIN_FILE);

        foreach (self::REQUIRED_FILES as $file => $purpose) {
            $full_path = $plugin_dir . $file;
            if (!file_exists($full_path)) {
                $issues[] = [
                    'file' => $file,
                    'purpose' => $purpose,
                    'severity' => 'critical'
                ];
            }
        }

        return [
            'status' => empty($issues) ? 'healthy' : 'unhealthy',
            'issues' => $issues
        ];
    }

    /**
     * Register health check with WordPress Site Health
     *
     * @return void
     */
    public static function register_health_check(): void
    {
        add_filter('site_status_tests', function($tests) {
            $tests['direct']['cart_quote_integrity'] = [
                'label' => __('Cart Quote Plugin Integrity', 'cart-quote-woocommerce-email'),
                'test' => [self::class, 'run_test']
            ];
            return $tests;
        });
    }

    /**
     * Run health check test
     *
     * @return array{label: string, status: string, badge: array, description: string, actions?: string}
     */
    public static function run_test(): array
    {
        $check = self::check_plugin_integrity();

        if ($check['status'] === 'healthy') {
            return [
                'label' => __('Cart Quote Plugin files are intact', 'cart-quote-woocommerce-email'),
                'status' => 'good',
                'badge' => [
                    'label' => __('Plugin Integrity', 'cart-quote-woocommerce-email'),
                    'color' => 'green'
                ],
                'description' => __(
                    'All required plugin files are present and accessible.',
                    'cart-quote-woocommerce-email'
                )
            ];
        } else {
            $missing_files = array_column($check['issues'], 'file');
            $message = sprintf(
                __(
                    'Missing %d critical file(s): %s',
                    'cart-quote-woocommerce-email'
                ),
                count($check['issues']),
                implode(', ', $missing_files)
            );

            return [
                'label' => __('Cart Quote Plugin has missing files', 'cart-quote-woocommerce-email'),
                'status' => 'critical',
                'badge' => [
                    'label' => __('Plugin Integrity', 'cart-quote-woocommerce-email'),
                    'color' => 'red'
                ],
                'description' => $message,
                'actions' => sprintf(
                    '<p><a href="%s" class="button">%s</a></p>',
                    admin_url('plugins.php'),
                    __('Reinstall the plugin', 'cart-quote-woocommerce-email')
                )
            ];
        }
    }
}
