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
class Trackingmore_Detrack_Model_Track extends Mage_Core_Model_Abstract
{
    /**
     * [_construct description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    protected function _construct()
    {
        $this->_init('detrack/track');
    }

    const STATUS_PENDING = 'pending';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_IN_TRANSIT = 'in_transit';
    const STATUS_EXCEPTION = 'exception';
    const STATUS_NO_INFO = 'no_info';
 

   
    const TYPE_SUBMITTED_PENDING = 0;

 
    const TYPE_SUBMITTED_SENT = 1;

 
    const TYPE_SUBMITTED_REMOVE_PENDING = 2;

    
    public static $mapCols = array(
        'status',
    );


   
    protected $_shippingInfo;

   
    protected $_carrier;

    /**
     * [getOrderedStatusList description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public static function getOrderedStatusList()
    {
        return array(
            self::STATUS_PENDING => 10,
            self::STATUS_NO_INFO => 10,
            self::STATUS_IN_TRANSIT => 30,
            self::STATUS_EXCEPTION => 30,
            self::STATUS_DELIVERED => 60,
        );
    }

    /**
     * [_beforeSave description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    protected function _beforeSave()
    {
       
        if (!$this->getId()) {
            $this->created_at = time();
        } else {
            $this->updated_at = time();
        }
        return parent::_beforeSave();
    }

 
    /**
     * [updateInfo description]
     * 
     * @param [type] $data [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]       [description]
     */
    public function updateInfo($data)
    {   
        foreach (self::$mapCols as $field) {
            if (isset($data[$field])) {
                $this->$field = $data[$field];
            }
        }
       
        $this->save();
    }

    /**
     * [getOrderDataByIdAndCode description]
     * 
     * @param [type]  $orderId   [description]
     * @param [type]  $code      [description]
     * @param boolean $fetchData [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]             [description]
     */
    public function getOrderDataByIdAndCode($orderId, $code, $fetchData = true)
    {
        $fromDbData = Mage::getModel('detrack/track')
            ->getCollection()
            ->addFieldToFilter('code', array('eq' => $code))
            ->addFieldToFilter('order_id', array('eq' => $orderId));
        $trackModel = $fromDbData->getFirstItem();
        $this->setData($trackModel->getData());
        return $this;
    }

    /**
     * [getDataByExpressAndCode description]
     * 
     * @param [type] $carrier [description]
     * @param [type] $code    [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]          [description]
     */
    public function getDataByExpressAndCode($carrier, $code)
    {
        $fromDbData = Mage::getModel('detrack/track')
            ->getCollection()
            ->addFieldToFilter('code', array('eq' => $code))
            ->addFieldToFilter('carrier_code', array('eq' => $carrier));
        $trackModel = $fromDbData->getFirstItem();
        $this->setData($trackModel->getData());

        return $this;
    }

    /**
     * [loadInfoByHash description]
     * 
     * @param [type]  $hash      [description]
     * @param boolean $fetchData [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]             [description]
     */
    public function loadInfoByHash($hash, $fetchData = true)
    {
        $this->load($hash, 'hash');

    }

}
