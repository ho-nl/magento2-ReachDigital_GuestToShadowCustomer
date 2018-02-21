<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Test\Integration;


use Ho\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\CustomerRegistry;
use Ho\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToCustomerException;
use Ho\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToShadowCustomerException;
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

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    protected $_customerRegistry;

    protected function setUp()
    {
        parent::setUp();
        $this->_objectManager = Bootstrap::getObjectManager();
        $this->_convertGuestOrderToShadowCustomer = $this->_objectManager->create(ConvertGuestOrderToShadowCustomerInterface::class);
        $this->_customerRegistry = $this->_objectManager->create(CustomerRegistry::class);

    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testConvertGuestOrderToShadowCustomer()
    {
        $order = $this->_objectManager->create(OrderInterface::class);
        $customerRepository = $this->_objectManager->create(CustomerRepositoryInterface::class);
        $order->loadByIncrementId('100000001');
        $this->_convertGuestOrderToShadowCustomer->execute($order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     */
    public function testOrderAlreadyAssignedToCustomerException()
    {
        $order = $this->_objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $this->expectException(OrderAlreadyAssignedToCustomerException::class);
        $this->_convertGuestOrderToShadowCustomer->execute($order->getId());
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderAlreadyAssignedToShadowCustomerException()
    {
        $order = $this->_objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $this->_convertGuestOrderToShadowCustomer->execute($order->getId());
        $this->expectException(OrderAlreadyAssignedToShadowCustomerException::class);
        $this->_convertGuestOrderToShadowCustomer->execute($order->getId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShadowCustomerWithoutPasswordHash()
    {
        $order              = $this->_objectManager->create(
            OrderInterface::class
        );
        $customerRepository = $this->_objectManager->create(
            CustomerRepositoryInterface::class
        );
        $order->loadByIncrementId('100000001');
        $this->_convertGuestOrderToShadowCustomer->execute($order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertNull($this->_customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash());
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     */
    public function testCustomerWithPasswordHash()
    {

        $customerRepository = $this->_objectManager->create(
            CustomerRepositoryInterface::class
        );
        $customer = $customerRepository->get('customer@example.com');
        $this->assertNotNull($this->_customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash());
    }
}