<?php
 /**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */
namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;

class AccountManagementInterfaceApiAroundPlugin
{

    private $customerRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var Random
     */
    private $mathRandom;

    public function __construct(
            StoreManagerInterface $storeManager,
            Random $mathRandom,
            AddressRepositoryInterface $addressRepository,
            CustomerRegistry $customerRegistry,
            CustomerRepositoryInterface $customerRepository
        ) {
            $this->storeManager = $storeManager;
            $this->mathRandom = $mathRandom;
            $this->addressRepository = $addressRepository;
            $this->customerRegistry = $customerRegistry;
            $this->customerRepository = $customerRepository;
        }

    /**
     * @param AccountManagementInterface $subject
     * @param callable                   $proceed
     * @param CustomerInterface          $customer
     * @param                            $hash
     * @param string                     $redirectUrl
     *
     * @return CustomerInterface
     * @throws InputException
     * @throws \Exception
     */
    public function aroundCreateAccountWithPasswordHash(
        AccountManagementInterface $subject,
        callable $proceed,
        CustomerInterface $customer,
        $hash,
        $redirectUrl = ''
    ) {
        if (!$customer->getCustomAttribute('is_shadow')) {
            return $proceed($customer, $hash, $redirectUrl);
        }
        // This logic allows an existing customer to be added to a different store.  No new account is created.
        // The plan is to move this logic into a new method called something like 'registerAccountWithStore'
        if ($customer->getId()) {
            $customer = $this->customerRepository->get($customer->getEmail());
            $websiteId = $customer->getWebsiteId();

            if ($this->isCustomerInStore($websiteId, $customer->getStoreId())) {
                throw new InputException(__('This customer already exists in this store.'));
            }
            // Existing password hash will be used from secured customer data registry when saving customer
        }

        // Make sure we have a storeId to associate this customer with.
        if (!$customer->getStoreId()) {
            if ($customer->getWebsiteId()) {
                $storeId = $this->storeManager->getWebsite($customer->getWebsiteId())->getDefaultStore()->getId();
            } else {
                $storeId = $this->storeManager->getStore()->getId();
            }
            $customer->setStoreId($storeId);
        }

        // Associate website_id with customer
        if (!$customer->getWebsiteId()) {
            $websiteId = $this->storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);
        }

        // Update 'created_in' value with actual store name
        if ($customer->getId() === null) {
            $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
            $customer->setCreatedIn($storeName);
        }

        $customerAddresses = $customer->getAddresses() ?: [];
        $customer->setAddresses(null);
        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer, $hash);
        } catch (AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email already exists in an associated website.!!!')
            );
        } catch (LocalizedException $e) {
            throw $e;
        }
        try {
            foreach ($customerAddresses as $address) {
                if ($address->getId()) {
                    $newAddress = clone $address;
                    $newAddress->setId(null);
                    $newAddress->setCustomerId($customer->getId());
                    $this->addressRepository->save($newAddress);
                } else {
                    $address->setCustomerId($customer->getId());
                    $this->addressRepository->save($address);
                }
            }
            $this->customerRegistry->remove($customer->getId());
        } catch (InputException $e) {
            $this->customerRepository->delete($customer);
            throw $e;
        }
        $customer = $this->customerRepository->getById($customer->getId());
        $newLinkToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newLinkToken);
        $this->sendEmailConfirmation($customer, $redirectUrl);

        return $customer;
    }
}