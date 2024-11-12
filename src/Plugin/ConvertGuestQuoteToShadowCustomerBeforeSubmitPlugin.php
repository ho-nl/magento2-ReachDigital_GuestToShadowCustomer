<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\ScopeInterface;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestQuoteToShadowCustomerInterface;

class ConvertGuestQuoteToShadowCustomerBeforeSubmitPlugin
{
    /** @var ScopeConfigInterface $scopeConfig */
    private $scopeConfig;

    /** @var ConvertGuestQuoteToShadowCustomerInterface $convertGuestQuoteToShadowCustomer */
    private $convertGuestQuoteToShadowCustomer;

    /**
     * @param ScopeConfigInterface                       $scopeConfig
     * @param ConvertGuestQuoteToShadowCustomerInterface $convertGuestQuoteToShadowCustomer
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ConvertGuestQuoteToShadowCustomerInterface $convertGuestQuoteToShadowCustomer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->convertGuestQuoteToShadowCustomer = $convertGuestQuoteToShadowCustomer;
    }

    /**
     * Since a customer is necessary to create a billing agreement
     * This is done at this point because the customer ID is needed as 'shopperReference'
     * in the Adyen recurring contract notification
     *
     * @throws NoSuchEntityException
     */
    public function beforeSubmit(QuoteManagement $subject, Quote $quote, $orderData = []): array
    {
        $enabled = $this->scopeConfig->isSetFlag(
            'guest_to_shadow_customer/general/enabled',
            ScopeInterface::SCOPE_STORES,
            $quote->getStore()
        );

        if ($enabled === true && $quote->getCustomerIsGuest()) {
            $this->convertGuestQuoteToShadowCustomer->execute($quote);

            $quote->getShippingAddress()->setCustomerId($quote->getCustomer()->getId());
            $quote->getBillingAddress()->setCustomerId($quote->getCustomer()->getId());
        }

        return [$quote, $orderData];
    }
}
