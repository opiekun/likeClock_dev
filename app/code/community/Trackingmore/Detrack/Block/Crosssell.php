<?php
 
/**
 * PHP version 5
 * 
 * @file(Track.php)
 * 
 * @category  Mage
 * @package   Trackingmore
 * @author    Trackingmore <service@trackingmore.org>
 * @copyright 2017 Trackingmore
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 * @link      https://trackingmore.com
 */

/**
 * Myclass File
 * 
 * @category  Mage
 * @package   Trackingmore
 * @author    Trackingmore <service@trackingmore.org>
 * @copyright 2017 Trackingmore
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 * @link      https://trackingmore.com
 */
class Trackingmore_Detrack_Block_Crosssell extends 
Mage_Catalog_Block_Product_Abstract
{
    /**
     * [getCrossSellingItemsByOrderId description]
     * 
     * @param [type] $orderId [description]
     * @param [type] $limit   [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]          [description]
     */
    public function getCrossSellingItemsByOrderId($orderId, $limit)
    {
        $crossSellingItemsArray = array();
        $orders = Mage::getModel('sales/order')->load($orderId);
        $items = $orders->getAllVisibleItems();

        foreach ($items as $item) {
            $product = Mage::getModel('catalog/product')
            ->load($item->getProductId());
            $crossSellCollection = $product->getCrossSellProductCollection();
            $crossSellCollection
                ->getSelect()->order(new Zend_Db_Expr('RAND()'))->limit($limit);
            foreach ($crossSellCollection as $crossSellItem) {
                $crossSellingItemsArray[] = Mage::getModel('catalog/product')
                ->load($crossSellItem->getId());
            }
        }
        return $crossSellingItemsArray;
    }

}