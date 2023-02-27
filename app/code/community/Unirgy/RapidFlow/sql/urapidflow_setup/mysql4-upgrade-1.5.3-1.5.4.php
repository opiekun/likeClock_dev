<?php

$this->startSetup();

try { $this->run('set session old_alter_table=1'); } catch (Exception $e) {}

try {
    // MySQL 5.7.4, the IGNORE clause for ALTER TABLE is removed and its use produces errors
    $this->run("
ALTER IGNORE TABLE {$this->getTable('catalog_product_entity_media_gallery')}
ADD UNIQUE KEY UNQ_GALLERY_ENTRY (entity_id,attribute_id,value);
DROP INDEX UNQ_GALLERY_ENTRY ON {$this->getTable('catalog_product_entity_media_gallery')};
");
} catch(Exception $e) {
    Mage::log($e->getMessage(), Zend_Log::ALERT, 'rf-install.log', true);
}

$this->endSetup();
