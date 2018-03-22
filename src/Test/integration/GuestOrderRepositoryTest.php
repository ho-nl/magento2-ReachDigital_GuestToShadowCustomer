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


    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShouldReturnGuestOrder()
    {
        $this->assertEquals(1, $this->guestOrderRepository->getList($this->searchCriteriaInterface)->getTotalCount());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     */
    public function testShouldNotReturnGuestOrder()
    {
        $this->assertEquals(0, $this->guestOrderRepository->getList($this->searchCriteriaInterface)->getTotalCount());
    }
}