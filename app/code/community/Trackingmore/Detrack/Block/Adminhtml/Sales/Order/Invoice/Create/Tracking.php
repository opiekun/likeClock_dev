<?php

/**
 * Description
 * PHP version 5
 * 
 * @file(Tracking.php)
 * 
 * @category  Mage
 * @package   Mage_Adminhtml
 * @author    Magento Core Team <core@magentocommerce.com>
 * @copyright 2006-2017 Tracking.php
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 * @link      https://trackingmore.com
 */
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category  Mage
 * @package   Mage_Adminhtml
 * @author    Magento Core Team <core@magentocommerce.com>
 * @copyright 2006-2017 Tracking.php
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License
 * @link      https://trackingmore.com
 */
class Trackingmore_Detrack_Block_Adminhtml_Sales_Order_Invoice_Create_Tracking
 extends Mage_Adminhtml_Block_Sales_Order_Invoice_Create_Tracking
{
    
    /* public function getCarriers()
    {
        $express[''] = $this->__('----- Trackingmore carriers -----');
        return $express;
        $express = parent::getCarriers();
        $enabled = Mage::getStoreConfig('tr_section_setttings/settings/status');
        
        if (!$enabled) {
            return $express; 
        }
        $disableDefault = isset($config['disable_default_carriers'])
         && $config['disable_default_carriers'] ? 1 : 0;
        $TrackingmoreCarriers = Mage::getModel('detrack/carrier')
            ->getList(); 
        if ($TrackingmoreCarriers) {
            if ($disableDefault) {
                $express = array();
            }
            else {
                $express[''] = $this->__('----- Trackingmore carriers -----');
            }
            foreach ($TrackingmoreCarriers as $item) {
                $express[$item->getPrefixedCode()] = $item->getData('name');
            }
        }
        return $express;
    } */
    
    /**
     * [getCarriers description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public function getCarriers()
    {
        $express = parent::getCarriers();
        $enabled = Mage::getStoreConfig('tr_section_setttings/settings/status');
        if (!$enabled) {
            return $express; 
        }
        $disableDefault = isset($config['disable_default_carriers']);
        $disableDefault = isset($config['disable_default_carriers'])
         && $config['disable_default_carriers'] ? 1 : 0;
        $TrackingmoreCarriers = Mage::getModel('detrack/carrier')
            ->getList(); 
        if ($TrackingmoreCarriers) {
            if ($disableDefault) {
                $express = array();
            } else {
                $express[''] = $this->__('----- Trackingmore carriers -----');
            }
            foreach ($TrackingmoreCarriers as $item) {
                $express[$item->getPrefixedCode()] = $item->getData('name');
            }
        }
        return $express;
    }
    
}
