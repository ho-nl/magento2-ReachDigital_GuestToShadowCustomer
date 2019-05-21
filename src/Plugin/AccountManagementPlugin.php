<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

class AccountManagementPlugin
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    public function __construct(
        StoreManagerInterface $storeManager,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
    }

    /**
     * @param AccountManagement $subject
     * @param callable          $proceed
     * @param                   $customerEmail
     * @param null              $websiteId
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundIsEmailAvailable(
        AccountManagement $subject,
        callable $proceed,
        $customerEmail,
        $websiteId = null
    ) : bool {
        try {
            if ($websiteId === null) {
                $websiteId = $this->storeManager->getStore()->getWebsiteId();
            }

            $customer = $this->customerRepository->get($customerEmail, $websiteId);

            // Email is 'available' when found customer is shadow customer
            return (bool) $customer->getCustomAttribute('is_shadow')->getValue();
        } catch (NoSuchEntityException $e) {
            return true;
        }
    }
}
