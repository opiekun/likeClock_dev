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
class Trackingmore_Detrack_Model_Resource_Carrier extends 
Mage_Core_Model_Mysql4_Abstract
{
    /**
     * [construct description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    protected function _construct()
    {
        $this->_init('detrack/carrier', 'id');
    }

}