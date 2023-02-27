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
class Trackingmore_Detrack_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * [indexAction description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public function indexAction()
    {
        $modelData = Mage::getModel('detrack/track');
        $hashData = Mage::app()->getRequest()->getParam('h');
        if ($hashData) {
            $modelData->loadInfoByHash($hashData);
        }
        Mage::register('model', $modelData);
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Shipment status"));
        $secondDescription = $this->getLayout()->getBlock("breadcrumbs");
        $secondDescription->addCrumb(
            "home",
            array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link" => Mage::getBaseUrl()
            )
        );
        $secondDescription->addCrumb(
            "shipment status",
            array(
                "label" => $this->__("Shipment status"),
                "title" => $this->__("Shipment status")
            )
        );
        $this->renderLayout();
    }

    /**
     * [popupAction description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    public function popupAction()
    {  
        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Shipment status"));
        $this->renderLayout();
    }
}