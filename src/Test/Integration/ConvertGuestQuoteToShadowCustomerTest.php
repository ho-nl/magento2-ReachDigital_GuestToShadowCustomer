<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Helper\Bootstrap;

class ConvertGuestQuoteToShadowCustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    protected function setup()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
    }

    /**
     * @test
     * @magentoDataFixture getQuoteWithProductAndCustomerToOrderDataFixture
     */
    public function should_convert_guest_quote_to_order_with_shadow_customer()
    {
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $orderRepository = $this->objectManager->create(OrderRepository::class);
        $searchCriteriaInterface = $this->objectManager->create(SearchCriteriaInterface::class);
        $order = $orderRepository->getList($searchCriteriaInterface)->getItems();
        $customer = $customerRepository->getList($searchCriteriaInterface)->getItems();
        $this->assertNotEmpty($customer);
        $this->assertNotEmpty($order);
    }

    public static function getQuoteWithProductAndCustomerToOrderDataFixture()
    {
        include __DIR__ . '/_files/quote_with_product_and_customer_to_order.php';
    }
}
