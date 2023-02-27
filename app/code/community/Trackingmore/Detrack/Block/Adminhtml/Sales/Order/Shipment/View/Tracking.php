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
class Trackingmore_Detrack_Block_Adminhtml_Sales_Order_Shipment_View_Tracking
 extends Mage_Adminhtml_Block_Sales_Order_Shipment_View_Tracking
{
    public static $carrierList = array();

    /**
     * [getCarriers description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public function getCarriers()
    { 
        if (!self::$carrierList) {
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
                } else {
                    $express[''] = $this->__('----- Trackingmore carriers -----');
                }
                foreach ($TrackingmoreCarriers as $item) {
                    $express[$item->getPrefixedCode()] = $item->getData('name');
                }
            }

            self::$carrierList = $express;
        }

        return self::$carrierList;
    }

    /**
     * [getCarrierTitle description]
     * 
     * @param [type] $code [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]       [description]
     */
    public function getCarrierTitle($code)
    {
        $enabled = Mage::getStoreConfig('tr_section_setttings/settings/status');
        if (!$enabled) {
            return parent::getCarrierTitle($code);
        }

        $allCarriers = $this->getCarriers();

        if (isset($allCarriers[$code])) {
            return $allCarriers[$code];
        } else {
            return parent::getCarrierTitle($code);
        }

    }

}