<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface GuestOrderRepositoryInterface
{

    /**
     * Get GuestOrders list, defines a GuestOrder if customer password hash is null
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Sales\Api\Data\OrderSearchResultInterface
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );
}
