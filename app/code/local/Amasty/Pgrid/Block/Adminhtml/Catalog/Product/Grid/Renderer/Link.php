<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

class  Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Link
extends Amasty_Pgrid_Block_Adminhtml_Catalog_Product_Grid_Renderer_Abstract
{
/**
* Format variables pattern
*
* @var string
*/


/**
* Renders grid column
*
* @param Varien_Object $row
* @return mixed
*/

    public function render(Varien_Object $row)
    {

        $storeId = (int)Mage::app()->getRequest()->getParam(
            'store', Mage::app()->getDefaultStoreView()->getId()
        );
        $url = Mage::getModel('catalog/product')->setStoreId($storeId)->load(
            $row["entity_id"]
        )->getProductUrl();

       $imageUrl = Mage::getDesign()->getSkinUrl('images/ampgrid/ico-amasty-product.png');
       return "<a href='$url' target='blank'><img src=".$imageUrl."></a>";
    }

}