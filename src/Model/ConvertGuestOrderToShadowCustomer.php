<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
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

    private $customerRepository;

    public function __construct(
        OrderCustomerManagementInterface $orderCustomerManagement,
        OrderRepositoryInterface $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerRegistry $customerRegistry
    ) {
        $this->orderCustomerManagement = $orderCustomerManagement;
        $this->orderRepository = $orderRepository;
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @inheritdoc
     * @throws LocalizedException
     */
    public function execute($orderId)
    {
        $order = $this->orderRepository->get($orderId);

        if ($order->getCustomerId()) {
            $hash = $this->customerRegistry
                            ->retrieveSecureData($order->getCustomerId())
                            ->getPasswordHash();
            if ($hash) {
                throw new OrderAlreadyAssignedToCustomerException();
            }
            throw new OrderAlreadyAssignedToShadowCustomerException();
        }
        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail());
            $order->setCustomerId($customer->getId());
            $this->orderRepository->save($order);
        } catch (NoSuchEntityException $exception) {
            $this->orderCustomerManagement->create($orderId);
        }

    }
}