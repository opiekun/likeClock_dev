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
class Trackingmore_Detrack_Model_Order_Shipment extends 
Mage_Sales_Model_Order_Shipment
{
    /**
     * [getAllTracks description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public function getAllTracks()
    {
        $tracks = parent::getAllTracks();
        $config = Mage::getStoreConfig('tr_section_setttings/settings');
        return $tracks;
    }
    

}
