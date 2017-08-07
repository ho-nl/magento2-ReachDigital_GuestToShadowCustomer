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
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\Module\ModuleListInterface;
class ModuleState extends Command
{

    protected $moduleList;


    /**
     * CommandInfo constructor.
     *
     */
    public function __construct(
        ModuleListInterface $moduleList
    ) {
        $this->moduleList = $moduleList;
        parent::__construct();
    }


    /**
     * Set Name and description of command
     */
    protected function configure()
    {
        $this->setName('g2sc:module:state');
        $this->setDescription('Convert Guest to Shadow Customer Module');
        parent::configure();
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('G2SC Name: ' . $this->getModuleName());
        $output->writeln('G2SC Current Version: ' . $this->getModuleVersion());
    }


    /**
     * @return mixed
     */
    protected function getModuleName()
    {
        return $this->moduleList->getOne('Ho_GuestToShadowCustomer')['name'];
    }


    /**
     * @return mixed
     */
    protected function getModuleVersion()
    {
        return $this->moduleList->getOne('Ho_GuestToShadowCustomer')['setup_version'];
    }
}
