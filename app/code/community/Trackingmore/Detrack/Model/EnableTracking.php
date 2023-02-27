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
class Trackingmore_Detrack_Model_EnableTracking extends Mage_Core_Model_Config_Data
{
    /**
     * [save description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public function save() 
    {     
          
        $helper = Mage::helper('detrack');
                    
        $apiKey = $this->_data['groups']['settings']['fields']['api_key']['value'];
        $pluginStatus = $this
        ->_data['groups']['settings']['fields']['status']['value']; 
        $oldApiKey = Mage::getStoreConfig('tr_section_setttings/settings/api_key');
        $oldPluginStatus = Mage::getStoreConfig(
            'tr_section_setttings/settings/status'
        );
        Mage::app()->getStore()
        ->setConfig('tr_section_setttings/settings/api_key', $apiKey);
        $oldCrossSellStatus = Mage::getStoreConfig(
            'tr_section_setttings/crosssell/cross_sell_page'
        );
        $crossSellStatus = $this
        ->_data['groups']['crosssell']['fields']['cross_sell_page']['value'];
        
        if (!$apiKey) {
            Mage::throwException(
                Mage::helper('detrack')
                ->__('You have to enter API key before saving config!')
            );
        }
        
        $flag = 0;
        $body['plugin_crossSell_status'] = $crossSellStatus;
        if (!$pluginStatus AND $oldApiKey !== $apiKey) {
            $info = $helper->testApiKey($pluginStatus, 'GET', $body); 
            $flag = 1;
        } else if (($pluginStatus AND $oldApiKey !== $apiKey) 
            OR ($pluginStatus AND !$oldPluginStatus)
        ) {
            $info = $helper->testApiKey($pluginStatus, 'GET', $body); 
            $flag = 1;
        } else if ($crossSellStatus != $oldCrossSellStatus) {
            
            $info = $helper->testApiKey($pluginStatus, 'GET', $body); 
            $flag = 1;
        }
        if ($flag and (!$info OR $info['statusCode'] == 400)) {
            if ($info['body']['reason']) {
                Mage::getSingleton('core/session')
                ->addWarning(Mage::helper('detrack')->__($info['body']['reason']));
            } else {
                Mage::getSingleton('core/session')->addWarning(
                    Mage::helper('detrack')->__('Error sending data to API')
                );
            }
        }
        return parent::save();
    }
}