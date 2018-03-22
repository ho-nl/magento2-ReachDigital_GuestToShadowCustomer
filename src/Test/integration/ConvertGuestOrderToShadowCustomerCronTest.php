<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use ReachDigital\GuestToShadowCustomer\Cron\ConvertGuestOrderToShadowCustomerCron;
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
    private $objectManager;

    /**
     * @var ConvertGuestOrderToShadowCustomerCron
     */
    private $convertGuestOrderToShadowCustomerCron;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /** @var  SearchCriteria */
    private $searchCriteria;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager         = Bootstrap::getObjectManager();
        $this->convertGuestOrderToShadowCustomerCron = $this->objectManager->create(ConvertGuestOrderToShadowCustomerCron::class);
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(SearchCriteriaBuilder::class);
        $this->searchCriteria = $this->searchCriteriaBuilder->create();
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/two_orders_for_one_of_two_customers.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testExecute()
    {
        // @todo hoe gaan we om met 100.000 orders? Aparte test hiervoor. User Story 9
        $this->convertGuestOrderToShadowCustomerCron->execute();
        $customers = $this->customerRepository->getList($this->searchCriteria);
        $this->assertEquals(3, $customers->getTotalCount());
    }
}