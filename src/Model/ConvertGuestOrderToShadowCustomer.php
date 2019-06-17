<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use ReachDigital\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToCustomerException;
use ReachDigital\GuestToShadowCustomer\Exception\OrderAlreadyAssignedToShadowCustomerException;

class ConvertGuestOrderToShadowCustomer implements ConvertGuestOrderToShadowCustomerInterface
{
    /** @var OrderCustomerManagementInterface $orderCustomerManagement */
    private $orderCustomerManagement;

    /** @var OrderRepositoryInterface $orderRepository */
    private $orderRepository;

    /** @var CustomerRegistry $customerRegistry */
    private $customerRegistry;

    /** @var CustomerRepositoryInterface $customerRepository */
    private $customerRepository;

    /**
     * @param OrderCustomerManagementInterface $orderCustomerManagement
     * @param OrderRepositoryInterface         $orderRepository
     * @param CustomerRepositoryInterface      $customerRepository
     * @param CustomerRegistry                 $customerRegistry
     */
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
     */
    public function execute(int $orderId): void
    {
        $order = $this->orderRepository->get($orderId);

        if ($order->getCustomerId()) {
            $hash = $this->customerRegistry->retrieveSecureData($order->getCustomerId())->getPasswordHash();
            if ($hash) {
                throw new OrderAlreadyAssignedToCustomerException();
            }
            throw new OrderAlreadyAssignedToShadowCustomerException();
        }

        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail());
        } catch (NoSuchEntityException $exception) {
            $customer = $this->orderCustomerManagement->create($orderId);
        }

        $order->setCustomerId($customer->getId());
        $order->setCustomerIsGuest(0);
        $order->setCustomerFirstname($customer->getFirstname());
        $order->setCustomerMiddlename($customer->getMiddlename());
        $order->setCustomerLastname($customer->getLastname());

        $this->orderRepository->save($order);
    }
}
