<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */
class Amasty_Pgrid_Model_Observer
{
    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function checkoutAllSubmitAfter(Varien_Event_Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        if (!$quote->getInventoryProcessed()) {
            $this->reindexQuoteInventory($observer);
        }
        return $this;
    }

    /**
     * Refresh qty sold index for specific stock items after succesful order placement
     * @param $observer
     */
    public function reindexQuoteInventory($observer)
    {
        $quote = $observer->getEvent()->getQuote();
        return $this->reindexItems($quote->getAllItems());
    }

    /**
     * Refresh qty sold index for specific stock items after order refund
     * @param $observer
     */
    public function refundCreditmemoInventory($observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();
        return $this->reindexItems($creditmemo->getAllItems());
    }

    /**
     * reindex qty sold
     * @param $items
     * @return $this
     */
    protected function reindexItems($items)
    {
        $productIds = array();

        foreach ($items as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children   = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }

        if (count($productIds)) {
            Mage::getResourceSingleton('ampgrid/indexer_qty')->reindexProducts($productIds);
        }

        return $this;
    }

    public function catalogProductPrepareSave(Varien_Event_Observer $observer) {
        if($observer->getRequest()->getModuleName() == 'ampgrid') {
            $date = $observer->getProduct()->getData('created_at');
            $observer->getProduct()->setData('created_at', strtotime($date));
        }
    }
}