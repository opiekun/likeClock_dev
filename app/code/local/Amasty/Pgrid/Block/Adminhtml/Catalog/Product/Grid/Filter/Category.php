<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Filter_Category extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    public function getCondition()
    {
        /**
         * @var Mage_Catalog_Model_Resource_Product_Collection $collection
         */
        $collection = Mage::registry('product_collection');

        if ($collection)
        {
            if ($this->getValue())
            {
                $category = Mage::getModel('catalog/category')->load($this->getValue());
                if (
                    !Mage::helper('ambase')->isVersionLessThan(1, 7) &&
                    Mage::getStoreConfig('ampgrid/additional/category_anchor') &&
                    $category->getIsAnchor()
                ){
                    $categories = Mage::helper('ampgrid/category')->getChildrenCategories($category)->getAllIds();
                    $categories[] = $this->getValue();
                    $collection->joinField('category_id',
                        'catalog/category_product',
                        'category_id',
                        'product_id=entity_id',
                        null,
                        'left'
                    );
                    $collection->addAttributeToFilter('category_id', array('in' => $categories));
                    $collection->getSelect()
                        ->group('e.entity_id');
                } else {
                    $collection->addCategoryFilter($category);
                }
            }
        
            if (0 == $this->getValue() && strlen($this->getValue()) > 0)
            {

                $collection->getSelect()->joinLeft(array('nocat_idx' => $collection->getTable('catalog/category_product')),
                    '(nocat_idx.product_id = e.entity_id)',
                    array(
                        'nocat_idx.category_id',
                    )
                );
                $collection->getSelect()->where('nocat_idx.category_id IS NULL');

            }
        }
    }
}
