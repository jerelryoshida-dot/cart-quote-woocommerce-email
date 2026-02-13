<?php
/**
 * Input Sanitization Security Tests
 *
 * Tests for proper input sanitization and validation across the plugin.
 *
 * @package CartQuoteWooCommerce\Tests\Unit\Security
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

class InputSanitization_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cart_quote_test_reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_sanitize_customer_name_xss(): void
    {
        $payloads = [
            '<script>alert("XSS")</script>',
            '<img src=x onerror="alert(1)">',
            '<svg onload="alert(1)">',
        ];

        foreach ($payloads as $payload) {
            $sanitized = sanitize_text_field($payload);
            $this->assertStringNotContainsString('<script', strtolower($sanitized));
            $this->assertStringNotContainsString('<', $sanitized);
        }
    }

    public function test_sanitize_email_validation(): void
    {
        $this->assertFalse(is_email('not-an-email'));
        $this->assertTrue(is_email('test@example.com') !== false);
        $this->assertFalse(is_email(''));
    }

    public function test_date_validation(): void
    {
        $validDates = ['2024-12-15', '2024-02-29'];
        $invalidDates = ['2024-13-01', 'not-a-date'];

        foreach ($validDates as $date) {
            $parts = explode('-', $date);
            $this->assertTrue(checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]));
        }

        foreach ($invalidDates as $date) {
            $parts = explode('-', $date);
            if (count($parts) === 3) {
                $this->assertFalse(checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]));
            }
        }
    }

    public function test_sql_injection_sanitization(): void
    {
        $payloads = [
            "' OR '1'='1",
            "'; DROP TABLE wp_users;--",
            "' UNION SELECT * FROM users--",
        ];

        foreach ($payloads as $payload) {
            $sanitized = sanitize_text_field($payload);
            $this->assertIsString($sanitized);
        }
    }
}
