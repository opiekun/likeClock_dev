<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
$installer = $this;
$installer->startSetup();

$configTable = $this->getTable('core/config_data');
$old = 'amconf/general/image_container';
$new = 'amconf/css_selector/image';

$installer->run("
DELETE FROM `$configTable` WHERE path = '$new';
INSERT  INTO `$configTable` (scope, scope_id, value, path)
  SELECT scope, scope_id, value, '$new'
  FROM `$configTable`
WHERE path = '$old';
");

$installer->endSetup();