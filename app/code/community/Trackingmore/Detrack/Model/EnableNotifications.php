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
class Trackingmore_Detrack_Model_Enablenotifications extends 
Mage_Core_Model_Config_Data
{
    /**
     * [save description]
     * 
     * @author rackingmore 2017-11-08
     * @return [type] [description]
     */
    public function save() 
    {     
         
        $helper = Mage::helper('detrack');
        $apiKey = Mage::getStoreConfig('tr_section_setttings/settings/api_key');
        $pluginStatus = Mage::getStoreConfig('tr_section_setttings/settings/status');
        if (!$apiKey) {
            Mage::throwException(
                Mage::helper('detrack')
                ->__('You have to enter API key before saving config!')
            );
        }
        $flag = 0;
        if ($apiKey) {
            $body['plugin_notify_status'] = $this
            ->_data['groups']['settings']['fields']['tr_enable_notifications']['value'];
            $info   = $helper->notifyApiKey($pluginStatus, 'GET', $body);
            $flag   = 1;
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