<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Road9\Notificaions\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
		$installer->getConnection()->dropTable($setup->getTable('user_notifications'));

        // Get user_notifications table
        $tableName = $installer->getTable('user_notifications');
        // Check if the table already exists
            // Create tutorial_simplenews table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'user_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'user_id'
                )
                ->addColumn(
                    'device_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Device_id'
                );
            $installer->getConnection()->createTable($table);
        

        $installer->endSetup();
    }
}