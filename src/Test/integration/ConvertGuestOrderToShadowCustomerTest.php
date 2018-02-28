<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;


use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\CustomerRegistry;
use ReachDigital\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToCustomerException;
use ReachDigital\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToShadowCustomerException;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\AlreadyExistsException;
class ConvertGuestOrderToShadowCustomerTest extends TestCase
{

    /**
     * @var \ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface
     */
    private $convertGuestOrderToShadowCustomer;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Customer\Model\CustomerRegistry
     */
    private $customerRegistry;

    private function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->convertGuestOrderToShadowCustomer = $this->objectManager->create(ConvertGuestOrderToShadowCustomerInterface::class);
        $this->customerRegistry = $this->objectManager->create(CustomerRegistry::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testConvertGuestOrderToShadowCustomer()
    {
        $order = $this->objectManager->create(OrderInterface::class);
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $order->loadByIncrementId('100000001');
        // @todo aparte exception test functie toevoegen
//        $customer = $customerRepository->get('customer@null.com');
//        $this->assertNull($customer);
        $this->convertGuestOrderToShadowCustomer->execute($order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testConvertGuestOrderToShadowCustomerWithoutNewAccountConfirmation()
    {
        // @todo Nog even een test erbij voor MET confirmation, en verplaatsen naar een aparte file qua benaming als de plugin.
        $order = $this->objectManager->create(OrderInterface::class);
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute($order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
        $transportBuilderMock = $this->objectManager->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class);
        /** @var TransportBuilderMock $transportBuilderMock */
        $this->assertNull($transportBuilderMock->getSentMessage());
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     */
    public function testOrderAlreadyAssignedToCustomerException()
    {
        $order = $this->objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $this->expectException(OrderAlreadyAssignedToCustomerException::class);
        $this->convertGuestOrderToShadowCustomer->execute($order->getId());
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderAlreadyAssignedToShadowCustomerException()
    {
        $order = $this->objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute($order->getId());
        $this->expectException(OrderAlreadyAssignedToShadowCustomerException::class);
        $this->convertGuestOrderToShadowCustomer->execute($order->getId());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testShadowCustomerWithoutPasswordHash()
    {
        $order              = $this->objectManager->create(
            OrderInterface::class
        );
        $customerRepository = $this->objectManager->create(
            CustomerRepositoryInterface::class
        );
        $order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute($order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertNull($this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash());
    }


    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     */
    public function testCustomerWithPasswordHash()
    {

        $customerRepository = $this->objectManager->create(
            CustomerRepositoryInterface::class
        );
        $customer = $customerRepository->get('customer@example.com');
        $this->assertNotNull($this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash());
    }


//    /**
//     * @magentoDataFixture Magento/Sales/_files/order.php
//     */
//    public function testCreateAccountForShadowCustomer()
//    {
//
//        $order = $this->objectManager->create(OrderInterface::class);
//        $order->loadByIncrementId('100000001');
//        $this->convertGuestOrderToShadowCustomer->execute($order->getId());
//
//        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
//        /** @var Customer $customer */
//        $customer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
//            CustomerInterface::class
//        );
//        $customer->setEmail("customer@null.com");
//        $customer->setFirstname("firstname");
//        $customer->setLastname("lastname");
//        $customerRepository->save($customer);
//    }
}