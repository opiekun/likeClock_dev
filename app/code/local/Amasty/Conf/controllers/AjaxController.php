<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */

class Amasty_Conf_AjaxController extends Mage_Core_Controller_Front_Action
{
    protected function _initProduct()
    {
        Mage::dispatchEvent('catalog_controller_product_init_before', array('controller_action'=>$this));
        $productId  = (int) $this->getRequest()->getParam('id');

        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);

        if (!in_array(Mage::app()->getStore()->getWebsiteId(), $product->getWebsiteIds())) {
            return false;
        }

        Mage::register('current_product', $product);
        Mage::register('product', $product);
        /* fix for custom design
        $design = $product->getCustomDesign();
        $date   = $product->getCustomDesignDate();
        $this->_isApplyDesign($design, $date);*/

        try {
            Mage::dispatchEvent('catalog_controller_product_init', array('product'=>$product));
            Mage::dispatchEvent('catalog_controller_product_init_after', array('product'=>$product, 'controller_action' => $this));
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }
    }
    
    public function indexAction()
    {
        if (!Mage::app()->getRequest()->isAjax()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
        }
        else {
            $this->_initProduct();
            Mage::register('amconf_product_load', true);
            $this->loadLayout('catalog_product_view');
            //$this->loadLayout('default');  //fix fatal errors with 3-party extensions
            $block = Mage::app()->getLayout('catalog_product_view')->getBlock('product.info.media');
            $this->getResponse()->setBody($block->toHtml());
        }
        /*
        the second way
        $parentBlock = $this->getLayout()->createBlock('catalog/product_view', 'product.info');
        $template = 'amasty/amconf/media.phtml';
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('catalog/product_view_media', 'product.info.media')->setTemplate($template)->setParentBlock($parentBlock)->toHtml()
        ); 
        */
    }
    
    public function galleryAction()
    {
        $this->_initProduct();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function ajaxAction()
    {
        $_SERVER['REQUEST_URI'] = str_replace(Mage::getBaseUrl(), '/', $_SERVER['HTTP_REFERER']);
        $ids  = $this->getRequest()->getParam('ids');

        if (!$ids) {
            return false;
        }
        $ids = explode(':', $ids);

        $response = array();
        foreach($ids as $id){
            $_product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($id);
            if($_product->isSaleable() && $_product->isConfigurable()){
                $response[] = array(
                    'id'        => $id,
                    'onclick'   => Mage::helper('checkout/cart')->getAddUrl($_product),
                    'html'      => str_replace('class="amconf-block"', 'class="amconf-block" style="display: none;"', Mage::helper('amconf')->getHtmlBlock($_product, ''))
                );
            }
        }

        $this->getResponse()->setBody(
            Zend_Json::encode($response)
        );
    }

    protected function _apply($package, $theme)
    {
        Mage::getSingleton('core/design_package')
            ->setPackageName($package)
            ->setTheme($theme);
    }

    protected function _isApplyDesign($design, array $date)
    {
        if (!array_key_exists('from', $date) || !array_key_exists('to', $date)) {
            return false;
        }

        $designInfo = explode("/", $design);
        if (count($designInfo) != 2) {
            return false;
        }

        // define package and theme
        $package    = $designInfo[0];
        $theme      = $designInfo[1];

        // compare dates
        if (Mage::app()->getLocale()->isStoreDateInInterval(null, $date['from'], $date['to'])) {
            $this->_apply($package, $theme);
            return true;
        }

        return false;
    }
}
