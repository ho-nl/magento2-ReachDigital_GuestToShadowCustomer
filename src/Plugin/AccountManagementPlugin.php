<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
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
    private ScopeConfigInterface $scopeConfig;

    public function __construct(StoreManagerInterface $storeManager, CustomerRepositoryInterface $customerRepository, ScopeConfigInterface $scopeConfig)
    {
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
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
    ): bool {
        try {
            $guestLoginConfig = $this->scopeConfig->getValue(
                AccountManagement::GUEST_CHECKOUT_LOGIN_OPTION_SYS_CONFIG,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );

            if (!$guestLoginConfig) {
                return true;
            }

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
