<?php
 /**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
namespace Ho\GuestToShadowCustomer\Plugin;

use Magento\Customer\Model\CustomerRegistry;

class EmailNotificationInterfacePlugin
{
    /** @var  CustomerRegistry */
    protected $_customerRegistry;

    public function __construct(CustomerRegistry $customerRegistry)
    {
        $this->_customerRegistry = $customerRegistry;
    }


    /**
     * @param \Magento\Customer\Model\EmailNotificationInterface $subject
     * @param \Closure                                           $proceed
     * @param \Magento\Customer\Api\Data\CustomerInterface       $customer
     * @param                                                    $type
     * @param string                                             $backUrl
     * @param int                                                $storeId
     * @param null                                               $sendemailStoreId
     *
     * @return mixed|void
     */
    public function aroundNewAccount(
        \Magento\Customer\Model\EmailNotificationInterface $subject,
        \Closure $proceed,
        \Magento\Customer\Api\Data\CustomerInterface $customer,
        $type = self::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        if (!$this->_customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash()) {
            return;
        }
        return $proceed($customer, $type, $backUrl, $storeId, $sendemailStoreId);
    }
}