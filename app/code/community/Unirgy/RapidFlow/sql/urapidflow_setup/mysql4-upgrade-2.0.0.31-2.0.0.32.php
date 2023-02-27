<?php
/** @var Mage_Core_Model_Resource_Setup $this */
$this->startSetup();

try {
    $tableName = $this->getTable('catalog_product_entity_media_gallery');

    $connection = $this->getConnection();

    $indexList = $connection->getIndexList($tableName);

    // adding index to speed up gallery updates
    if (!in_array('CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE', $indexList)) {
        $connection->addIndex($tableName, 'CATALOG_PRODUCT_ENTITY_MEDIA_GALLERY_VALUE', 'value');
    }
} catch(Exception $e) {
    Mage::log($e->getMessage(), Zend_Log::ALERT, 'rf-install.log', true);
}

$this->endSetup();
