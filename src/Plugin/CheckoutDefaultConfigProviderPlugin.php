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

    public function afterGetConfig(DefaultConfigProvider $subject, $result)
    {
        $result['quoteData'] = $this->getQuoteData();
        return $result;
    }

    /**
     * Plugin created to get the correct quote ID (masked) when customer in quote is shadow customer
     *
     * @return array
     */
    public function getQuoteData()
    {
        $quoteData = [];
        if ($this->checkoutSession->getQuote()->getId()) {
            $quote = $this->quoteRepository->get($this->checkoutSession->getQuote()->getId());
            $quoteData = $quote->toArray();
            $quoteData['is_virtual'] = $quote->getIsVirtual();

            $customAttributes = $quote->getCustomer()->getCustomAttributes();
            if (!$quote->getCustomer()->getId()
                || !$this->customerSession->isLoggedIn()
                || (isset($customAttributes['is_shadow']) && $customAttributes['is_shadow']->getValue())
            ) {
                /** @var $quoteIdMask \Magento\Quote\Model\QuoteIdMask */
                $quoteIdMask = $this->quoteIdMaskFactory->create();
                $quoteData['entity_id'] = $quoteIdMask->load(
                    $this->checkoutSession->getQuote()->getId(),
                    'quote_id'
                )->getMaskedId();
            }
        }
        return $quoteData;
    }
}
