<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Api;

interface ConvertGuestOrderToShadowCustomerInterface
{

    /**
     * @param $orderId
     *
     * @throws EntityNotFoundException (something went wrong, do not catch, let the script crash)
     * @throws OrderAlreadyAssignedToShadowCustomerException (should catch in the cron, this is ok)
     * @throws OrderAlreadyAssignedToCustomerException (something went wrong, do not catch, let the script crash
     * @return void
     */
    public function execute($orderId);

}