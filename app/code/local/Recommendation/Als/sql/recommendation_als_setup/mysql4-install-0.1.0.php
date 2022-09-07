<?php
$installer = $this;
$installer->startSetup();
$table = $installer->getConnection()
    ->newTable($installer->getTable('page_recomended_user'))
        ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'ID')
        ->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_TEXT, 256, array(
		'nullable'  => false,
        ), 'Page Id')
	->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_TEXT, 256, array(
		'nullable'  => false,
        ), 'Store Id')
        ;
		
$installer->getConnection()->createTable($table);

$installer->endSetup();