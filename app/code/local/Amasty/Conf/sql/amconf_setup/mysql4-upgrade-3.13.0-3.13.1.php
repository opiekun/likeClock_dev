<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
$installer = $this;
$installer->startSetup();

if(version_compare(Mage::getVersion(), '1.9.3.0') >= 0) {
    $configTable = $this->getTable('core/config_data');

    $installer->run("
       INSERT  INTO `$configTable` (scope, scope_id, path, value)
        VALUES ('default', 0, 'amconf/check_version/show_options', 1)
    ");
}

$installer->endSetup();
