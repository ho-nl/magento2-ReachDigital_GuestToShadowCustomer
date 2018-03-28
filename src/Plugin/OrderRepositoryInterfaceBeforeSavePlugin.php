<?php
 /**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryInterfaceBeforeSavePlugin
{
    /** @var CustomerRepositoryInterface  */
    private $customerRepository;

    public function __construct(CustomerRepositoryInterface $customerRepository)
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderInterface           $order
     *
     * @return array
     */
    public function beforeSave(
        OrderRepositoryInterface $orderRepository,
        OrderInterface $order
    ) {

        try {
            $customer = $this->customerRepository->get($order->getCustomerEmail());
        } catch (NoSuchEntityException $exception) {
            return [$order];
        } catch (LocalizedException $exception) {
            return [$order];
        }
        $order->setIsShadow($customer->getCustomAttribute('is_shadow')->getValue());
        return [$order];
    }
}