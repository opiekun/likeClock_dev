<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

require_once Mage::getModuleDir('controllers','Mage_Catalog').DS.'CategoryController.php';

class Amasty_Shopby_CategoryController extends Mage_Catalog_CategoryController
{

    protected $category;

    protected function _initCatagory()
    {
        if (!Mage::registry('current_category')) {
            $this->category = parent::_initCatagory();
        }

        return $this->category;
    }

    public function viewAction()
    {
        $pageResource = Mage::getResourceModel('amshopby/page');
        $page = $pageResource->getCurrentMatchedPage($this->_initCatagory()->getId());
        if ($page) {
            Mage::register('amshopby_page', $page);
            $this->getLayout()->getUpdate()->addUpdate($page->getCustomLayoutUpdateXml());
        }
        parent::viewAction();
    }
}
