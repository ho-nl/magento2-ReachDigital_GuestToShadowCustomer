<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
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
    public function afterResolve(
        ProcessTransaction $subject,
        array $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null,
    ): array {
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

        if ($context->getExtensionAttributes()->getIsCustomer() === true) {
            // Keep customer logged in
            return $result;
        }

        // Shadow customer created, remove logged in data from quote to make it accessible again for guest customer
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

        $cart->save();

        $result['cart']['model'] = $cart;

        return $result;
    }
}
