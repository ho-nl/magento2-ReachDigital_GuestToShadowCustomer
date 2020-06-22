<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use Magento\Framework\Api\SearchCriteriaInterface;
use ReachDigital\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class GuestOrderRepositoryTest extends TestCase
{
    /** @var  GuestOrderRepositoryInterface */
    private $guestOrderRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /** @var  SearchCriteriaInterface */
    private $searchCriteriaInterface;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->guestOrderRepository = $this->objectManager->create(GuestOrderRepositoryInterface::class);
        $this->searchCriteriaInterface = $this->objectManager->create(SearchCriteriaInterface::class);
    }

    public static function createOrder(): void
    {
        include __DIR__ . '/_files/order.php';
    }

    public static function createOrderWithCustomer(): void
    {
        include __DIR__ . '/_files/order_with_customer.php';
    }

    /**
     * @test
     * @magentoDataFixture createOrder
     */
    public function should_return_guest_order()
    {
        $this->assertEquals(1, $this->guestOrderRepository->getList($this->searchCriteriaInterface)->getTotalCount());
    }

    /**
     * @test
     * @magentoDataFixture createOrderWithCustomer
     */
    public function should_not_return_guest_order()
    {
        $this->assertEquals(0, $this->guestOrderRepository->getList($this->searchCriteriaInterface)->getTotalCount());
    }
}
