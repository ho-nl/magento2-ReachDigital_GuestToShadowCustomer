<?php
/**
 * Copyright (c) 2018 Reach Digital, http://www.reachdigital.nl
 * See LICENSE.txt for license details.
 */

namespace ReachDigital\GuestToShadowCustomer\Setup;

class InstallSchema implements \Magento\Framework\Setup\InstallSchemaInterface
{

    /**
     *
     *
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(
        \Magento\Framework\Setup\SchemaSetupInterface $setup,
        \Magento\Framework\Setup\ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();
        $tableName = 'customer_entity';
        if ($installer->tableExists($tableName)) {
            $installer->getConnection()->addColumn($tableName,
                            'is_shadow',
                            \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                            null,
                            ['unsigned' => true, 'nullable' => false, 'default' => '0'],
                            'Is Shadow Customer');
        }
        $installer->endSetup();
    }
}