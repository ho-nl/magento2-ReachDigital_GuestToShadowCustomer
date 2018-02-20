<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Test\Integration;

use Ho\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
class ConvertGuestOrderToShadowCustomerTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var \Ho\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface
     */
    protected $_convertGuestOrderToShadowCustomer;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp()
    {
        parent::setUp();
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->_convertGuestOrderToShadowCustomer = $this->_objectManager->create(ConvertGuestOrderToShadowCustomerInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testConvertGuestOrderToShadowCustomer()
    {
        $order = $this->_objectManager->create(OrderInterface::class);
        $customerRepository = $this->_objectManager->create(CustomerRepositoryInterface::class);
        $order->loadByIncrementId('100000001');
        $this->expectException(NoSuchEntityException::class);
        $customerRepository->get('customer@null.com');
        $this->_convertGuestOrderToShadowCustomer->execute($order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderAlreadyAssignedToCustomerException()
    {
        $this->testConvertGuestOrderToShadowCustomer();
        $order = $this->_objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $this->_convertGuestOrderToShadowCustomer->execute($order->getId());
    }

    public function testOrderAlreadyAssignedToShadowCustomerException()
    {

    }
}