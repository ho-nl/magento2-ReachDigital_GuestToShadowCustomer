<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Mollie\Api\Types\PaymentStatus;
use Mollie\Payment\GraphQL\Resolver\Checkout\ProcessTransaction;

class RestoreShadowQuoteToGuestQuoteForMollieGraphQl
{
    private CartRepositoryInterface $cartRepository;
    private CustomerInterfaceFactory $customerFactory;

    public function __construct(CartRepositoryInterface $cartRepository, CustomerInterfaceFactory $customerFactory)
    {
        $this->cartRepository = $cartRepository;
        $this->customerFactory = $customerFactory;
    }

    /**
     * Implement restoring quote logic for Mollie GraphQL implementation.
     * @see \ReachDigital\GuestToShadowCustomer\Observer\RestoreShadowQuoteToGuestQuote
     */
    public function afterResolve(ProcessTransaction $subject, array $result): array
    {
        if (
            !isset($result['paymentStatus']) ||
            strtolower($result['paymentStatus']) != PaymentStatus::STATUS_CANCELED ||
            !isset($result['cart']['model'])
        ) {
            return $result;
        }

        /** @var Quote $cart */
        $cart = $result['cart']['model'];

        if (!$cart->getCustomerEmail()) {
            return $result;
        }

        $email = $cart->getCustomerEmail();

        $cart->setCustomer($this->customerFactory->create());
        $cart->setCustomerId(0);
        $cart->setCustomerIsGuest(true);
        if ($cart->getBillingAddress()) {
            $cart->getBillingAddress()->setCustomerAddressId(null);
        }

        $cart->setCustomerEmail($email);

        foreach ($cart->getAllAddresses() as $address) {
            $address->setCustomerId(0);
            $address->setCustomerAddressId(0);
        }

        $this->cartRepository->save($cart);

        $result['cart']['model'] = $cart;

        return $result;
    }
}
