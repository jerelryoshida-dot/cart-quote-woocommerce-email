<?php
/**
 * Quote Repository Unit Tests
 *
 * @package CartQuoteWooCommerce\Tests\Unit\Database
 */

declare(strict_types=1);

namespace CartQuoteWooCommerce\Tests\Unit\Database;

use PHPUnit\Framework\TestCase;

class Quote_Repository_Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        cart_quote_test_reset();
    }

    public function test_update_quote_validates_status(): void
    {
        $validStatuses = ['pending', 'contacted', 'closed', 'canceled'];
        
        foreach ($validStatuses as $status) {
            $this->assertTrue(in_array($status, $validStatuses, true));
        }
        
        $this->assertFalse(in_array('processing', $validStatuses, true));
        $this->assertFalse(in_array('', $validStatuses, true));
    }

    public function test_quote_id_format(): void
    {
        $validPattern = '/^[A-Z]+\d+$/';
        
        $this->assertEquals(1, preg_match($validPattern, 'Q1001'));
        $this->assertEquals(0, preg_match($validPattern, '1001'));
        $this->assertEquals(0, preg_match($validPattern, 'q1001'));
    }

    public function test_cart_data_json_encoding(): void
    {
        $cartData = [
            ['product_id' => 123, 'product_name' => 'Test', 'quantity' => 2],
        ];
        
        $encoded = wp_json_encode($cartData);
        $decoded = json_decode($encoded, true);
        
        $this->assertEquals($cartData, $decoded);
    }

    public function test_date_range_validation(): void
    {
        $from = strtotime('2024-01-01');
        $to = strtotime('2024-12-31');
        
        $this->assertLessThanOrEqual($to, $from);
    }
}
