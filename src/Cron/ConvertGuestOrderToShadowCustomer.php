<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Cron;

use Ho\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;

class ConvertGuestOrderToShadowCustomer
{

    /** @var  GuestOrderRepositoryInterface */
    protected $_guestOrderRepository;


    public function __construct(GuestOrderRepositoryInterface $guestOrderRepository)
    {

        $this->_guestOrderRepository = $this->_objectManager->create(
            GuestOrderRepositoryInterface::class
        );
    }


    public function execute()
    {

    }
}