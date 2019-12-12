<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Api;

use Magento\Quote\Api\Data\CartInterface;

interface ConvertGuestQuoteToShadowCustomerInterface
{
    /**
     * @param $quote
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return void
     */
    public function execute(CartInterface $quote);
}
