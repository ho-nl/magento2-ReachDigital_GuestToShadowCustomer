<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Api;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use ReachDigital\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToCustomerException;

interface ConvertGuestOrderToShadowCustomerInterface
{
    /**
     * @param int $orderId
     *
     * @throws NoSuchEntityException
     * @throws OrderAlreadyAssignedToCustomerException
     * @throws LocalizedException
     *
     * @return void
     */
    public function execute(int $orderId): void;
}
