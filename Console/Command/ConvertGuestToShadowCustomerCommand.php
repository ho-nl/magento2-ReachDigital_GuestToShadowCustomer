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

use Symfony\Component\Console\Command\Command;
/**
 * Command for enabling cache
 */
class ConvertGuestToShadowCustomerCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('g2sc:convert');
        $this->setDescription('Convert Guest to Shadow Customer by Order ID');
        parent::configure();
    }
}
