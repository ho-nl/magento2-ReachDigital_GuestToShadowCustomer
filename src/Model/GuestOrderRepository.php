<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Model;

use ReachDigital\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GuestOrderRepository implements GuestOrderRepositoryInterface
{
    private $orderRepository;

    private $searchCriteriaBuilder;


    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    /**
     * @inheritdoc
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ) {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            OrderInterface::CUSTOMER_IS_GUEST,
            1
        )->create();
        $searchResult = $this->orderRepository->getList($searchCriteria);
        return $searchResult;
    }
}