<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use MultiSafepay\ConnectCore\Util\PaymentMethodUtil;


class RestoreShadowQuoteToGuestQuoteForMspGraphQl
{
    private CartRepositoryInterface $cartRepository;
    private CustomerInterfaceFactory $customerFactory;
    private MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId;
    private PaymentMethodUtil $paymentMethodUtil;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CustomerInterfaceFactory $customerFactory,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        PaymentMethodUtil $paymentMethodUtil
    ) {
        $this->cartRepository = $cartRepository;
        $this->customerFactory = $customerFactory;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
        $this->paymentMethodUtil = $paymentMethodUtil;
    }

    /**
     * Implement restoring quote logic for Msp GraphQL implementation, similar to RestoreShadowQuoteToGuestQuoteForMollieGraphQl
     *
     * @see \ReachDigital\GuestToShadowCustomer\Plugin\RestoreShadowQuoteToGuestQuoteForMollieGraphQl::afterResolve
     */
    public function afterResolve(
        \MultiSafepay\ConnectGraphQl\Model\Resolver\RestoreQuote $subject,
        string $result,
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null,
    ): string
    {
        $maskedCartId = $args['input']['cart_id'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        /** @var Quote $cart */
        $cart = $this->getQuoteByHash($maskedCartId, $context->getUserId(), $storeId);

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
        if ($cart->getShippingAddress()) {
            $cart->getShippingAddress()->setCustomerAddressId(null);
        }

        $cart->setCustomerEmail($email);

        foreach ($cart->getAllAddresses() as $address) {
            $address->setCustomerId(0);
            $address->setCustomerAddressId(0);
        }

        $cart->save();

        return $result;
    }

    /**
     * @param string $cartHash
     * @param int|null $customerId
     * @param int $storeId
     * @return CartInterface
     * @throws GraphQlAuthorizationException
     * @throws GraphQlNoSuchEntityException
     */
    private function getQuoteByHash(string $cartHash, ?int $customerId, int $storeId): CartInterface
    {
        try {
            $cartId = $this->maskedQuoteIdToQuoteId->execute($cartHash);

            /** @var Quote $cart */
            $cart = $this->cartRepository->get($cartId);
        } catch (NoSuchEntityException $exception) {
            throw new GraphQlNoSuchEntityException(
                __('Could not find a cart with ID "%1"', $cartHash)
            );
        }

        if (!$this->paymentMethodUtil->isMultisafepayCart($cart)) {
            throw new GraphQlNoSuchEntityException(
                __('This cart "%1" is not using a MultiSafepay payment method', $cartHash)
            );
        }

        $cartCustomerId = (int)$cart->getCustomerId();

        if ($cartCustomerId === 0 && (null === $customerId || 0 === $customerId)) {
            return $cart;
        }

        if ($cartCustomerId !== $customerId) {
            throw new GraphQlAuthorizationException(
                __('The current user cannot perform operations on cart "%1"', $cartHash)
            );
        }

        if ((int)$cart->getStoreId() !== $storeId) {
            throw new GraphQlNoSuchEntityException(
                __('Wrong store code specified for cart "%1"', $cartHash)
            );
        }

        return $cart;
    }
}
