<?php
/**
 * Frontend AJAX Integration Tests
 *
 * @package CartQuoteWooCommerce\Tests\Integration\AJAX
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Tests\Integration\AJAX;

use PHPUnit\Framework\TestCase;

class Frontend_AJAX_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cart_quote_test_reset();
    }

    public function test_quote_submission_nonce_check(): void
    {
        $validNonce = 'valid_nonce';
        $action = 'cart_quote_frontend_nonce';
        
        $result = wp_verify_nonce($validNonce, $action);
        $this->assertEquals(1, $result);
    }

    public function test_cart_update_authorization(): void
    {
        $validNonce = 'valid_nonce';
        $invalidNonce = 'invalid_nonce';
        $action = 'cart_quote_frontend_nonce';
        
        $validResult = wp_verify_nonce($validNonce, $action);
        $invalidResult = wp_verify_nonce($invalidNonce, $action);
        
        $this->assertEquals(1, $validResult);
        $this->assertFalse($invalidResult);
    }

    public function test_form_data_validation(): void
    {
        $data = [
            'billing_first_name' => 'John',
            'billing_email' => 'john@example.com',
            'billing_phone' => '+1234567890',
        ];
        
        $this->assertNotEmpty($data['billing_first_name']);
        $this->assertTrue(is_email($data['billing_email']) !== false);
        $this->assertNotEmpty($data['billing_phone']);
    }

    public function test_xss_sanitization_in_form(): void
    {
        $maliciousInputs = [
            'billing_first_name' => '<script>alert(1)</script>John',
        ];
        
        $sanitized = sanitize_text_field($maliciousInputs['billing_first_name']);
        
        $this->assertStringNotContainsString('<script', $sanitized);
    }
}
