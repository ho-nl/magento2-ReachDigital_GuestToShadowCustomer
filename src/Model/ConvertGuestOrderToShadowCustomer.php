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
use \Magento\Sales\Api\OrderCustomerManagementInterface;

class ConvertGuestOrderToShadowCustomer implements \Ho\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface
{
    protected $_orderCustomerManagement;

    protected $_orderRepository;

    private $_customerRepository;

    protected $_customerRegistry;

    public function __construct(OrderCustomerManagementInterface $orderCustomerManagement,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
CustomerRegistry $customerRegistry)
    {
        $this->_orderCustomerManagement = $orderCustomerManagement;
        $this->_orderRepository = $orderRepository;
        $this->_customerRepository = $customerRepository;
        $this->_customerRegistry = $customerRegistry;
    }


    /**
     * @inheritdoc
     */
    public function execute($orderId)
    {
        $order = $this->_orderRepository->get($orderId);

        if ($order->getCustomerId()) {
            if ($this->_customerRegistry->retrieveSecureData($order->getCustomerId())->getPasswordHash()) {
                throw new OrderAlreadyAssignedToCustomerException(__("Order already assigned to customer exception"));
            }
            throw new OrderAlreadyAssignedToShadowCustomerException(__("Order already assigned to shadow customer exception"));
        } else {
            $this->_orderCustomerManagement->create($orderId);
        }
    }
}