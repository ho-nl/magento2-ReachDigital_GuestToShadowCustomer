<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Model;

use \Magento\Sales\Api\OrderCustomerManagementInterface;

class ConvertGuestOrderToShadowCustomer implements \Ho\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface
{
    protected $_orderCustomerManagement;

    public function __construct(OrderCustomerManagementInterface $orderCustomerManagement)
    {
        $this->_orderCustomerManagement = $orderCustomerManagement;
    }


    /**
     * @inheritdoc
     */
    public function execute($orderId)
    {
        $this->_orderCustomerManagement->create($orderId);
    }
}