<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Model;

use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use ReachDigital\GuestToShadowCustomer\Api\GuestOrderRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class GuestOrderRepository implements GuestOrderRepositoryInterface
{
    /** @var OrderRepositoryInterface  */
    private $orderRepository;

    /** @var  FilterGroup */
    private $filterGroup;

    /** @var Filter  */
    private $filter;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param FilterGroup              $filterGroup
     * @param Filter                   $filter
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        FilterGroup $filterGroup,
        Filter $filter
    ) {
        $this->orderRepository = $orderRepository;
        $this->filterGroup = $filterGroup;
        $this->filter = $filter;
    }

    /**
     * @inheritdoc
     */
    public function getList(SearchCriteriaInterface $searchCriteria): OrderSearchResultInterface
    {
        $this->filter->setField('customer_id');
        $this->filter->setConditionType('null');
        $this->filterGroup->setFilters([$this->filter]);
        $searchCriteria->setFilterGroups([$this->filterGroup]);
        $searchCriteria->setPageSize(500);

        return $this->orderRepository->getList($searchCriteria);
    }
}
