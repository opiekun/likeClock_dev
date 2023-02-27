<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

$this->startSetup();

$this->run("
    alter table `{$this->getTable('ampgrid/qty_sold')}` 
    drop foreign key FK_AMPGRID_QTY_SOLD_PRODUCT_ID_CATALOG_PRODUCT_ENTITY_ENTITY_ID;
  
");

$this->endSetup(); 