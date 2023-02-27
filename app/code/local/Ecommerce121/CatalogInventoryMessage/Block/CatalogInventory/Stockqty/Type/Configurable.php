<?php
/**
 * 121Ecommerce
 *
 * Class Ecommerce121_CatalogInventoryMessage_Block_CatalogInventory_Stockqty_Type_Configurable
 *
 * @category   Ecommerce121
 * @package    Ecommerce121_CatalogInventoryMessage
 * @module     Extended CatalogInventory
 * @author     Augusto Leao <augusto@121ecommerce.com>
 */
class Ecommerce121_CatalogInventoryMessage_Block_CatalogInventory_Stockqty_Type_Configurable
    extends Mage_CatalogInventory_Block_Stockqty_Type_Configurable {

    const XML_PATH_SHOW_STOCK_THRESHOLD_QTY_MESSAGE = 'cataloginventory/options/show_stock_threshold_qty_message';

    /**
     * Get show flag for showing threshold qty stock message
     *
     * @return string
     */
    public function getShowThresholdQtyMessage()
    {
        if (!$this->hasData('show_stock_threshold_qty_message')) {
            $value = Mage::getStoreConfigFlag(self::XML_PATH_SHOW_STOCK_THRESHOLD_QTY_MESSAGE);
            $this->setData('show_stock_threshold_qty_message', $value);
        }

        return $this->getData('show_stock_threshold_qty_message');
    }


    /**
     * Retrieve visibility of stock qty message
     *
     * @return bool
     */
    public function isMsgVisible()
    {
        return ($this->getStockQty() > 0 && ($this->getStockQty() <= $this->getThresholdQty()) && $this->getShowThresholdQtyMessage());
    }


}