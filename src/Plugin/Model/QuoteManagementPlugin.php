<?php
 /**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
namespace Ho\GuestToShadowCustomer\Plugin\Model;

use Magento\Quote\Model\QuoteManagement;
use Magento\Quote\Model\Quote;

class QuoteManagementPlugin
{


    /**
     * Since a customer is necessary to create a billing agreement
     * This is done at this point because the customer ID is needed as 'shopperReference'
     * in the Adyen recurring contract notification
     *
     * @param \Magento\Quote\Model\QuoteManagement $subject
     * @param Quote $quote
     * @param array $orderData
     * @return array
     */
    public function beforeSubmit(QuoteManagement $subject, Quote $quote, $orderData = [])
    {

        return [$quote, $orderData];
    }
}