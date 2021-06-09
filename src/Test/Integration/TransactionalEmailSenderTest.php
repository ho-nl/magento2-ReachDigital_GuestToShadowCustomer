<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\TransportInterface;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\CreditmemoSender;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use Magento\Sales\Model\Order\Email\Sender\ShipmentSender;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;

class TransactionalEmailSenderTest extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var ConvertGuestOrderToShadowCustomerInterface */
    private $convertGuestOrderToShadowCustomer;

    /** @var  CustomerRepositoryInterface */
    private $customerRepository;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->convertGuestOrderToShadowCustomer = $this->objectManager->get(
            ConvertGuestOrderToShadowCustomerInterface::class
        );
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
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
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture createOrder
     */
    public function should_send_shadow_customer_guest_order_template()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $orderBefore = $this->objectManager->create(OrderInterface::class);
        $orderBefore->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $orderBefore->getId());
        $orderAfter = $this->objectManager->create(OrderInterface::class);
        $orderAfter->loadByIncrementId('100000001');
        $customer = $this->customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
        $this->assertEmpty($orderAfter->getEmailSent());
        $orderSender = Bootstrap::getObjectManager()->create(OrderSender::class);
        $result = $orderSender->send($orderAfter);
        $this->assertTrue($result);
        $this->assertNotEmpty($orderAfter->getEmailSent());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $this->assertNotContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }

    /**
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture createOrderWithCustomer
     */
    public function should_send_customer_order_template()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $order = $this->objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');
        $this->assertEmpty($order->getEmailSent());
        $orderSender = Bootstrap::getObjectManager()->create(OrderSender::class);
        $result = $orderSender->send($order);
        $this->assertTrue($result);
        $this->assertNotEmpty($order->getEmailSent());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $this->assertContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }

    /**
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture createOrder
     */
    public function should_send_shadow_customer_guest_invoice_template()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $orderBefore = $this->objectManager->create(OrderInterface::class);
        $orderBefore->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $orderBefore->getId());
        $orderAfter = $this->objectManager->create(OrderInterface::class);
        $orderAfter->loadByIncrementId('100000001');
        $customer = $this->customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
        $this->assertEmpty($orderAfter->getEmailSent());

        $invoice = $this->objectManager->create(InvoiceInterface::class);
        $invoice->setOrder($orderAfter);

        /** @var InvoiceSender $invoiceSender */
        $invoiceSender = Bootstrap::getObjectManager()->create(InvoiceSender::class);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $invoiceSender->send($invoice, true);

        $this->assertTrue($result);
        $this->assertNotEmpty($invoice->getEmailSent());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $this->assertNotContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }

    /**
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture createOrderWithCustomer
     */
    public function should_send_customer_invoice_template()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $order = $this->objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $invoice = $this->objectManager->create(InvoiceInterface::class);
        $invoice->setOrder($order);

        /** @var InvoiceSender $invoiceSender */
        $invoiceSender = Bootstrap::getObjectManager()->create(InvoiceSender::class);

        $this->assertEmpty($invoice->getEmailSent());
        $result = $invoiceSender->send($invoice, true);

        $this->assertTrue($result);
        $this->assertNotEmpty($invoice->getEmailSent());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $this->assertContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }

    /**
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture createOrder
     */
    public function should_send_shadow_customer_guest_shipment_template()
    {
        self::markTestSkipped('Incomplete test. Shipment not properly created.');

        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $orderBefore = $this->objectManager->create(OrderInterface::class);
        $orderBefore->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $orderBefore->getId());
        $orderAfter = $this->objectManager->create(OrderInterface::class);
        $orderAfter->loadByIncrementId('100000001');
        $customer = $this->customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
        $this->assertEmpty($orderAfter->getEmailSent());

        $shipment = Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order\Shipment::class);
        $shipment->setOrder($orderAfter);

        $this->assertEmpty($shipment->getEmailSent());

        $orderSender = Bootstrap::getObjectManager()->create(ShipmentSender::class);
        $result = $orderSender->send($shipment, true);

        $this->assertTrue($result);

        $this->assertNotEmpty($shipment->getEmailSent());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $this->assertNotContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }

    /**
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture createOrderWithCustomer
     */
    public function should_send_customer_shipment_template()
    {
        self::markTestSkipped('Incomplete test. Shipment not properly created.');

        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $order = $this->objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $shipment = Bootstrap::getObjectManager()->create(\Magento\Sales\Model\Order\Shipment::class);
        $shipment->setOrder($order);

        $this->assertEmpty($shipment->getEmailSent());

        $orderSender = Bootstrap::getObjectManager()->create(ShipmentSender::class);
        $result = $orderSender->send($shipment, true);

        $this->assertTrue($result);

        $this->assertNotEmpty($shipment->getEmailSent());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $this->assertContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }

    /**
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture createOrder
     */
    public function should_send_shadow_customer_guest_creditmemo_template()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $orderBefore = $this->objectManager->create(OrderInterface::class);
        $orderBefore->loadByIncrementId('100000001');
        $this->convertGuestOrderToShadowCustomer->execute((int) $orderBefore->getId());
        $orderAfter = $this->objectManager->create(OrderInterface::class);
        $orderAfter->loadByIncrementId('100000001');
        $customer = $this->customerRepository->get('customer@null.com');
        $this->assertEquals('customer@null.com', $customer->getEmail());
        $this->assertEmpty($orderAfter->getEmailSent());

        $creditmemo = Bootstrap::getObjectManager()->create(CreditmemoInterface::class);
        $creditmemo->setOrder($orderAfter);
        $this->assertEmpty($creditmemo->getEmailSent());
        $creditmemoSender = $this->objectManager->create(CreditmemoSender::class);
        $result = $creditmemoSender->send($creditmemo, true);
        $this->assertTrue($result);
        $this->assertNotEmpty($creditmemo->getEmailSent());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $this->assertNotContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }

    /**
     * @todo Fix retrieving TransportInterface
     * @test
     * @magentoDataFixture createOrderWithCustomer
     */
    public function should_send_customer_creditmemo_template()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $order = $this->objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');

        $creditmemo = Bootstrap::getObjectManager()->create(CreditmemoInterface::class);
        $creditmemo->setOrder($order);

        $this->assertEmpty($creditmemo->getEmailSent());

        $creditmemoSender = $this->objectManager->create(CreditmemoSender::class);
        $result = $creditmemoSender->send($creditmemo, true);

        $this->assertTrue($result);
        $this->assertNotEmpty($creditmemo->getEmailSent());
        //        $transportInterface = $this->objectManager->get(TransportInterface::class);
        //        $this->assertContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }
}
