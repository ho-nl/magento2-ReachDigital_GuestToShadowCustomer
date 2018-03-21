<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Mail\Template\TransportBuilderMock;
use PHPUnit\Framework\TestCase;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;

class PreventNewAccountEmailNotificationInterfacePluginTest extends TestCase
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface
     */
    private $convertGuestOrderToShadowCustomer;

    /**
     * @var \Magento\Customer\Api\Data\CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var \Magento\Customer\Api\AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    private $order;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->convertGuestOrderToShadowCustomer = $this->objectManager->create(ConvertGuestOrderToShadowCustomerInterface::class);
        $this->accountManagement = $this->objectManager->create(AccountManagementInterface::class);
        $this->customerFactory = $this->objectManager->create(CustomerInterfaceFactory::class);
        $this->order = $this->objectManager->create(OrderInterface::class);
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testOrderToShadowCustomerWithoutEmailNotification()
    {
        $this->order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute($this->order->getId());
        $customer = $this->customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        /** @var TransportBuilderMock $transportBuilderMock */
        $this->assertNull($transportBuilderMock->getSentMessage());
    }

    /**
     * @magentoDbIsolation disabled
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testNewAccountByShadowCustomerEmailWithNotification()
    {

        $this->order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute($this->order->getId());
        $customer = $this->customerRepository->get('customer@null.com');
        $this->customerRepository->save($customer);

        $email     = 'customer@null.com';
        $storeId   = 1;
        $firstname = 'Tester';
        $lastname  = 'McTest';
        $groupId   = 1;
        $newCustomerEntity = $this->customerFactory->create()
            ->setStoreId($storeId)
            ->setEmail($email)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setGroupId($groupId);
        $savedCustomer     = $this->accountManagement->createAccount($newCustomerEntity, '_aPassword1');
        $this->assertNotNull($savedCustomer->getId());
        $this->assertEquals($email, $savedCustomer->getEmail());
        $this->assertEquals($storeId, $savedCustomer->getStoreId());
        $this->assertEquals($firstname, $savedCustomer->getFirstname());
        $this->assertEquals($lastname, $savedCustomer->getLastname());
        $this->assertEquals($groupId, $savedCustomer->getGroupId());
        $this->assertTrue(!$savedCustomer->getSuffix());
        $transportBuilderMock = $this->objectManager->get(TransportBuilderMock::class);
        /** @var TransportBuilderMock $transportBuilderMock */
        $this->assertNotNull($transportBuilderMock->getSentMessage());
    }
}
