<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Model;

use ReachDigital\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToCustomerException;
use ReachDigital\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToShadowCustomerException;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class ConvertGuestOrderToShadowCustomer
    implements ConvertGuestOrderToShadowCustomerInterface
{
    private $orderCustomerManagement;

    private $orderRepository;

    private $customerRegistry;


    public function __construct(
        OrderCustomerManagementInterface $orderCustomerManagement,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerRegistry $customerRegistry
    ) {
        $this->orderCustomerManagement = $orderCustomerManagement;
        $this->orderRepository = $orderRepository;
        $this->customerRegistry = $customerRegistry;
    }


    /**
     * @inheritdoc
     */
    public function execute($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        if ($order->getCustomerId()) {
            /** @todo verplaats de IF logica in een variable. $hash = $this->customerRegistry
                            ->retrieveSecureData($order->getCustomerId())
                            ->getPasswordHash() */
            if ($this->customerRegistry
                ->retrieveSecureData($order->getCustomerId())
                ->getPasswordHash()) {
                throw new OrderAlreadyAssignedToCustomerException();
            }
            // @todo parameter verwijderen
            throw new OrderAlreadyAssignedToShadowCustomerException(__("Order already assigned to shadow customer exception"));
        }

        $this->orderCustomerManagement->create($orderId);
    }
}