<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Model;

use Ho\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GuestOrderRepository implements GuestOrderRepositoryInterface
{
    CONST SALES_ORDER_GUEST_COLUMN_NAME = 'customer_is_guest';

    protected $_orderRepository;

    protected $_searchCriteriaBuilder;


    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
    }


    /**
     * @inheritdoc
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    ) {
        $searchCriteria = $this->_searchCriteriaBuilder->addFilter(
            self::SALES_ORDER_GUEST_COLUMN_NAME,
            1
        )->create();
        $searchResult   = $this->_orderRepository->getList($searchCriteria);
        return $searchResult;
    }
}