<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Customer\Model\CustomerRegistry;
use ReachDigital\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToCustomerException;
use ReachDigital\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToShadowCustomerException;
use PHPUnit\Framework\TestCase;
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

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var AccountManagementInterface */
    private $accountManagement;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->convertGuestOrderToShadowCustomer = $this->objectManager->create(
            ConvertGuestOrderToShadowCustomerInterface::class
        );
        $this->customerRegistry = $this->objectManager->create(CustomerRegistry::class);
        $this->customerFactory = $this->objectManager->create(CustomerInterfaceFactory::class);
        $this->accountManagement = $this->objectManager->create(AccountManagementInterface::class);
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
    public function should_convert_guest_order_to_shadow_customer()
    {
        $order = $this->objectManager->create(OrderInterface::class);
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
    }

    /**
     * @test
     * @magentoDataFixture createOrder
     */
    public function should_assign_guest_order_to_existing_customer_by_email()
    {
        $email = 'customer@null.com';
        $storeId = 1;
        $websiteId = 1;
        $firstname = 'Tester';
        $lastname = 'McTest';
        $groupId = 1;
        $newCustomerEntity = $this->customerFactory
            ->create()
            ->setStoreId($storeId)
            ->setWebsiteId($websiteId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $savedCustomer = $this->accountManagement->createAccount($newCustomerEntity, '_aPassword1');
        $this->assertNotNull($savedCustomer->getId());
        $order = $this->objectManager->create(OrderInterface::class);
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertNotNull($customer->getId());
        $order->loadByIncrementId('100000001');
        $this->assertEquals($order->getCustomerId(), $savedCustomer->getId());
        $this->assertEquals('customer@null.com', $customer->getEmail());
    }

    /**
     * @test
     * @magentoDataFixture createOrder
     */
    public function should_get_no_such_customer_entity_exception()
    {
        $order = $this->objectManager->create(OrderInterface::class);
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $order->loadByIncrementId('100000001');
        $this->expectException(NoSuchEntityException::class);
        $customerRepository->get('customer@null.com');
        $this->convertGuestOrderToShadowCustomer->execute((int) $order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
    }

    /**
     * @test
     * @magentoDataFixture createOrderWithCustomer
     */
    public function should_get_order_already_assigned_to_customer_exception()
    {
        $order = $this->objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $this->expectException(OrderAlreadyAssignedToCustomerException::class);
        $this->convertGuestOrderToShadowCustomer->execute((int) $order->getId());
    }

    /**
     * @test
     * @magentoDataFixture createOrder
     */
    public function should_get_order_already_assigned_to_shadow_customer_exception()
    {
        $order = $this->objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $order->getId());
        $this->expectException(OrderAlreadyAssignedToShadowCustomerException::class);
        $this->convertGuestOrderToShadowCustomer->execute((int) $order->getId());
    }

    /**
     * @test
     * @magentoDataFixture createOrder
     */
    public function should_get_customer_without_password_hash()
    {
        $order = $this->objectManager->create(OrderInterface::class);
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $order->getId());
        $customer = $customerRepository->get('customer@null.com');
        $this->assertNull($this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash());
    }

    /**
     * @magentoDataFixture createOrderWithCustomer
     */
    public function should_get_customer_with_password_hash()
    {
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $customer = $customerRepository->get('customer@example.com');
        $this->assertNotNull($this->customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash());
    }
}
