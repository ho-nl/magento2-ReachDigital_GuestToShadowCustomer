<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

//declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use ReachDigital\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
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

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    private function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->guestOrderRepository = $this->objectManager->create(GuestOrderRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGuestOrderRepositoryList()
    {
        // @todo nog een fixture erbij om te kijken of er daadwerkelijke die ene guestorder opgehaald wordt.
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $this->assertEquals(1, $this->guestOrderRepository->getList($searchCriteria)->getTotalCount());
    }
}