<?php
declare(strict_types=1);


namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Store\Model\StoreManagerInterface;

class DisallowPasswordResetForShadowCustomers
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
    )
    {
        $this->storeManager = $storeManager;
        $this->customerRepository = $customerRepository;
    }

    public function aroundInitiatePasswordReset(AccountManagement $accountManagement, \Closure $proceed, $email, $template, $websiteId = null) {

        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        $customer = $this->customerRepository->get($email, $websiteId);
        if ((int)$customer->getCustomAttribute('is_shadow')->getValue() === 1)  {
            // Shadow customers do not have an officially registered account and therefore they can not reset their password.
            return false;
        }
        return $proceed($email, $template, $websiteId);
    }
}
