<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Model;

use Ho\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToCustomerException;
use Ho\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToShadowCustomerException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Ho\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class ConvertGuestOrderToShadowCustomer
    implements ConvertGuestOrderToShadowCustomerInterface
{
    protected $_orderCustomerManagement;

    protected $_orderRepository;

    protected $_customerRegistry;


    public function __construct(
        OrderCustomerManagementInterface $orderCustomerManagement,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerRegistry $customerRegistry
    ) {
        $this->_orderCustomerManagement = $orderCustomerManagement;
        $this->_orderRepository = $orderRepository;
        $this->_customerRegistry = $customerRegistry;
    }


    /**
     * @inheritdoc
     */
    public function execute($orderId)
    {
        $order = $this->_orderRepository->get($orderId);

        if ($order->getCustomerId()) {
            /** @todo verplaats de IF logica in een variable. $hash = $this->_customerRegistry
                            ->retrieveSecureData($order->getCustomerId())
                            ->getPasswordHash() */
            if ($this->_customerRegistry
                ->retrieveSecureData($order->getCustomerId())
                ->getPasswordHash()) {
                throw new OrderAlreadyAssignedToCustomerException();
            }
            // @todo parameter verwijderen
            throw new OrderAlreadyAssignedToShadowCustomerException(__("Order already assigned to shadow customer exception"));
        }

        $this->_orderCustomerManagement->create($orderId);
    }
}