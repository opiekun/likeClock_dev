<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */


/**
 * Class Qty.php
 *
 * @author Artem Brunevski
 */
class Amasty_Pgrid_Model_Indexer_Qty extends Mage_Index_Model_Indexer_Abstract
{
    /**
     * Data key for matching result to be saved in
     */
    const EVENT_MATCH_RESULT_KEY = 'ampgrid_qty_sold_match_result';

    /**
     * @var array
     */
    protected $_matchedEntities = array(
        Mage_CatalogInventory_Model_Stock_Item::ENTITY => array(
            Mage_Index_Model_Event::TYPE_SAVE
        )
    );

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('ampgrid/indexer_qty');
    }

    /**
     * Get Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('ampgrid')->__('Qty Sold');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('ampgrid')->__('Index Qty Sold Product for Extended Product Grid');
    }

    /**
     * Register indexer required data inside event object
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        $event->addNewData(self::EVENT_MATCH_RESULT_KEY, true);
        switch ($event->getEntity()) {
            case Mage_CatalogInventory_Model_Stock_Item::ENTITY:
                /* @var $object Mage_CatalogInventory_Model_Stock_Item */
                $object = $event->getDataObject();
                $event->addNewData('reindex_ampgrid_qty_sold', $object->getProductId());
                break;
        }
    }


    /**
     * Process event based on event state data
     *
     * @param   Mage_Index_Model_Event $event
     */
    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        $data = $event->getNewData();
        if (array_key_exists('reindex_ampgrid_qty_sold', $data)){
            $productId = $data['reindex_ampgrid_qty_sold'];
            $this->_getResource()->reindexProducts($productId);
        }
    }
}