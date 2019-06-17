<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;

interface GuestOrderRepositoryInterface
{
    /**
     * Get GuestOrders list, defines a GuestOrder if customer password hash is null
     *
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return OrderSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria): OrderSearchResultInterface;
}
