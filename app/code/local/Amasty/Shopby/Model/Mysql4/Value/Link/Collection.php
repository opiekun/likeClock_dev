<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


class Amasty_Shopby_Model_Mysql4_Value_Link_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('amshopby/value_link');
    }

    public function getValueLink()
    {
        $select = $this->getSelect()->reset(Zend_Db_Select::COLUMNS)->columns('parent_id')->columns('child_id');

        return $this->getConnection()->fetchAll($select);
    }
}
