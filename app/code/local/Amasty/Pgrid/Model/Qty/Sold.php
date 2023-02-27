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
class Amasty_Pgrid_Model_Qty_Sold extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('ampgrid/qty_sold');
    }
}