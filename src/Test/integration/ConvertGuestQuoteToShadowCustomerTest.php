<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

class ConvertGuestQuoteToShadowCustomerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @test
     * @magentoDataFixture getQuoteWithProductAndCustomerToOrderDataFixture
     */
    public function should_convert_guest_quote_to_order_with_shadow_customer()
    {
    }
    
    public static function getQuoteWithProductAndCustomerToOrderDataFixture()
    {
        include __DIR__ . '/_files/quote_with_product_and_customer_to_order.php';
    }
}