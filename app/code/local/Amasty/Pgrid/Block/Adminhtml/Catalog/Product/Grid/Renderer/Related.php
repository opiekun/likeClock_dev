<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Related extends Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $ret = '';
        $index = $this->getColumn()->getIndex();
        
        $collection = NULL;
           
        switch ($index){
            case "related_products":
                $collection = $row->getRelatedProductCollection();
                break;
            case "up_sells":
                $collection = $row->getUpSellProductCollection();
                break;
            case "cross_sells":
                $collection = $row->getCrossSellProductCollection();
                break;
        }
        
        $qty = Mage::getStoreConfig('ampgrid/additional/products_qty');
        
        $collection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'left');
        $collection->setPageSize($qty);
        
        $items = $collection->getItems();
        
        if ($items){
            
            foreach ($collection->getItems() as $item){
                $ret .= '<div style="font-size: 90%; margin-bottom: 8px; border-bottom: 1px dotted #bcbcbc;">' . $item->getName() . '</div>';
            }
        }
        
        return $ret;
    }
}