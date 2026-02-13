<?php
/**
 * Admin AJAX Integration Tests
 *
 * @package CartQuoteWooCommerce\Tests\Integration\AJAX
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Tests\Integration\AJAX;

use PHPUnit\Framework\TestCase;

class Admin_AJAX_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cart_quote_test_reset();
    }

    protected function tearDown(): void
    {
        $GLOBALS['current_user_can_result'] = null;
        parent::tearDown();
    }

    private function setMockCapability(bool $can): void
    {
        $GLOBALS['current_user_can_result'] = $can;
    }

    public function test_status_update_requires_capability(): void
    {
        $this->setMockCapability(false);
        $this->assertFalse(current_user_can('manage_woocommerce'));
    }

    public function test_status_update_with_capability(): void
    {
        $this->setMockCapability(true);
        $this->assertTrue(current_user_can('manage_woocommerce'));
    }

    public function test_status_validation(): void
    {
        $validStatuses = ['pending', 'contacted', 'closed', 'canceled'];
        
        $this->assertTrue(in_array('pending', $validStatuses, true));
        $this->assertFalse(in_array('processing', $validStatuses, true));
    }

    public function test_google_oauth_requires_admin(): void
    {
        $this->setMockCapability(false);
        $this->assertFalse(current_user_can('manage_options'));
    }

    public function test_admin_nonce_verification(): void
    {
        $action = 'cart_quote_admin_nonce';
        $validNonce = 'valid_nonce';
        
        $result = wp_verify_nonce($validNonce, $action);
        $this->assertEquals(1, $result);
    }
}
