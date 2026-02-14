<?php
/**
 * Debug Logger
 *
 * Centralized logging for Cart Quote plugin. Only logs when WordPress
 * debug mode is enabled (WP_DEBUG and WP_DEBUG_LOG must both be true).
 *
 * @package CartQuoteWooCommerce\Core
 * @author Jerel Yoshida
 * @since 1.0.9
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Core;

class Debug_Logger
{
    private static ?self $instance = null;

    private bool $enabled;

    private string $prefix = '[Cart Quote]';

    private function __construct()
    {
        $this->enabled = $this->is_logging_enabled();
    }

    public static function get_instance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function is_logging_enabled(): bool
    {
        return defined('WP_DEBUG') && WP_DEBUG === true
            && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG === true;
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context, true);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context, false);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context, false);
    }

    public function debug(string $message, array $context = []): void
    {
        $this->log('DEBUG', $message, $context, false);
    }

    private function log(string $level, string $message, array $context, bool $include_trace): void
    {
        if (!$this->enabled) {
            return;
        }

        $log_entry = $this->prefix . ' ' . $level . ': ' . $message;

        if (!empty($context)) {
            $log_entry .= ' | Context: ' . $this->format_context($context);
        }

        if ($include_trace) {
            $log_entry .= $this->get_stack_trace();
        }

        error_log($log_entry);
    }

    private function format_context(array $context): string
    {
        $sanitized = $this->sanitize_context($context);
        return wp_json_encode($sanitized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '{}';
    }

    private function sanitize_context(array $context): array
    {
        $sanitized = [];
        $sensitive_keys = ['password', 'secret', 'token', 'api_key', 'apikey', 'credential', 'auth'];

        foreach ($context as $key => $value) {
            $key_lower = strtolower((string) $key);
            $is_sensitive = false;

            foreach ($sensitive_keys as $sensitive) {
                if (str_contains($key_lower, $sensitive)) {
                    $is_sensitive = true;
                    break;
                }
            }

            if ($is_sensitive) {
                $sanitized[$key] = '***REDACTED***';
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitize_context($value);
            } elseif (is_object($value)) {
                $sanitized[$key] = get_class($value);
            } elseif (is_resource($value)) {
                $sanitized[$key] = 'resource';
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    private function get_stack_trace(): string
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
        $trace_lines = [];

        foreach ($trace as $index => $item) {
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
}
