<?php
declare(strict_types=1);

namespace ReachDigital\GuestToShadowCustomer\Observer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Model\Quote;

class RestoreShadowQuoteToGuestQuote implements \Magento\Framework\Event\ObserverInterface
{
    /** @var CartRepositoryInterface */
    private $cartRepository;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var CustomerSession */
    private $customerSession;

    /**
     * @var CustomerInterfaceFactory
     */
    private $customerFactory;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CustomerRepositoryInterface $customerRepository,
        CustomerSession $customerSession,
        CustomerInterfaceFactory $customerFactory
    ) {
        $this->cartRepository = $cartRepository;
        $this->customerRepository = $customerRepository;
        $this->customerSession = $customerSession;
        $this->customerFactory = $customerFactory;
    }

    /**
     * When restoring the quote, check if associated customer is a shadow customer. If so, must we convert the quote
     * back to a guest quote to allow restoring the quote. Without this, after cancelling an order payment, you would
     * end up with an empty cart due to the customer ID being checked in \Magento\Checkout\Model\Session::getQuote
     *
     * @event restore_quote
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @throws LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer): void
    {
        /** @var Quote $quote */
        $quote = $observer->getData('quote');

        if (!$quote->getCustomerEmail()) {
            return;
        }
        try {
            $customer = $this->customerRepository->get($quote->getCustomerEmail());
        } catch (NoSuchEntityException $e) {
            return;
        }

        // Check if quote customer is shadow, if so, convert quote to guest quote
        $isShadow = $customer->getCustomAttribute('is_shadow');
        if (!$this->customerSession->isLoggedIn() && $isShadow && $isShadow->getValue() == 1) {

            // Must set to empty customer, else it will override customer_id field,
            // see \Magento\Quote\Model\Quote::beforeSave
            $quote->setCustomer($this->customerFactory->create());
            $quote->setCustomerId(0);
            $quote->setCustomerIsGuest(true);

            /**
             * Ensure address validation doesn't fail when saving quote
             * @see \Magento\Quote\Model\Quote\Address\BillingAddressPersister::save
             * @see \Magento\Quote\Model\QuoteAddressValidator::doValidate
             */
            if ($quote->getBillingAddress()) {
                $quote->getBillingAddress()->setCustomerAddressId(null);
            }
            $this->cartRepository->save($quote);
        }
    }
}
