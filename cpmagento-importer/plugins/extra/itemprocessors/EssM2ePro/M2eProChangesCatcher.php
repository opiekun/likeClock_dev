<?php

/*
 * @copyright  Copyright (c) 2013 by  ESS-UA.
 */

class M2eProChangesCatcher extends Magmi_ItemProcessor
{
    const CHANGE_UPDATE_ATTRIBUTE_CODE = '__INSTANCE__';
    const CHANGE_UPDATE_ACTION         = 'update';
    const CHANGE_INITIATOR_DEVELOPER   = 4;

    protected $changes = array();

    protected $statistics = array(
        'not_presented' => 0,
        'existed'       => 0,
        'inserted'      => 0
    );

    // ########################################

    public function initialize($params) {}

    public function getPluginInfo()
    {
        return array(
            "name"    => "Ess M2ePro Product Changes Inspector",
            "author"  => "ESS",
            "version" => "1.0.4",
            "url"     => "http://docs.m2epro.com/display/BestPractice/Plugin+for+Magmi+Import+Tool"
        );
    }

    // ########################################

    public function processItemAfterId(&$item, $params = null)
    {
        $result = parent::processItemAfterId($item, $params);

        $this->changes[$params['product_id']] = array(
            'product_id'    => $params['product_id'],
            'action'        => self::CHANGE_UPDATE_ACTION,
            'attribute'     => self::CHANGE_UPDATE_ATTRIBUTE_CODE,
            'initiators'    => self::CHANGE_INITIATOR_DEVELOPER,
            'update_date'   => $date = date('Y-m-d H:i:s'),
            'create_date'   => $date
        );

        return $result;
    }

    public function endImport()
    {
        $this->filterOnlyAffectedChanges();
        $this->insertChanges();

        return true;
    }

    // ########################################

    private function filterOnlyAffectedChanges()
    {
        $count = count($this->changes);
        $this->log("Will be checked {$count} products.");

        if ($count <= 0) {
            return;
        }

        $listingProductTable  = $this->tablename('m2epro_listing_product');
        $variationOptionTable = $this->tablename('m2epro_listing_product_variation_option');
        $listingOtherTable    = $this->tablename('m2epro_listing_other');

        $stmt = $this->select("SELECT DISTINCT `product_id` FROM `{$listingProductTable}`
                               UNION
                               SELECT DISTINCT `product_id` FROM `{$variationOptionTable}`
                               UNION
                               SELECT DISTINCT `product_id` FROM `{$listingOtherTable}`
                               WHERE `component_mode` = 'ebay' AND
                                     `product_id` IS NOT NULL");

        $productsInListings = array();
        while ($row = $stmt->fetch()) {
            $productsInListings[] = (int)$row['product_id'];
        }

        foreach ($this->changes as $key => $change) {

            if (!in_array($change['product_id'], $productsInListings)) {
                $this->statistics['not_presented']++;
                unset($this->changes[$key]);
            }
        }
    }

    private function insertChanges()
    {
        if (count($this->changes) <= 0) {
            $this->log('The updated products are not presented in the M2e Pro Listings.');
            return;
        }

        $tableName = $this->tablename('m2epro_product_change');

        $existedChanges = array();
        foreach (array_chunk($this->changes, 500, true) as $productChangesPart) {

            if (count($productChangesPart) <= 0) {
                continue;
            }

            $stmt = $this->select("SELECT *
                           FROM `{$tableName}`
                           WHERE `product_id` IN (?)", implode(',', array_keys($productChangesPart)));

            while ($row = $stmt->fetch()) {
                $existedChanges[] = $row['product_id'].'##'.$row['attribute'];
            }
        }

        $insertSql = "INSERT INTO `{$tableName}`
                      (`product_id`,`action`,`attribute`,`initiators`,`update_date`,`create_date`)
                      VALUES (?,?,?,?,?,?)";

        foreach ($this->changes as $productId => $change) {

            if (in_array($change['product_id'].'##'.$change['attribute'], $existedChanges)) {
                $this->statistics['existed']++;
                continue;
            }

            $this->statistics['inserted']++;
            $this->insert($insertSql, array($change['product_id'],
                                            $change['action'],
                                            $change['attribute'],
                                            $change['initiators'],
                                            $change['update_date'],
                                            $change['create_date']));
        }

        $this->saveStatistics();
    }

    // ########################################

    protected function resetStatistics()
    {
        $this->statistics = array(
            'not_presented' => 0,
            'existed'       => 0,
            'inserted'      => 0
        );
    }

    protected function saveStatistics()
    {
        $message  = "Not presented (skipped): {$this->statistics['not_presented']} ## ";
        $message .= "Existed (skipped): {$this->statistics['existed']} ## ";
        $message .= "Processed: {$this->statistics['inserted']}.";

        $this->log($message);
        $this->resetStatistics();
    }

    // ########################################
}