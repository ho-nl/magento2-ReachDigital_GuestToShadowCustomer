<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Math\Random;
use Magento\Store\Model\StoreManagerInterface;

class AccountManagementInterfaceApiAroundPlugin
{
    /**
     * @var CustomerRepositoryInterface
     */
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

    /**
     * @var EmailNotificationInterface
     */
    private $emailNotification;

    /**
     * @var AccountConfirmation
     */
    private $accountConfirmation;

    public function __construct(
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        AddressRepositoryInterface $addressRepository,
        CustomerRegistry $customerRegistry,
        CustomerRepositoryInterface $customerRepository,
        EmailNotificationInterface $emailNotification,
        AccountConfirmation $accountConfirmation
    ) {
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->addressRepository = $addressRepository;
        $this->customerRegistry = $customerRegistry;
        $this->customerRepository = $customerRepository;
        $this->emailNotification = $emailNotification;
        $this->accountConfirmation = $accountConfirmation;
    }

    /**
     * @param AccountManagementInterface $accountManagement
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
        AccountManagementInterface $accountManagement,
        callable $proceed,
        CustomerInterface $customer,
        $hash,
        $redirectUrl = ''
    ) {
        if (!$customer->getId()) {
            $isShadow = $hash ? 0 : 1;
            $customer->setCustomAttribute('is_shadow', $isShadow);
            try {
                $existingCustomer = $this->customerRepository->get($customer->getEmail());
                if ($existingCustomer) {
                    if (!$existingCustomer->getCustomAttribute('is_shadow')->getValue()) {
                        throw new AlreadyExistsException(
                            __('A customer with the same email address already exists in an associated website.')
                        );
                    }
                    $customer->setId($existingCustomer->getId());
                    $customer->setDefaultShipping($existingCustomer->getDefaultShipping());
                    $customer->setDefaultBilling($existingCustomer->getDefaultBilling());
                }
            } catch (NoSuchEntityException $e) {
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
                        __('A customer with the same email already exists in an associated website.')
                    );
                } catch (LocalizedException $e) {
                    throw $e;
                }
                try {
                    foreach ($customerAddresses as $address) {
                        if(!$this->meetsMinimumRequirements($address)){
                            continue;
                        }
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
                $accountManagement->changeResetPasswordLinkToken($customer, $newLinkToken);

                if (!$isShadow) {
                    $this->emailNotification->newAccount(
                        $customer,
                        \Magento\Customer\Model\EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED,
                        '',
                        $customer->getStoreId()
                    );
                }

                return $customer;
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
            $websiteId = $this->storeManager->getStore($customer->getStoreId())->getWebsiteId();
            $customer->setWebsiteId($websiteId);

            // Update 'created_in' value with actual store name
            if ($customer->getId() === null) {
                $storeName = $this->storeManager->getStore($customer->getStoreId())->getName();
                $customer->setCreatedIn($storeName);
            }

            $customerAddresses = $customer->getAddresses() ?: [];
            $customer->setAddresses(null);

            if ($this->accountConfirmation->isConfirmationRequired($websiteId, $customer->getId(), $customer->getEmail())) {
                $customer->setConfirmation(1);
            }

            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer, $hash);

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
            $accountManagement->changeResetPasswordLinkToken($customer, $newLinkToken);

            if ($this->accountConfirmation->isConfirmationRequired($websiteId, $customer->getId(), $customer->getEmail())) {
                $accountManagement->resendConfirmation($customer->getEmail(), $websiteId, $redirectUrl);
            }

            if (!$isShadow) {
                $this->emailNotification->newAccount(
                    $customer,
                    \Magento\Customer\Model\EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED,
                    '',
                    $customer->getStoreId()
                );
            }

            return $customer;
        }
        return $proceed($customer, $hash, $redirectUrl);
    }


    /**
     * Core magento will throw an error if you try to save an address without these requirements
     * @param $address
     *
     * @return bool
     */
    private function meetsMinimumRequirements($address)
    {
        $test = null;
        return
        (
            $address->getFirstname() !== null &&
            $address->getLastname() !== null &&
            $address->getCity() !== null &&
            $address->getTelephone() !== null &&
            $address->getPostcode() !== null
        );
    }
}
