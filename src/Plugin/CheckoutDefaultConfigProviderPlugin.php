<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Checkout\Model\DefaultConfigProvider;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Customer\Model\Session as CustomerSession;

class CheckoutDefaultConfigProviderPlugin
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        CheckoutSession $session,
        CartRepositoryInterface $quoteRepository,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CustomerSession $customerSession
    ) {
        $this->checkoutSession = $session;
        $this->quoteRepository = $quoteRepository;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->customerSession = $customerSession;
    }

    /**
     * For shadow customers and non-logged-in guests, replace the quote entity_id with the masked ID
     * so the checkout JS uses the correct guest cart API endpoint.
     *
     * Only patches entity_id — does not replace quoteData entirely, preserving extension_attributes
     * populated by other modules (e.g. SendCloud, Mollie).
     */
    public function afterGetConfig(DefaultConfigProvider $subject, array $result): array
    {
        if (!$this->checkoutSession->getQuote()->getId()) {
            return $result;
        }

        $quote = $this->quoteRepository->get($this->checkoutSession->getQuote()->getId());
        $customAttributes = $quote->getCustomer()->getCustomAttributes();

        if (
            !$quote->getCustomer()->getId() ||
            !$this->customerSession->isLoggedIn() ||
            (isset($customAttributes['is_shadow']) && $customAttributes['is_shadow']->getValue())
        ) {
            /** @var \Magento\Quote\Model\QuoteIdMask $quoteIdMask */
            $quoteIdMask = $this->quoteIdMaskFactory->create();
            $result['quoteData']['entity_id'] = $quoteIdMask
                ->load($this->checkoutSession->getQuote()->getId(), 'quote_id')
                ->getMaskedId();
        }

        return $result;
    }
}
