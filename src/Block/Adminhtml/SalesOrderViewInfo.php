<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Block\Adminhtml;

use Magento\Backend\Block\Template;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;

class SalesOrderViewInfo extends Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $_authorization;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_backendSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * @var \Magento\Framework\Code\NameBuilder
     */
    protected $nameBuilder;

    /** @var CustomerRepositoryInterface  */
    private $customerRepository;

    /**
     * SalesOrderViewInfo constructor.
     *
     * @param Registry                    $registry
     * @param CustomerRepositoryInterface $customerRepository
     * @param Template\Context            $context
     * @param array                       $data
     */
    public function __construct(
        Registry $registry,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->customerRepository = $customerRepository;
        $this->_coreRegistry = $registry;
        $this->_localeDate = $context->getLocaleDate();
        $this->_authorization = $context->getAuthorization();
        $this->mathRandom = $context->getMathRandom();
        $this->_backendSession = $context->getBackendSession();
        $this->formKey = $context->getFormKey();
        $this->nameBuilder = $context->getNameBuilder();
        parent::__construct($context, $data);
    }

    /**
     * Retrieve available order
     *
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if ($this->_coreRegistry->registry('current_order')) {
            return $this->_coreRegistry->registry('current_order');
        }
        if ($this->_coreRegistry->registry('order')) {
            return $this->_coreRegistry->registry('order');
        }
        throw new \Magento\Framework\Exception\LocalizedException(__('We can\'t get the order instance right now.'));
    }

    /**
     * @return bool|\Magento\Framework\Phrase
     */
    public function getIsShadowCustomer()
    {
        try {
            $customer = $this->customerRepository->getById($this->getOrder()->getCustomerId());
        } catch (NoSuchEntityException $exception) {
            return __('No');
        } catch (LocalizedException $exception) {
            return false;
        }
        return $customer->getCustomAttribute('is_shadow')->getValue() ? __('Yes') : __('No');
    }
}
