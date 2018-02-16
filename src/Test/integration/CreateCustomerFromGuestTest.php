<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Test\integration;

use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;

class CreateCustomerFromGuestTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @magentoDataFixture getQuoteWithProductAndCustomerToOrderDataFixture
     */
    public function testCreateCustomerFromGuest()
    {

    }


    public static function getQuoteWithProductAndCustomerToOrderDataFixture()
    {
        include __DIR__ . '/_files/quote_with_product_and_customer_to_order.php';
    }
}