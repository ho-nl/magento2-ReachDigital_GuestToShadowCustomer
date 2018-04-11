<?php
/**
 * Copyright © Reach Digital (https://www.reachdigital.io/)
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Setup;

use Magento\Customer\Model\Customer;
use Magento\Framework\Indexer\IndexerInterfaceFactory;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

class UpgradeData implements UpgradeDataInterface
{
    /** @var StateInterface */
    private $state;

    /** @var IndexerInterfaceFactory  */
    private $indexerFactory;

    public function __construct(StateInterface $state, IndexerInterfaceFactory $indexerFactory)
    {
        $this->state = $state;
        $this->indexerFactory = $indexerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if (version_compare($context->getVersion(), '1.0.1') < 0) {
            $indexer = $this->indexerFactory->create()->load(Customer::CUSTOMER_GRID_INDEXER_ID);
            $indexer->invalidate();
        }

        $setup->endSetup();
    }
}
