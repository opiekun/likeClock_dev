<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Template_Switcher extends Mage_Adminhtml_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
        $this->_initVariables();
        $this->setTemplate('amasty/ampgrid/grid_template_switcher.phtml');
    }

    protected function _initVariables()
    {

        $attributesKey = $this->getAttributesKey();
        $groupId = Mage::helper('ampgrid')->getSelectedGroupId($attributesKey);

        $variables = array(
            'group_id'           => $groupId,
            'groups'            => Mage::helper('ampgrid')->getGroupsByUserId(),
            'change_group_url'  => $this->getUrl(
                'adminhtml/ampgrid_attribute/changeGroup'
            ),
        );

        foreach ($variables as $varName => $value) {
            $this->setData($varName, $value);
        }
    }
}