<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Cron;

use Ho\GuestToShadowCustomer\Api\ConvertGuestOrderToShadowCustomerInterface;
use Ho\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ConvertGuestOrderToShadowCustomer
{

    /** @var  GuestOrderRepositoryInterface */
    protected $_guestOrderRepository;

    /** @var SearchCriteriaBuilder */
    protected $_searchCriteriaBuilder;


    public function __construct(GuestOrderRepositoryInterface $guestOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ConvertGuestOrderToShadowCustomerInterface $convertGuestOrderToShadowCustomer
    )
    {
        $this->_guestOrderRepository = $guestOrderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_convertGuestOrderToShadowCustomer = $convertGuestOrderToShadowCustomer;
    }


    public function execute()
    {
        $searchCriteria = $this->_searchCriteriaBuilder->create();
        $orders = $this->_guestOrderRepository->getList($searchCriteria);
        if ($orders->getTotalCount() > 0) {
            foreach ($orders as $order) {
                $this->_convertGuestOrderToShadowCustomer->execute($order->getId());
            }
        }

    }
}