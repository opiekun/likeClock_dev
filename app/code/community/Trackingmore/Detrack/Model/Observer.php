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
class Trackingmore_Detrack_Model_Observer
{

 
    protected $_config = array();

    
    /**
     * [trackingPopupPreDispatch description]
     * 
     * @param Varien_Event_Observer $observer [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]                          [description]
     */
    public function trackingPopupPreDispatch(Varien_Event_Observer $observer)
    {
        $enabled = Mage::getStoreConfig('tr_section_setttings/settings/status');
        if (!$enabled) {
            return false;
        }
 
        $shippingTrackModel = null;
        $shippingList = null;  
        $hash = Mage::app()->getRequest()->getParam('hash');
        $shippingInfoModel = Mage::getModel('shipping/info')->loadByHash($hash);
       
        $shippingTrackId = $shippingInfoModel->getTrackId(); 
        if ($shippingTrackId) {
            $shippingTrackModel = Mage::getModel('sales/order_shipment_track')->load($shippingTrackId);
        } else {
            $trackingInfo = $shippingInfoModel->getTrackingInfo();  
            if ($trackingInfo) {
                $shippingList = current($trackingInfo); 
            }
        }
       
     
        if ($shippingList) {
            $orderId = $shippingInfoModel->getOrderId();
            if (!$orderId) {
                $shipId = $shippingInfoModel->getShipId();
                if ($shipId) {
                    $shipModel = Mage::getModel('sales/order_shipment')->load($shipId);
                    $orderId = $shipModel->getOrderId();
                }
            }
          
            $models = array();
            foreach ($shippingList as $item) {
                $trackModel = Mage::getModel('detrack/track')
                    ->getOrderDataByIdAndCode($orderId, $item['number']);
                if ($trackModel->getId()) {
                    $models[] = $trackModel;
                }
            }
             
            if ($models) {
                Mage::register('models', $models);

               
                $request = Mage::app()->getRequest();
                $request->initForward()
                    ->setControllerName('index')
                    ->setModuleName('detrack')
                    ->setActionName('popup')
                    ->setDispatched(false);

                return false;
            }
        } elseif ($shippingTrackModel->getId()) {
            $trackData = $shippingTrackModel->getData();
            $trackModel = Mage::getModel('detrack/track')
                ->getOrderDataByIdAndCode($trackData['order_id'], $this->_getTrackingCode($trackData));
            if ($trackModel->getId()) {
                Mage::register('model', $trackModel);
            
                $request = Mage::app()->getRequest();
                $request->initForward()
                    ->setControllerName('index')
                    ->setModuleName('detrack')
                    ->setActionName('popup')
                    ->setDispatched(false);

                return false;
            }
        }
    }


    /**
     * [afterShipmentSaved description]
     * 
     * @param Varien_Event_Observer $observer [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]                          [description]
     */
    public function afterShipmentSaved(Varien_Event_Observer $observer)
    {  
       
        $enabled = Mage::getStoreConfig('tr_section_setttings/settings/status');
        if (!$enabled) {
            return false;
        }

        $track = $observer->getEvent()->getTrack();
        $order = $track->getShipment()->getOrder();
       
        
        $trackData = $track->getData();
        $orderData = $order->getData();
        $addressData = $order->getShippingAddress()->getData();

        $trackingCode = $this->_getTrackingCode($trackData);
        $email = trim($orderData['customer_email']) ? $orderData['customer_email'] : null;
        $phone = trim($addressData['telephone']) ? $addressData['telephone'] : null;
        
        $notifyEnabled = Mage::getStoreConfig('tr_section_notification_emails/settings/tr_enable_notifications');
        

        $collection = Mage::getModel('detrack/track')
            ->getCollection()
            ->addFieldToFilter('code', array('eq' => $trackingCode))
            ->addFieldToFilter('order_id', array('eq' => $order->getId()));
        $trackModel = $collection->getFirstItem();
        $trackModel->setSubmitted(Trackingmore_Detrack_Model_Track::TYPE_SUBMITTED_PENDING);

        if (!$trackModel->getId()) {
            $trackModel->setOrderId($order->getId());
            $trackModel->setShipmentId($track->getId());
            $trackModel->setCode($trackingCode);
            $carrierCode = str_replace(Trackingmore_Detrack_Model_Carrier::CODE_PREFIX, '', $trackData['carrier_code']);
            $trackModel->setCarrierCode($carrierCode);
            $trackModel->setCarrierName($trackData['title']);
            $trackModel->setPhone($phone);
            $trackModel->setEmail($email);
            $trackModel->setPostalCode($addressData['postcode']);
            $trackModel->setDestinationCountry($addressData['country_id']);
            $trackModel->setStatus(Trackingmore_Detrack_Model_Track::STATUS_PENDING);
            
            if ($enabled && $notifyEnabled) {
                $body['customerFirstname'] = trim($orderData['customer_firstname'])?$orderData['customer_firstname']:'';
                $body['customerLastname']  = trim($orderData['customer_lastname'])?$orderData['customer_lastname']:'';
                $body['incrementId']       = trim($orderData['increment_id'])?$orderData['increment_id']:'';
                $body['email']             = $email;
                $body['carrierCode']       = $carrierCode;
                $body['trackingCode']      = $trackingCode;
                $helper = Mage::helper('detrack');
                $info   = $helper->setShipmentInfo(true, $body);
            }
        }
        
        
        $trackModel->save();
    }


    /**
     * [_getTrackingCode description]
     * 
     * @param [type] $trackData [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]            [description]
     */
    protected function _getTrackingCode($trackData)
    {
        $trackingCode = trim($trackData['track_number']) ? trim($trackData['track_number']) : trim($trackData['number']);

        return $trackingCode;
    }

  
    /**
     * [_getConfig description]
     * 
     * @param Mage_Sales_Model_Order $order [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]                        [description]
     */
    protected function _getConfig(Mage_Sales_Model_Order $order)
    {
        $websiteId = $order->getStore()->getWebsiteId();

        if (!isset($this->_config[$websiteId])) {
            $config = Mage::app()->getWebsite($websiteId)->getConfig('tr_section_setttings');

            $this->_config[$websiteId] = (object)((array)$config['settings']);
        }

        return $this->_config[$websiteId];
    }


    /**
     * [saveCarriers description]
     * 
     * @param Varien_Event_Observer $observer [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]                          [description]
     */
    public function saveCarriers(Varien_Event_Observer $observer)
    {
        $carriers = Mage::getModel('detrack/carrier')->getList(true);
        foreach ($carriers as $carrier) {
            $configValue = (int) Mage::getStoreConfig('tr_section_carriers/carriers/' . $carrier->getCode());

            if ($carrier->enabled != $configValue) {
                $carrier->setEnabled($configValue);
                $carrier->save();
            }
        }
    }

}