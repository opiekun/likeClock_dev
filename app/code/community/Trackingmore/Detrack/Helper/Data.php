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
class Trackingmore_Detrack_Helper_Data extends Mage_Core_Helper_Abstract
{
 
    const API_BACKEND = 'http://api.trackingmore.com/v1/'; 
    const TR_ROUTE_CARRIERS = 'carriers/';
    const TR_ROUTE_TRACKINGS = 'trackings';
    const TR_ROUTE_TRACKINGS_BATCH = 'trackings/batch'; 
    const TR_ROUTE_TRACKING_INFO = 'trackings/%s/%s';
    const TR_ROUTE_CONNECTORS = 'connectors/';
    const TR_ROUTE_TEST = 'test/';
    const TR_ROUTE_SETINFO = 'setinfo/';
    const TR_ROUTE_NOTIFYAPI = 'notifyapi/';
    const API_ROLE_NAME = 'trackingmore_connection'; 
    const XML_PATH_API_KEY = 'tr_section_setttings/settings/api_key'; 

  
    protected $_apiKey; 
    protected $_lastStatusCode; 
    protected $_shopStatusCode; 

    /**
     * [_getApiKey description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    protected function _getApiKey()
    { 
        if ($this->_apiKey === null) {
            $this->_apiKey = Mage::getStoreConfig(self::XML_PATH_API_KEY);
        }

        return $this->_apiKey;
    }

    /**
     * [_getApiData description]
     * 
     * @param [type] $route    [description]
     * @param string $method   [description]
     * @param array  $sendData [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]           [description]
     */
    protected function _getApiData($route, $method = 'GET', $sendData = array())
    {
        $sendData   = $this->_getSendData($sendData);
        $requestUrl = self::API_BACKEND.$route; 
        $curlObj = curl_init();
        curl_setopt($curlObj, CURLOPT_URL, $requestUrl);
        if ($method == 'POST') {
            curl_setopt($curlObj, CURLOPT_POST, 1);
        } elseif ($method == 'PUT') {
            curl_setopt($curlObj, CURLOPT_PUT, true);
        } else {
            curl_setopt($curlObj, CURLOPT_CUSTOMREQUEST, $method);
        }
     
        curl_setopt($curlObj, CURLOPT_CONNECTTIMEOUT, 25);
        curl_setopt($curlObj, CURLOPT_TIMEOUT, 90);

        curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlObj, CURLOPT_HEADER, 0);
        $headers = array(
            'trackingmore-api-key: ' . $this->_getApiKey(),
            'Content-Type: application/json',
        ); 
        if ($sendData) {
            $dataString = json_encode($sendData);
            curl_setopt($curlObj, CURLOPT_POSTFIELDS, $dataString);
            $headers[] = 'Content-Length: ' . strlen($dataString);
        }
        curl_setopt($curlObj, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($curlObj);
        $this->_lastStatusCode = curl_getinfo($curlObj, CURLINFO_HTTP_CODE);
        curl_close($curlObj);
        unset($curlObj); 

        return $response;
    }
    
    /**
     * [_getSendData description]
     * 
     * @param [type] $sendData [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]           [description]
     */
    protected function _getSendData($sendData)
    {
        $sendData['plugin_type'] = 'magento';
        $sendData['plugin_version'] = $this->getExtensionVersion();
        $sendData['plugin_shop_version'] = Mage::getVersion();
        $sendData['plugin_user'] = Mage::getStoreConfig('trans_email/ident_general/name');
        $sendData['plugin_email'] = Mage::getStoreConfig('trans_email/ident_general/email');
        $sendData['plugin_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
        $sendData['plugin_shop_status'] = $this->_shopStatusCode;
        $sendData['plugin_key'] = $this->_getApiKey();    
        return  $sendData;
    }

    /**
     * [getTrackingInfo description]
     * 
     * @param [type] $carrierCode  [description]
     * @param [type] $trackingCode [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]               [description]
     */
    public function getTrackingInfo($carrierCode, $trackingCode)
    {
        $returnData = array();
        $requestUrl = sprintf(self::TR_ROUTE_TRACKING_INFO, $carrierCode, $trackingCode);
        $result = $this->_getApiData($requestUrl, 'GET');
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }

    /**
     * [getCarrierList description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public function getCarrierList()
    {
        $returnData = array();
        $requestUrl = self::TR_ROUTE_CARRIERS; 
        $result = $this->_getApiData($requestUrl, 'GET'); 
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }
    
    /**
     * [testApiKey description]
     * 
     * @param [type] $status   [description]
     * @param [type] $methode  [description]
     * @param [type] $sendData [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]           [description]
     */
    public function testApiKey($status,$methode,$sendData)
    {   
        $returnData = array();
        $this->_shopStatusCode = $status;
        $requestUrl = self::TR_ROUTE_TEST;  
        $result = $this->_getApiData($requestUrl, $methode, $sendData);
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }
    
    /**
     * [notifyApiKey description]
     * 
     * @param [type] $status   [description]
     * @param [type] $methode  [description]
     * @param [type] $sendData [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]           [description]
     */
    public function notifyApiKey($status,$methode,$sendData)
    {   
        $returnData = array();
        $this->_shopStatusCode = $status;
        $requestUrl = self::TR_ROUTE_NOTIFYAPI;  
        $result = $this->_getApiData($requestUrl, $methode, $sendData);
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }

    /**
     * [setShipmentInfo description]
     * 
     * @param [type] $status   [description]
     * @param [type] $sendData [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]           [description]
     */
    public function setShipmentInfo($status,$sendData)
    {   
        $returnData = array();
        $this->_shopStatusCode = $status;
        $requestUrl = self::TR_ROUTE_SETINFO;  
        $result = $this->_getApiData($requestUrl, 'GET', $sendData);
        if ($result) {
            $returnData = json_decode($result, true);
        }
        return $returnData;
    }
    
    /**
     * [getExtensionVersion description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public function getExtensionVersion()
    {
        return (string) Mage::getConfig()->getNode()->modules->Trackingmore_Detrack->version;
    }

}
     