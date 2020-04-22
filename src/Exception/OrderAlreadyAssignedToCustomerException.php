<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Exception;

use Magento\Framework\Exception\AlreadyExistsException;

class OrderAlreadyAssignedToCustomerException extends AlreadyExistsException
{
    public function __construct(\Exception $cause = null, $code = 0)
    {
        $msg = __('Order already assigned to customer: %1');
        parent::__construct($msg, $cause, $code);
    }
}
