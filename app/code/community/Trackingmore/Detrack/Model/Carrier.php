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
class Trackingmore_Detrack_Model_Carrier extends Mage_Core_Model_Abstract
{
 
    const API_UPDATE_TIMELENGTH = 86400;
    const CODE_PREFIX = 'tr_';
    /**
     * [construct description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    protected function _construct() 
    { 
        $this->_init('detrack/carrier'); 
    }
    
    /**
     * [getList description]
     * 
     * @param boolean $fetchAll [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]            [description]
     */
    public function getList($fetchAll = false)
    { 
        $carrierCollect = Mage::getModel('detrack/carrier')
            ->getCollection();
        if (!$fetchAll) {
            $carrierCollect->addFieldToFilter('enabled', array('eq' => 1));
        }
        $carrierData = $carrierCollect->getItems();
        $config  = Mage::getStoreConfig('tr_section_setttings/settings');
        $updated = !empty($config['last_carrier_update']) ? 
        $config['last_carrier_update']:0;
        if (!$carrierData 
            || !$updated 
            || $updated + self::API_UPDATE_TIMELENGTH < time()
        ) {
            $helper = Mage::helper('detrack');
            $res = $helper->getCarrierList();
            if ($res && $res["statusCode"] == 200) {
                $list = $res["body"];
                if (is_array($list)) {
                    $newList = array();
                    $oldList = array();
                    foreach ($carrierData as $rowData) {
                        $oldList[$rowData->code] = $rowData;
                    }
                    foreach ($list as $rowData) {
                        $carrierMd = Mage::getModel('detrack/carrier')
                            ->load($rowData['code'], 'code');

                        if ($carrierMd->getId()) {
                            if (isset($oldList[$rowData['code']])) {
                                unset($oldList[$rowData['code']]);
                            }
                        } else {
                            $carrierMd->enabled = 1;
                        }
						
						$firstStr = substr($rowData['code'],0,1);
						
						
						if(in_array($firstStr,array('0','1','2','3','4','5','6','7','8','9'))){
							$carrierMd->code = 'tm_'.$rowData['code'];
						}else{
							$carrierMd->code = $rowData['code'];
						}
						
                        $carrierMd->name = $rowData['name'];
                        $carrierMd->phone = $rowData['phone'];
                        $carrierMd->homepage = $rowData['homepage'];

                        $carrierMd->save();

                        if ($fetchAll || $carrierMd->enabled) {
                            $newList[] = $carrierMd;
                        }
                    }
                    foreach ($oldList as $carrierMd) {
                        $carrierMd->delete();
                    }
                    Mage::getModel('core/config')->saveConfig(
                        'tr_section_setttings/settings/last_carrier_update',
                        time()
                    );
					
                    $carrierData = $newList;
                }
            }
        }
        return $carrierData;
    }

    /**
     * [getPrefixedCode description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public function getPrefixedCode()
    {
        return self::CODE_PREFIX . $this->getCode();
    }
}
