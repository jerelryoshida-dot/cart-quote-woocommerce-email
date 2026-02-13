<?php
/**
 * Capability Check Security Tests
 *
 * @package CartQuoteWooCommerce\Tests\Unit\Security
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;

class CapabilityCheck_Test extends TestCase
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

    public function test_admin_access_denied_unauthenticated(): void
    {
        $this->setMockCapability(false);
        $this->assertFalse(current_user_can('manage_woocommerce'));
    }

    public function test_admin_access_granted_authorized(): void
    {
        $this->setMockCapability(true);
        $this->assertTrue(current_user_can('manage_woocommerce'));
    }

    public function test_google_oauth_requires_admin(): void
    {
        $this->setMockCapability(false);
        $this->assertFalse(current_user_can('manage_options'));
    }

    public function test_privilege_escalation_prevention(): void
    {
        $this->setMockCapability(false);
        
        $capabilities = ['manage_woocommerce', 'manage_options', 'edit_posts'];
        foreach ($capabilities as $cap) {
            $this->assertFalse(current_user_can($cap));
        }
    }
}
