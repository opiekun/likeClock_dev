<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


/**
 * Class Sold.php
 *
 * @author Artem Brunevski
 */
class Amasty_Pgrid_Model_Resource_Qty_Sold extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Define main table and initialize connection
     *
     */
    protected function _construct()
    {
        $this->_init('ampgrid/qty_sold', 'product_id');

    }
}