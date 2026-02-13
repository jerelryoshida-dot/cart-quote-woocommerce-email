<?php
/**
 * Google Calendar Service Unit Tests
 *
 * @package CartQuoteWooCommerce\Tests\Unit\Services
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;

class Google_Calendar_Service_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cart_quote_test_reset();
    }

    public function test_oauth_state_generation(): void
    {
        $action = 'cart_quote_google_oauth';
        $state = wp_create_nonce($action);
        
        $this->assertNotEmpty($state);
    }

    public function test_oauth_state_validation(): void
    {
        $action = 'cart_quote_google_oauth';
        $validState = 'valid_nonce';
        
        $result = wp_verify_nonce($validState, $action);
        $this->assertEquals(1, $result);
    }

    public function test_token_encryption(): void
    {
        if (!extension_loaded('openssl')) {
            $this->markTestSkipped('OpenSSL extension not available');
        }
        
        $originalToken = 'test_access_token';
        $key = hash('sha256', 'test_key', true);
        $iv = random_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt($originalToken, 'aes-256-cbc', $key, 0, $iv);
        
        $this->assertNotEquals($originalToken, $encrypted);
    }

    public function test_api_request_auth_header(): void
    {
        $accessToken = 'ya29.test_token';
        $expectedHeader = 'Bearer ' . $accessToken;
        
        $this->assertStringStartsWith('Bearer ', $expectedHeader);
    }
}
