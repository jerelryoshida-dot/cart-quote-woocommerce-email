<?php
/**
 * Debug Logger
 *
 * Centralized logging system for WordPress plugins.
 * Only logs when BOTH WP_DEBUG and WP_DEBUG_LOG are enabled.
 *
 * FEATURES:
 * - Four log levels: error, warning, info, debug
 * - Automatic sensitive data redaction
 * - Stack traces for errors
 * - Singleton pattern
 *
 * USAGE:
 *   $logger = Debug_Logger::get_instance();
 *   $logger->error('Database query failed', ['query' => $sql]);
 *   $logger->warning('Cache miss', ['key' => $key]);
 *   $logger->info('User logged in', ['user_id' => $user_id]);
 *   $logger->debug('Request data', ['post' => $_POST]);
 *
 * LOG LOCATION:
 *   wp-content/debug.log
 *
 * @package PLUGIN_NAMESPACE\Core
 * @author YOUR_NAME
 * @since 1.0.0
 */

declare(strict_types=1);

namespace PLUGIN_NAMESPACE\Core;

class Debug_Logger
{
    /**
     * Singleton instance
     *
     * @var self|null
     */
    private static ?self $instance = null;

    /**
     * Is logging enabled?
     * Requires both WP_DEBUG and WP_DEBUG_LOG to be true
     *
     * @var bool
     */
    private bool $enabled;

    /**
     * Log prefix for identifying plugin logs
     *
     * @var string
     */
    private string $prefix = '[PLUGIN_NAME]';

    /**
     * Sensitive keys to redact from logs
     *
     * @var array<string>
     */
    private array $sensitive_keys = [
        'password',
        'secret',
        'token',
        'api_key',
        'apikey',
        'credential',
        'auth',
        'private_key',
        'access_token',
        'refresh_token',
    ];

    /**
     * Private constructor (singleton pattern)
     */
    private function __construct()
    {
        $this->enabled = $this->is_logging_enabled();
    }

    /**
     * Get singleton instance
     *
     * @return self
     */
    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Check if WordPress debugging is enabled
     *
     * @return bool
     */
    private function is_logging_enabled(): bool
    {
        return defined('WP_DEBUG') && WP_DEBUG === true
            && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === true;
    }

    /**
     * ============================================================================
     * LOG METHODS
     * ============================================================================
     */

    /**
     * Log an error message
     *
     * Use for: Critical failures, exceptions, database errors
     * Includes: Stack trace automatically
     *
     * @param string $message Log message
     * @param array<string, mixed> $context Additional context data
     * @return void
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context, true);
    }

    /**
     * Log a warning message
     *
     * Use for: Non-critical issues, recoverable errors, deprecations
     * Includes: No stack trace
     *
     * @param string $message Log message
     * @param array<string, mixed> $context Additional context data
     * @return void
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context, false);
    }

    /**
     * Log an info message
     *
     * Use for: Successful operations, state changes, significant events
     * Includes: No stack trace
     *
     * @param string $message Log message
     * @param array<string, mixed> $context Additional context data
     * @return void
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context, false);
    }

    /**
     * Log a debug message
     *
     * Use for: Detailed debugging info, variable dumps, flow tracking
     * Includes: No stack trace
     *
     * @param string $message Log message
     * @param array<string, mixed> $context Additional context data
     * @return void
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context, false);
    }

    /**
     * Internal logging method
     *
     * @param string $level Log level (ERROR, WARNING, INFO, DEBUG)
     * @param string $message Log message
     * @param array<string, mixed> $context Context data
     * @param bool $include_trace Include stack trace?
     * @return void
     */
    private function log(string $level, string $message, array $context, bool $include_trace): void
    {
        // Skip if logging is disabled
        if (!$this->enabled) {
            return;
        }

        // Build log entry
        $log_entry = $this->prefix . ' ' . $level . ': ' . $message;

        // Add context if provided
        if (!empty($context)) {
            $log_entry .= ' | Context: ' . $this->format_context($context);
        }

        // Add stack trace for errors
        if ($include_trace) {
            $log_entry .= $this->get_stack_trace();
        }

        // Write to WordPress debug log
        error_log($log_entry);
    }

    /**
     * Format context data for logging
     *
     * Sanitizes sensitive data and converts to JSON.
     *
     * @param array<string, mixed> $context Context data
     * @return string JSON encoded context
     */
    private function format_context(array $context): string
    {
        $sanitized = $this->sanitize_context($context);
        return wp_json_encode($sanitized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    /**
     * Sanitize context data by redacting sensitive values
     *
     * @param array<string, mixed> $context Context data
     * @return array<string, mixed> Sanitized context
     */
    private function sanitize_context(array $context): array
    {
        $sanitized = [];

        foreach ($context as $key => $value) {
            $key_lower = strtolower((string) $key);
            $is_sensitive = false;

            // Check if key contains sensitive words
            foreach ($this->sensitive_keys as $sensitive) {
                if (str_contains($key_lower, $sensitive)) {
                    $is_sensitive = true;
                    break;
                }
            }

            if ($is_sensitive) {
                // Redact sensitive values
                $sanitized[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                // Recursively sanitize arrays
                $sanitized[$key] = $this->sanitize_context($value);
            } elseif (is_object($value)) {
                // Convert objects to class name
                $sanitized[$key] = get_class($value);
            } elseif (is_resource($value)) {
                // Identify resources
                $sanitized[$key] = 'resource';
            } else {
                // Pass through other values
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Get formatted stack trace
     *
     * Skips internal logger frames for cleaner output.
     *
     * @return string Formatted stack trace
     */
    private function get_stack_trace(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $trace_lines = [];

        foreach ($trace as $index => $item) {
            // Skip first two frames (internal logger calls)
            if ($index < 2) {
                continue;
            }

            $file = $item['file'] ?? 'unknown';
            $line = $item['line'] ?? 0;
            $class = $item['class'] ?? '';
            $function = $item['function'] ?? '';

            $trace_lines[] = sprintf(
                '#%d %s:%d %s%s%s()',
                $index - 2,
                basename($file),
                $line,
                $class,
                $class ? '->' : '',
                $function
            );
        }

        if (empty($trace_lines)) {
            return '';
        }

        return ' | Trace: ' . implode(' -> ', $trace_lines);
    }

    /**
     * Prevent cloning (singleton pattern)
     */
    private function __clone()
    {
    }

    /**
     * Prevent unserialization (singleton pattern)
     *
     * @throws \Exception
     */
    public function __wakeup()
    {
        throw new \Exception('Cannot unserialize singleton');
    }
}
