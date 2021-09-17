<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

class RestoreQuoteTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Checkout\Model\Session $checkoutSession */
    private $checkoutSession;

    /** @var \Magento\Customer\Model\Session $customerSession */
    private $customerSession;

    /** @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository */
    private $customerRepository;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->checkoutSession = $objectManager->create(\Magento\Checkout\Model\Session::class);
        $this->customerSession = $objectManager->create(\Magento\Customer\Model\Session::class);
        $this->customerRepository = $objectManager->create(\Magento\Customer\Api\CustomerRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_simple_product.php
     */
    public function testLoggedInCustomer()
    {
        $this->customerSession->loginById(1);
        $this->checkoutSession->restoreQuote();
        $quote = $this->checkoutSession->getQuote();

        self::assertSame(1, (int) $quote->getCustomerId());
        self::assertFalse($quote->getCustomerIsGuest());
        self::assertSame(1, $quote->getBillingAddress()->getCustomerAddressId());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture ../../../../vendor/reach-digital/magento2-guesttoshadowcustomer/src/Test/Integration/_files/order_by_guest.php
     */
    public function testGuest()
    {
        $this->checkoutSession->restoreQuote();
        $quote = $this->checkoutSession->getQuote();

        self::assertSame(0, $quote->getCustomerId());
        self::assertTrue($quote->getCustomerIsGuest());
        self::assertNull($quote->getBillingAddress()->getCustomerAddressId());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Sales/_files/customer_order_with_simple_product.php
     */
    public function testGuestWithAccount()
    {
        $this->checkoutSession->restoreQuote();
        $quote = $this->checkoutSession->getQuote();

        self::assertSame(0, $quote->getCustomerId());
        self::assertTrue($quote->getCustomerIsGuest());
        self::assertNull($quote->getBillingAddress()->getCustomerAddressId());
    }
}
