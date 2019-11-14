<?php
declare(strict_types=1);
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class PreserveIsShadowWhenSavingCustomer
{
    /** @var CustomerRepository */
    private $customerRepository;

    public function __construct(
        CustomerRepositoryInterface $customerRepository
    )
    {
        $this->customerRepository = $customerRepository;
    }

    /**
     * If newly provided customer object does not have custom attributes, copy from existing custom_attributes to
     * prevent them from being cleared unintentionally (i.e. through REST API).
     *
     * @param CustomerRepository $subject
     * @param CustomerInterface  $customer
     *
     * @return array
     */
    public function beforeSave(
        /** @noinspection PhpUnusedParameterInspection */
        CustomerRepository $subject,
        CustomerInterface $customer,
        $passwordHash = null
    ) : array
    {
        try {
            $loadedCustomer = $this->customerRepository->getById($customer->getId());

            if (empty($customer->getCustomAttributes()) && !empty($loadedCustomer->getCustomAttributes())) {
                $customer->setCustomAttributes($loadedCustomer->getCustomAttributes());
            }
        } catch (NoSuchEntityException $e) {
        } catch (LocalizedException $e) {
        }
        return [ $customer, $passwordHash ];
    }
}
