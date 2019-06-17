<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Cron;

use Magento\Framework\Api\SearchCriteriaInterface;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use ReachDigital\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;

class ConvertGuestOrderToShadowCustomerCron
{
    /** @var  GuestOrderRepositoryInterface $guestOrderRepository */
    private $guestOrderRepository;

    /** @var ConvertGuestOrderToShadowCustomerInterface $convertGuestOrderToShadowCustomer */
    private $convertGuestOrderToShadowCustomer;

    /** @var  SearchCriteriaInterface $searchCriteria */
    private $searchCriteria;

    /**
     * @param GuestOrderRepositoryInterface              $guestOrderRepository
     * @param SearchCriteriaInterface                    $searchCriteria
     * @param ConvertGuestOrderToShadowCustomerInterface $convertGuestOrderToShadowCustomer
     */
    public function __construct(
        GuestOrderRepositoryInterface $guestOrderRepository,
        SearchCriteriaInterface $searchCriteria,
        ConvertGuestOrderToShadowCustomerInterface $convertGuestOrderToShadowCustomer
    ) {
        $this->guestOrderRepository = $guestOrderRepository;
        $this->searchCriteria = $searchCriteria;
        $this->convertGuestOrderToShadowCustomer = $convertGuestOrderToShadowCustomer;
    }

    /**
     * Loop through Guest Orders and Create Shadow(Customer) accounts.
     *
     * @return void
     */
    public function execute(): void
    {
        $orders = $this->guestOrderRepository->getList($this->searchCriteria);
        if ($orders->getTotalCount() > 0) {
            foreach ($orders->getItems() as $order) {
                try {
                    $this->convertGuestOrderToShadowCustomer->execute((int) $order->getId());
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }
}
