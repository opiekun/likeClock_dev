<?php

/**
 * PHP version 5
 * 
 * @file(Tracking.php)
 * 
 * @category  Mage
 * @package   Trackingmore
 * @author    Trackingmore <service@trackingmore.org>
 * @copyright 2017 Trackingmore
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 * @link      https://trackingmore.com
 */
$installer = $this;

$installer->startSetup();
$tableNameTrack = $installer->getTable('detrack/track');
$tableNameCarriers = $installer->getTable('detrack/carrier');
$installer->run("DROP TABLE IF EXISTS `{$tableNameTrack}`;");
$installer->run("DROP TABLE IF EXISTS `{$tableNameCarriers}`;");

$sql=<<<SQLTEXT

CREATE TABLE IF NOT EXISTS `{$tableNameTrack}` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `hash` varchar(255) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `shipment_id` INT DEFAULT NULL,
  `code` varchar(50) NOT NULL,
  `carrier_code` varchar(50) DEFAULT NULL,
  `carrier_name` varchar(255) DEFAULT NULL,
  `postal_code` varchar(50) DEFAULT NULL,
  `ship_date` varchar(50) DEFAULT NULL,
  `destination_country` varchar(50) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `created_at` INT DEFAULT NULL,
  `updated_at` INT DEFAULT NULL,
  `submitted` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `hash` (`hash`),
  KEY `order_id` (`order_id`),
  KEY `submitted` (`submitted`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `{$tableNameCarriers}` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `code` varchar(50) NOT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `homepage` varchar(255) DEFAULT NULL,
  `enabled` INT NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

SQLTEXT;

$installer->run($sql);

$installer->endSetup();