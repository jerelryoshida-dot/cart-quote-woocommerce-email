<?php
/**
 * Nonce Verification Security Tests
 *
 * @package CartQuoteWooCommerce\Tests\Unit\Security
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

class NonceVerification_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cart_quote_test_reset();
    }

    public function test_frontend_nonce_valid(): void
    {
        $nonce = 'valid_nonce';
        $action = 'cart_quote_frontend_nonce';
        
        $result = wp_verify_nonce($nonce, $action);
        $this->assertEquals(1, $result);
    }

    public function test_frontend_nonce_invalid(): void
    {
        $nonce = 'invalid_nonce_value';
        $action = 'cart_quote_frontend_nonce';
        
        $result = wp_verify_nonce($nonce, $action);
        $this->assertFalse($result);
    }

    public function test_admin_nonce_valid(): void
    {
        $nonce = 'valid_nonce';
        $action = 'cart_quote_admin_nonce';
        
        $result = wp_verify_nonce($nonce, $action);
        $this->assertEquals(1, $result);
    }

    public function test_nonce_creation(): void
    {
        $action = 'cart_quote_frontend_nonce';
        $nonce = wp_create_nonce($action);
        
        $this->assertNotEmpty($nonce);
    }

    public function test_different_actions_different_nonces(): void
    {
        $frontendNonce = wp_create_nonce('cart_quote_frontend_nonce');
        $adminNonce = wp_create_nonce('cart_quote_admin_nonce');
        
        $this->assertNotEquals($frontendNonce, $adminNonce);
    }
}
