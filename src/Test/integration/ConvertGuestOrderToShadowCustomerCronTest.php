<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use ReachDigital\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;
use ReachDigital\GuestToShadowCustomer\Cron\ConvertGuestOrderToShadowCustomerCron;
use Magento\Framework\Api\SearchCriteria;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class ConvertGuestOrderToShadowCustomerCronTest extends TestCase
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ConvertGuestOrderToShadowCustomerCron
     */
    private $convertGuestOrderToShadowCustomerCron;

    /**
     * @var GuestOrderRepositoryInterface
     */
    private $guestOrderRepository;

    /** @var  SearchCriteria */
    private $searchCriteria;


    /** @var  CustomerRepositoryInterface */
    private $customerRepository;

    /** @var OrderRepositoryInterface */
    private $orderRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager         = Bootstrap::getObjectManager();
        $this->convertGuestOrderToShadowCustomerCron = $this->objectManager->create(ConvertGuestOrderToShadowCustomerCron::class);
        $this->guestOrderRepository = $this->objectManager->create(GuestOrderRepositoryInterface::class);
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->searchCriteria = $this->objectManager->create(SearchCriteriaInterface::class);
        $this->orderRepository = $this->objectManager->create(OrderRepositoryInterface::class);
    }


    /**
     * @test
     * NOTE: order_list.php fixture is not being used because the orders are not fetched with OrderRepositoryInterface
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function should_process_guest_order_to_shadow_customer_via_cron()
    {
        $orders = $this->guestOrderRepository->getList($this->searchCriteria);
        $this->assertEquals(1, $orders->getTotalCount());
        $this->convertGuestOrderToShadowCustomerCron->execute();
        $customer = $this->customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
        $orders = $this->guestOrderRepository->getList($this->searchCriteria);
        $this->assertEquals(0, $orders->getTotalCount());
    }
}