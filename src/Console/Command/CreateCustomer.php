<?php
/**
 * Ho
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the H&O Commercial License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.h-o.nl/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to info@h-o.com so we can send you a copy immediately.
 *
 * @category    Ho
 * @package     Ho_GuestToShadowCustomer
 * @copyright   Copyright (c) 2017 H&O (http://www.h-o.nl/)
 * @license     H&O Commercial License (http://www.h-o.nl/license)
 */

namespace Ho\GuestToShadowCustomer\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Sales\Api\OrderCustomerManagementInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

class CreateCustomer extends \Symfony\Component\Console\Command\Command
{
    protected $orderCustomerManagement;
    protected $state;

    /**
     * CommandInfo constructor.
     *
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     */
    public function __construct(
        OrderCustomerManagementInterface $orderCustomerManagement,
        State $state
    )
    {
        $this->orderCustomerManagement = $orderCustomerManagement;
        $this->state = $state->setAreaCode(Area::AREA_CRONTAB);
        parent::__construct();
    }


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('g2sc:create:from-order-id');
        $this->setDescription('Create shadow customer account by order ID');
        $this->addArgument('order_id', InputArgument::REQUIRED, __('Order ID'));
        parent::configure();
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->orderCustomerManagement->create($input->getArgument('order_id'));
    }


}