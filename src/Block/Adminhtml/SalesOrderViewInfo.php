<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderAddressInterfaceFactory;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Model\Order\Address\Renderer;
use Temando\Shipping\Block\Adminhtml\Sales\Order\View\Info as TemandoShippingOrderInfo;
use Temando\Shipping\Model\ResourceModel\Order\OrderRepository;
use Temando\Shipping\Model\Shipment\ShipmentProviderInterface;

class SalesOrderViewInfo extends TemandoShippingOrderInfo
{
    /** @var CustomerRepositoryInterface  */
    private $customerRepository;

    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        GroupRepositoryInterface $groupRepository,
        CustomerMetadataInterface $metadata,
        ElementFactory $elementFactory,
        Renderer $addressRenderer,
        ShipmentProviderInterface $shipmentProvider,
        OrderAddressInterfaceFactory $addressFactory,
        OrderRepository $orderRepository,
        CustomerRepositoryInterface $customerRepository,
        $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $groupRepository, $metadata, $elementFactory,
            $addressRenderer, $shipmentProvider, $addressFactory, $orderRepository, $data);

        $this->customerRepository = $customerRepository;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException If customer with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return bool|\Magento\Framework\Phrase
     */
    public function getIsShadowCustomer()
    {
        try {
            $customer = $this->customerRepository->getById($this->getOrder()->getCustomerId());
        } catch (NoSuchEntityException $exception){
            return __('No');
        } catch (LocalizedException $exception) {
            return false;
        }
        return $customer->getCustomAttribute('is_shadow')->getValue() ? __('Yes') : __('No');
    }
}