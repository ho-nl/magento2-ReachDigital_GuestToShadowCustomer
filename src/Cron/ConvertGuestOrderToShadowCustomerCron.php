<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Cron;

use Magento\Framework\Api\SearchCriteriaInterface;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use ReachDigital\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;

class ConvertGuestOrderToShadowCustomerCron
{

    /** @var  GuestOrderRepositoryInterface */
    private $guestOrderRepository;

    /** @var ConvertGuestOrderToShadowCustomerInterface  */
    private $convertGuestOrderToShadowCustomer;

    /** @var  SearchCriteriaInterface */
    private $searchCriteria;

    public function __construct(GuestOrderRepositoryInterface $guestOrderRepository,
        SearchCriteriaInterface $searchCriteria,
        ConvertGuestOrderToShadowCustomerInterface $convertGuestOrderToShadowCustomer
    )
    {
        $this->guestOrderRepository = $guestOrderRepository;
        $this->searchCriteria = $searchCriteria;
        $this->convertGuestOrderToShadowCustomer = $convertGuestOrderToShadowCustomer;
    }

    /**
     * Loop through Guest Orders and Create Shadow(Customer) accounts.
     */
    public function execute()
    {
        /** @todo check if cron is skipped on 15 minutes interval. */
        $orders = $this->guestOrderRepository->getList($this->searchCriteria);
        if ($orders->getTotalCount() > 0) {
            foreach ($orders->getItems() as $order) {
                try {
                    $this->convertGuestOrderToShadowCustomer->execute($order->getId());
                } catch (\Exception $e) {

                }
            }
        }

    }
}