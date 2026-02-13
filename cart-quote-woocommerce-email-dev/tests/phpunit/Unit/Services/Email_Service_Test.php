<?php
/**
 * Email Service Unit Tests
 *
 * @package CartQuoteWooCommerce\Tests\Unit\Services
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class Email_Service_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cart_quote_test_reset();
    }

    public function test_email_subject_parsing(): void
    {
        $subject = 'New Quote #{quote_id}';
        $replacements = ['{quote_id}' => 'Q1001'];
        $result = str_replace(array_keys($replacements), array_values($replacements), $subject);
        
        $this->assertStringContainsString('Q1001', $result);
        $this->assertStringNotContainsString('{quote_id}', $result);
    }

    public function test_email_escaping(): void
    {
        $maliciousName = '<script>alert(1)</script>John';
        $escaped = esc_html($maliciousName);
        
        $this->assertStringNotContainsString('<script', strtolower($escaped));
    }

    public function test_email_address_validation(): void
    {
        $this->assertFalse(is_email('not-an-email'));
        $this->assertTrue(is_email('test@example.com') !== false);
    }

    public function test_price_formatting(): void
    {
        $price = 100.50;
        $formatted = number_format($price, 2);
        
        $this->assertStringContainsString('100.50', $formatted);
    }
}
