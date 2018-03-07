<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Cron;

use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use ReachDigital\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ConvertGuestOrderToShadowCustomerCron
{

    /** @var  GuestOrderRepositoryInterface */
    private $guestOrderRepository;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var ConvertGuestOrderToShadowCustomerInterface  */
    private $convertGuestOrderToShadowCustomer;


    public function __construct(GuestOrderRepositoryInterface $guestOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConvertGuestOrderToShadowCustomerInterface $convertGuestOrderToShadowCustomer
    )
    {
        $this->guestOrderRepository = $guestOrderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->convertGuestOrderToShadowCustomer = $convertGuestOrderToShadowCustomer;
    }


    /**
     *
     */
    public function execute()
    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $orders = $this->guestOrderRepository->getList($searchCriteria);
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