<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class Amasty_Pgrid_Block_Adminhtml_Catalog_Product
    extends Mage_Adminhtml_Block_Catalog_Product
{
    /**
     * Set template
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('amasty/ampgrid/product.phtml');
    }
}