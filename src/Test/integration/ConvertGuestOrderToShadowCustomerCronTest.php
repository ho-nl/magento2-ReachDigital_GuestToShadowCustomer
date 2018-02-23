<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Test\Integration;

use Ho\GuestToShadowCustomer\Cron\ConvertGuestOrderToShadowCustomerCron;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteria;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;

class ConvertGuestOrderToShadowCustomerCronTest extends TestCase
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ConvertGuestOrderToShadowCustomerCron
     */
    protected $_convertGuestOrderToShadowCustomerCron;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;

    /** @var  SearchCriteria */
    protected $_searchCriteria;

    protected function setUp()
    {
        parent::setUp();
        $this->_objectManager         = Bootstrap::getObjectManager();
        $this->_convertGuestOrderToShadowCustomerCron = $this->_objectManager->create(ConvertGuestOrderToShadowCustomerCron::class);
        $this->_customerRepository = $this->_objectManager->create(CustomerRepositoryInterface::class);
        $this->_searchCriteriaBuilder = $this->_objectManager->create(SearchCriteriaBuilder::class);
        $this->_searchCriteria = $this->_searchCriteriaBuilder->create();
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/two_orders_for_one_of_two_customers.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testExecute()
    {
        $this->_convertGuestOrderToShadowCustomerCron->execute();
        $customers = $this->_customerRepository->getList($this->_searchCriteria);
        $this->assertEquals(3, $customers->getTotalCount());
    }
}