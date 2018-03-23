<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Framework\DataObject\Copy;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote;
use ReachDigital\GuestToShadowCustomer\Api\ConvertGuestQuoteToShadowCustomerInterface;

class ConvertGuestQuoteToShadowCustomer implements ConvertGuestQuoteToShadowCustomerInterface
{
    /**
     * @var Copy
     */
    private $objectCopyService;

    /**
     * @var AddressInterfaceFactory
     */
    private $addressFactory;

    /**
     * @var AccountManagementInterface
     */
    private $accountManagement;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    /**
     * @var RegionInterfaceFactory
     */
    private $regionFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @param Copy $objectCopyService
     * @param AddressInterfaceFactory $addressFactory
     * @param AccountManagementInterface $accountManagement
     * @param CustomerInterfaceFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param RegionInterfaceFactory $regionFactory
     */
    public function __construct(
        Copy $objectCopyService,
        AddressInterfaceFactory $addressFactory,
        AccountManagementInterface $accountManagement,
        CustomerInterfaceFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        RegionInterfaceFactory $regionFactory
    ) {
        $this->objectCopyService = $objectCopyService;
        $this->addressFactory = $addressFactory;
        $this->accountManagement = $accountManagement;
        $this->customerFactory = $customerFactory;
        $this->regionFactory = $regionFactory;
        $this->customerRepository = $customerRepository;
    }


    /**
     * @param CartInterface|Quote $quote
     *
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(CartInterface $quote)
    {
        try {
            $account = $this->customerRepository->get($quote->getCustomerEmail(), $quote->getStore()->getWebsiteId());
        } catch (NoSuchEntityException $exception) {
            $customerData = $this->objectCopyService->copyFieldsetToTarget(
                'order_address',
                'to_customer',
                $quote->getBillingAddress(),
                []
            );
            $addresses = $quote->getAllAddresses();
            foreach ($addresses as $address) {
                $addressData = $this->objectCopyService->copyFieldsetToTarget(
                    'order_address',
                    'to_customer_address',
                    $address,
                    []
                );
                /** @var \Magento\Customer\Api\Data\AddressInterface $customerAddress */
                $customerAddress = $this->addressFactory->create(['data' => $addressData]);
                switch ($address->getAddressType()) {
                    case Quote\Address::ADDRESS_TYPE_BILLING:
                        $customerAddress->setIsDefaultBilling(true);
                        break;
                    case Quote\Address::ADDRESS_TYPE_SHIPPING:
                        $customerAddress->setIsDefaultShipping(true);
                        break;
                }
                if (\is_string($address->getRegion())) {
                    /** @var \Magento\Customer\Api\Data\RegionInterface $region */
                    $region = $this->regionFactory->create();
                    $region->setRegion($address->getRegion());
                    $region->setRegionCode($address->getRegionCode());
                    $region->setRegionId($address->getRegionId());
                    $customerAddress->setRegion($region);
                }
                $customerData['addresses'][] = $customerAddress;
            }
            /** @var \Magento\Customer\Api\Data\CustomerInterface $customer */
            $customer = $this->customerFactory->create(['data' => $customerData]);
            $account = $this->accountManagement->createAccount($customer);
        }
        $quote->setCustomer($account);
        $quote->setCustomerId($account->getId());
        $quote->setCustomerIsGuest(true);
    }

}