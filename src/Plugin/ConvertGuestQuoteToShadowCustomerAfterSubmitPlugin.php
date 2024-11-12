<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Plugin;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteManagement;

class ConvertGuestQuoteToShadowCustomerAfterSubmitPlugin
{
    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var CustomerSession */
    private $customerSession;

    /** @var CustomerInterfaceFactory $customerFactory */
    private $customerFactory;

    /**
     * @param CartRepositoryInterface $cartRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerSession $customerSession
     * @param CustomerInterfaceFactory $customerFactory
     */
    public function __construct(
        CartRepositoryInterface     $cartRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession             $customerSession,
        CustomerInterfaceFactory    $customerFactory
    )
    {
        $this->cartRepository = $cartRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
    }

    public function afterSubmit(QuoteManagement $subject, $result, Quote $quote)
    {
        if (!$quote->getCustomerEmail()) {
            return $result;
        }

        if ($this->customerSession->isLoggedIn() === false) {
            $guestEmail = $quote->getCustomerEmail();
            // Must set to empty customer, else it will override customer_id field,
            // see \Magento\Quote\Model\Quote::beforeSave
            $quote->setCustomer($this->customerFactory->create());
            $quote->setCustomerId(0);
            $quote->setCustomerIsGuest(true);

            if ($quote->getBillingAddress()) {
                $quote->getBillingAddress()->setCustomerAddressId(null);
            }

            $quote->setCustomerEmail($guestEmail);
            foreach ($quote->getAllAddresses() as $address) {
                $address->setCustomerId(0);
                $address->setCustomerAddressId(0);
            }

            $this->cartRepository->save($quote);
        }

        return $result;
    }
}
