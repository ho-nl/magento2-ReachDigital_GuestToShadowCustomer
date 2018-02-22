<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

//declare(strict_types=1);

namespace Ho\GuestToShadowCustomer\Test\Integration;

use Ho\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;


class GuestOrderRepositoryTest extends TestCase
{
    /** @var  GuestOrderRepositoryInterface */
    protected $_guestOrderRepository;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    protected function setUp()
    {
        parent::setUp();
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->_guestOrderRepository = $this->_objectManager->create(GuestOrderRepositoryInterface::class);
        $this->_searchCriteriaBuilder = $this->_objectManager->create(SearchCriteriaBuilder::class);
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testGuestOrderRepositoryList()
    {
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $this->assertEquals(1, $this->_guestOrderRepository->getList($searchCriteria)->getTotalCount());
    }
}