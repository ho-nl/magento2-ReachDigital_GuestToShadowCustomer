<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Exception;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Phrase;

class OrderAlreadyAssignedToShadowCustomerException extends AlreadyExistsException
{
    /**
     * @param Phrase $phrase
     * @param \Exception $cause
     * @param int $code
     * @since 100.2.0
     */
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if ($phrase === null) {
            $phrase = new Phrase('Order already assigned to shadow customer.');
        }
        parent::__construct($phrase, $cause, $code);
    }
}