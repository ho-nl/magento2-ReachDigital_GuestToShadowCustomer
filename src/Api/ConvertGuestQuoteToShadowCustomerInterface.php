<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Api;

interface ConvertGuestQuoteToShadowCustomerInterface
{

    /**
     * @param $quote
     *
     * @return void
     */
    public function execute($quote);

}