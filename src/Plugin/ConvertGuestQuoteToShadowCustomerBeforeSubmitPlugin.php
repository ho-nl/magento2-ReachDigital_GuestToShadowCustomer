<?php
 /**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\Quote;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestQuoteToShadowCustomerInterface;

class ConvertGuestQuoteToShadowCustomerBeforeSubmitPlugin
{
    private $convertGuestQuoteToShadowCustomer;

    public function __construct(ConvertGuestQuoteToShadowCustomerInterface $convertGuestQuoteToShadowCustomer)
    {
        $this->convertGuestQuoteToShadowCustomer = $convertGuestQuoteToShadowCustomer;
    }


    /**
     * Since a customer is necessary to create a billing agreement
     * This is done at this point because the customer ID is needed as 'shopperReference'
     * in the Adyen recurring contract notification
     *
     * @inheritdoc
     */
    public function beforeSubmit(QuoteManagement $subject, Quote $quote, $orderData = [])
    {
        $this->convertGuestQuoteToShadowCustomer->execute($quote);

        $quote->getShippingAddress()->setCustomerId($quote->getCustomer()->getId());
        $quote->getBillingAddress()->setCustomerId($quote->getCustomer()->getId());

        return [$quote, $orderData];
    }
}