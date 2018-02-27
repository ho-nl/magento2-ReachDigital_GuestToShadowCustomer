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
     * @throws \Magento\Framework\Exception\NoSuchEntityException (something went wrong, do not catch, let the script crash)
     * @throws \Magento\Framework\Exception\AlreadyExistsException (something went wrong, do not catch, let the script crash)
     * @throws \Ho\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToShadowCustomerException (should catch in the cron, this is ok)
     * @throws \Ho\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToCustomerException (something went wrong, do not catch, let the script crash
     * @return void
     */
    public function execute($orderId);

}