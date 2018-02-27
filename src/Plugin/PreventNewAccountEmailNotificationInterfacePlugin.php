<?php
 /**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

namespace Ho\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\EmailNotificationInterface;

/** @todo overal naam aanpassen  */
class PreventNewAccountEmailNotificationInterfacePlugin
{
    /** @todo alle properties worden private */
    /** @var  CustomerRegistry */
    protected $_customerRegistry;

    public function __construct(CustomerRegistry $customerRegistry)
    {
        /** @todo alle variables aanpassen naar format zonder underscore */
        $this->_customerRegistry = $customerRegistry;
    }


    /**
     * @param EmailNotificationInterface $subject
     * @param \Closure                                           $proceed
     * @param CustomerInterface       $customer
     * @param                                                    $type
     * @param string                                             $backUrl
     * @param int                                                $storeId
     * @param null                                               $sendemailStoreId
     *
     * @return mixed|void
     */
    public function aroundNewAccount(
        EmailNotificationInterface $subject,
        \Closure $proceed,
        CustomerInterface $customer,
        $type = EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED,
        $backUrl = '',
        $storeId = 0,
        $sendemailStoreId = null
    ) {
        // @todo noSuchEntity afhandelen, exception test toevoegen.
        if (!$this->_customerRegistry->retrieveSecureData($customer->getId())->getPasswordHash()) {
            return;
        }
        return $proceed($customer, $type, $backUrl, $storeId, $sendemailStoreId);
    }
}