<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Mail\TransportInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;

class CreateNewAccountEmailNotificationInterfacePluginTest extends TestCase
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
        $this->convertGuestOrderToShadowCustomer = $this->objectManager->create(
            ConvertGuestOrderToShadowCustomerInterface::class
        );
        $this->accountManagement = $this->objectManager->create(AccountManagementInterface::class);
        $this->customerFactory = $this->objectManager->create(CustomerInterfaceFactory::class);
        $this->order = $this->objectManager->create(OrderInterface::class);
        $this->customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
    }

    /**
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function should_create_shadow_customer_with_no_email_notification()
    {
        $this->order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $this->order->getId());
        $customer = $this->customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $body = $transportInterface->getMessage()->getBody();
        // Depending on the implementation of TransportInterface either false or null is returned.
        //        $this->assertTrue($body === false || $body === null);
    }

    /**
     * @test
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function should_be_able_to_register_as_customer_for_a_shadow_customer()
    {
        $this->order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $this->order->getId());
        $shadowCustomer = $this->customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $shadowCustomer->getEmail());
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
        $this->assertEquals($email, $savedCustomer->getEmail());
        $this->assertEquals($storeId, $savedCustomer->getStoreId());
        $this->assertEquals($storeId, $savedCustomer->getWebsiteId());
        $this->assertEquals($firstname, $savedCustomer->getFirstname());
        $this->assertEquals($lastname, $savedCustomer->getLastname());
        $this->assertEquals($groupId, $savedCustomer->getGroupId());
        $this->assertTrue(!$savedCustomer->getSuffix());
    }

    /**
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function should_send_welcome_email_when_shadow_customer_is_converted_to_customer()
    {
        $this->order->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $this->order->getId());
        $shadowCustomer = $this->customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $shadowCustomer->getEmail());
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
        $this->assertEquals($email, $savedCustomer->getEmail());
        $this->assertEquals($storeId, $savedCustomer->getStoreId());
        $this->assertEquals($websiteId, $savedCustomer->getWebsiteId());
        $this->assertEquals($firstname, $savedCustomer->getFirstname());
        $this->assertEquals($lastname, $savedCustomer->getLastname());
        $this->assertEquals($groupId, $savedCustomer->getGroupId());
        $this->assertTrue(!$savedCustomer->getSuffix());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $this->assertNotFalse($transportInterface->getMessage()->getBody());
    }
}
