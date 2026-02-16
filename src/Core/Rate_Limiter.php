<?php
/**
 * Rate Limiter - Prevents abuse of quote submission endpoint
 *
 * @package CartQuoteWooCommerce\Core
 * @author Jerel Yoshida
 * @since 1.0.9
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Core;

class Rate_Limiter
{
    private static $instance = null;
    private static $enabled = true;
    private static $max_per_minute = 5;
    private static $block_duration = 60;

    private function __construct()
    {
    }

    public static function get_instance(): Rate_Limiter
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function init(): void
    {
        self::$enabled = (bool) get_option('cart_quote_wc_rate_limit_enabled', true);
        self::$max_per_minute = (int) get_option('cart_quote_wc_rate_limit_max_per_minute', 5);
        self::$block_duration = (int) get_option('cart_quote_wc_rate_limit_block_duration', 60);
    }

    public static function is_enabled(): bool
    {
        return self::$enabled;
    }

    public static function check_rate_limit(string $ip = null): bool
    {
        if (!self::$enabled) {
            return true;
        }

        if (self::is_whitelisted($ip)) {
            return true;
        }

        $ip = self::get_ip($ip);
        $blocked = self::is_blocked($ip);

        if ($blocked) {
            return false;
        }

        $attempts = self::get_attempts_internal($ip);
        $window_start = time() - 60;

        $recent_attempts = array_filter($attempts, function($timestamp) use ($window_start) {
            return $timestamp >= $window_start;
        });

        if (count($recent_attempts) >= self::$max_per_minute) {
            self::block_ip($ip);
            return false;
        }

        return true;
    }

    public static function is_blocked(string $ip = null): bool
    {
        if (!self::$enabled) {
            return false;
        }

        $ip = self::get_ip($ip);
        $blocked_until = get_transient('cart_quote_blocked_' . md5($ip));

        return $blocked_until !== false && $blocked_until > time();
    }

    public static function increment_attempts(string $ip = null): void
    {
        if (!self::$enabled) {
            return;
        }

        $ip = self::get_ip($ip);

        if (self::is_whitelisted($ip)) {
            return;
        }

        $attempts = self::get_attempts_internal($ip);
        $attempts[] = time();

        \set_transient(
            'cart_quote_attempts_' . md5($ip),
            $attempts,
            300
        );
    }

    public static function reset_attempts(string $ip = null): void
    {
        $ip = self::get_ip($ip);
        \delete_transient('cart_quote_attempts_' . md5($ip));
    }

    public static function get_remaining_attempts(string $ip = null): int
    {
        if (!self::$enabled) {
            return PHP_INT_MAX;
        }

        $ip = self::get_ip($ip);

        if (self::is_whitelisted($ip)) {
            return PHP_INT_MAX;
        }

        $attempts = self::get_attempts_internal($ip);
        $window_start = time() - 60;

        $recent_attempts = array_filter($attempts, function($timestamp) use ($window_start) {
            return $timestamp >= $window_start;
        });

        return max(0, self::$max_per_minute - count($recent_attempts));
    }

    public static function get_block_expiration(string $ip = null): int
    {
        $ip = self::get_ip($ip);
        $blocked_until = \get_transient('cart_quote_blocked_' . md5($ip));

        return $blocked_until !== false ? (int) $blocked_until : 0;
    }

    public static function block_ip(string $ip = null): void
    {
        $ip = self::get_ip($ip);
        $blocked_until = time() + self::$block_duration;

        \set_transient(
            'cart_quote_blocked_' . md5($ip),
            $blocked_until,
            self::$block_duration
        );
    }

    public static function unblock_ip(string $ip = null): void
    {
        $ip = self::get_ip($ip);
        \delete_transient('cart_quote_blocked_' . md5($ip));
    }

    public static function is_whitelisted(string $ip = null): bool
    {
        if (!self::$enabled) {
            return false;
        }

        $ip = self::get_ip($ip);
        $whitelist = get_option('cart_quote_wc_rate_limit_whitelist_ips', '');

        if (empty($whitelist)) {
            return false;
        }

        $whitelist_ips = array_map('trim', explode("\n", $whitelist));

        return in_array($ip, $whitelist_ips, true);
    }

    public static function get_ip(?string $ip = null): string
    {
        if ($ip !== null) {
            return $ip;
        }

        $detected_ip = '';

        return self::detect_ip();
    }

    private static function detect_ip(): string
    {
        $detected_ip = '';

        $trusted_proxies = apply_filters('cart_quote_trusted_proxies', []);
        $is_trusted_proxy = !empty($trusted_proxies) && in_array($_SERVER['REMOTE_ADDR'] ?? '', $trusted_proxies, true);

        if ($is_trusted_proxy && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $forwarded = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (strpos($forwarded, ',') !== false) {
                $ips = array_map('trim', explode(',', $forwarded));
                $detected_ip = $ips[0];
            } else {
                $detected_ip = $forwarded;
            }
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $detected_ip = $_SERVER['REMOTE_ADDR'];
        }

        if (empty($detected_ip)) {
            return 'unknown';
        }

        $detected_ip = filter_var($detected_ip, FILTER_VALIDATE_IP) ? $detected_ip : 'unknown';

        return $detected_ip;
    }

    private static function get_attempts_internal(string $ip): array
    {
        $attempts = \get_transient('cart_quote_attempts_' . md5($ip));

        if ($attempts === false || !is_array($attempts)) {
            return [];
        }

        return $attempts;
    }

    public static function get_blocked_ips(): array
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'options';
        $pattern = 'cart_quote_blocked_%';

        $sql = $wpdb->prepare(
            "SELECT option_name, option_value FROM $table_name WHERE option_name LIKE %s",
            $wpdb->esc_like($pattern) . '%'
        );

        $results = $wpdb->get_results($sql);

        $blocked_ips = [];
        $current_time = time();

        foreach ($results as $result) {
            $blocked_until = (int) $result->option_value;

            if ($blocked_until > $current_time) {
                $ip_hash = str_replace('cart_quote_blocked_', '', $result->option_name);
                $blocked_ips[] = [
                    'ip_hash' => $ip_hash,
                    'blocked_until' => $blocked_until,
                    'remaining_seconds' => $blocked_until - $current_time,
                ];
            }
        }

        return $blocked_ips;
    }

    public static function get_rate_limit_statistics(): array
    {
        return [
            'enabled' => self::$enabled,
            'max_per_minute' => self::$max_per_minute,
            'block_duration' => self::$block_duration,
            'blocked_ips' => count(self::get_blocked_ips()),
        ];
    }

    public static function clear_all_blocks(): int
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'options';
        $pattern = 'cart_quote_blocked_%';

        $sql = $wpdb->prepare(
            "DELETE FROM $table_name WHERE option_name LIKE %s",
            $wpdb->esc_like($pattern) . '%'
        );

        return $wpdb->query($sql);
    }

    public static function clear_all_attempts(): int
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'options';
        $pattern = 'cart_quote_attempts_%';

        $sql = $wpdb->prepare(
            "DELETE FROM $table_name WHERE option_name LIKE %s",
            $wpdb->esc_like($pattern) . '%'
        );

        return $wpdb->query($sql);
    }
}
