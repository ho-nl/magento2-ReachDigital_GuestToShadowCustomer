<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */
namespace ReachDigital\GuestToShadowCustomer\Plugin\Block\Adminhtml\Order\View;

class InfoPlugin
{
    public function afterGetCustomerViewUrl(\Magento\Sales\Block\Adminhtml\Order\View\Info $subject, $result)
    {
        // ShadowCustomer sets the customerId on the order
        if ($subject->getOrder()->getCustomerId()) {
            return $subject->getUrl('customer/index/edit', ['id' => $subject->getOrder()->getCustomerId()]);
        }

        return $result;
    }
}
