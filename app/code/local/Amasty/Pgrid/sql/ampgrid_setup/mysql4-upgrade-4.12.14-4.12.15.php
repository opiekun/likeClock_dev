<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

/**
 * @var Magento_Db_Adapter_Pdo_Mysql $this
 */
$installer = $this;
$installer->startSetup();

$installer->getConnection()->addIndex(
    $installer->getTable('ampgrid/qty_sold'),
    $installer->getIdxName(
        'ampgrid/qty_sold',
        array('product_id')
    ),
    array('product_id'),
    Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
);

$installer->endSetup();
$installer->getConnection()->closeConnection();
