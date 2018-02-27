<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Test\Integration;

class ConvertGuestQuoteToShadowCustomerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @magentoDataFixture getQuoteWithProductAndCustomerToOrderDataFixture
     */
    public function testConvertGuestQuoteToShadowCustomer()
    {

        // @todo check daadwerkelijk of de shadow customer bestaat
        // @todo Model folder van de plugin eruit halen.
    }
    
    public static function getQuoteWithProductAndCustomerToOrderDataFixture()
    {
        include __DIR__ . '/_files/quote_with_product_and_customer_to_order.php';
    }
}