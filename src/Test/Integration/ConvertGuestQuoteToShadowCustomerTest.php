<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Test\Integration;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Model\OrderRepository;
use Magento\TestFramework\Helper\Bootstrap;
use ReachDigital\GuestToShadowCustomer\Model\ConvertGuestQuoteToShadowCustomer;

class ConvertGuestQuoteToShadowCustomerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    private $convertGuestQuoteToShadowCustomer;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    protected function setup(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->convertGuestQuoteToShadowCustomer = $this->objectManager->get(ConvertGuestQuoteToShadowCustomer::class);
        $this->cartRepository = $this->objectManager->get(CartRepositoryInterface::class);
    }

    /**
     * @test
     *
     * @magentoDataFixture ../../../../vendor/reach-digital/magento2-guesttoshadowcustomer/src/Test/Integration/_files/order_by_guest.php
     */
    public function should_convert_guest_quote_to_order_with_shadow_customer(): void
    {
        /** @var CustomerRepositoryInterface $customerRepository */
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $orderRepository = $this->objectManager->create(OrderRepository::class);
        $searchCriteriaInterface = $this->objectManager->create(SearchCriteriaInterface::class);
        $order = $orderRepository->getList($searchCriteriaInterface)->getItems();
        $customer = $customerRepository->getList($searchCriteriaInterface)->getItems();

        self::assertNotEmpty($customer);
        self::assertNotEmpty($order);
    }

    /**
     * @test
     * @magentoDataFixture Magento/Checkout/_files/quote_with_virtual_product_and_address.php
     */
    public function should_convert_guest_with_virtual_quote_to_order_with_shadow_customer(): void
    {
        $customerRepository = $this->objectManager->create(CustomerRepositoryInterface::class);
        $searchCriteriaInterface = $this->objectManager->create(SearchCriteriaInterface::class);
        $quote = $this->cartRepository->getList($searchCriteriaInterface)->getItems();
        $this->convertGuestQuoteToShadowCustomer->execute(reset($quote));
        $customer = $customerRepository->get('customer@example.com');

        self::assertEquals('customer@example.com', $customer->getEmail());
    }
}
