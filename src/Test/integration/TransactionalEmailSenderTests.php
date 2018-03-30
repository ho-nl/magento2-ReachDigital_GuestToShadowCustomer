<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use Magento\Framework\App\Area;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Mail\TransportInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Email\Sender\OrderSender;
use PHPUnit\Framework\TestCase;
use Magento\TestFramework\Helper\Bootstrap;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;

class TransactionalEmailSenderTests extends TestCase
{
    /** @var ObjectManager */
    private $objectManager;

    /** @var ConvertGuestOrderToShadowCustomerInterface */
    private $converter;

    protected function setUp()
    {
        parent::setUp();
        $this->objectManager = Bootstrap::getObjectManager();
        $this->converter = $this->objectManager->create(
            ConvertGuestOrderToShadowCustomerInterface::class
        );
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     */
    public function testSendNewShadowCustomerOrderEmail()
    {
        Bootstrap::getInstance()->loadArea(Area::AREA_FRONTEND);
        $order = $this->objectManager->create(OrderInterface::class);
        $order->loadByIncrementId('100000001');
        $order->setCustomerEmail('customer@example.com');
        $this->converter->execute($order->getId());
        $this->assertEmpty($order->getEmailSent());
        $orderSender = Bootstrap::getObjectManager()->create(OrderSender::class);
        $result = $orderSender->send($order);
        $this->assertTrue($result);
        $this->assertNotEmpty($order->getEmailSent());
        $transportInterface = $this->objectManager->get(TransportInterface::class);
        $this->assertNotContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order_with_customer.php
     */
    public function testSendNewCustomerOrderEmail()
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
        $transportInterface = $this->objectManager->get(TransportInterface::class);
        $this->assertContains('logging into your account', $transportInterface->getMessage()->getBody()->getContent());
    }
}