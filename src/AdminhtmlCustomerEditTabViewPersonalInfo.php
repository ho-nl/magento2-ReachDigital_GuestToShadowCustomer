<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer;

use Magento\Customer\Block\Adminhtml\Edit\Tab\View\PersonalInfo;

class AdminhtmlCustomerEditTabViewPersonalInfo extends PersonalInfo
{

    /**
     * Check if Customer is a Shadow Customer
     */
    public function getIsShadowCustomer()
    {
        return $this->getCustomer()->getCustomAttribute('is_shadow')->getValue() ? __('Yes') : __('No');
    }
}