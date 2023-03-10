<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_RapidFlow
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

class Unirgy_RapidFlow_Block_Adminhtml_Profile_Edit_Tab_Upload
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('urapidflow/upload.phtml');
    }

    protected function _prepareLayout()
    {
        //  checking for new media uploader SUPEE-8878
        if(Mage::helper('urapidflow')->hasMageFeature('multiple_uploader')){
            return $this->_prepareLayout1930();
        }
        return $this->_prepareLayout1923();
    }
    protected function _prepareLayout1923()
    {
        $this->setChild('uploader',
            $this->getLayout()->createBlock('adminhtml/media_uploader')
        );

        $this->getUploader()->getConfig()
            ->setFileField('file')
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/upload', $this->_params()))
            ->setFilters(array(
                'csv' => array(
                    'label' => Mage::helper('adminhtml')->__('CSV and Tab Separated files (.csv, .txt)'),
                    'files' => array('*.csv', '*.txt')
                ),
                'all'    => array(
                    'label' => Mage::helper('adminhtml')->__('All Files'),
                    'files' => array('*.*')
                )
            ));

        return Mage_Adminhtml_Block_Widget::_prepareLayout();
    }

    protected function _prepareLayout1930()
    {
        /*
            <action method="addJs"><name>lib/uploader/flow.min.js</name></action>
            <action method="addJs"><name>lib/uploader/fusty-flow.js</name></action>
            <action method="addJs"><name>lib/uploader/fusty-flow-factory.js</name></action>
            <action method="addJs"><name>mage/adminhtml/uploader/instance.js</name></action>
         */
            $this->setChild('uploader',
                $this->getLayout()->createBlock($this->_uploaderType)
            );
            $this->getUploader()->getUploaderConfig()
                 ->setFileParameterName('file')
                 ->setTarget(Mage::getModel('adminhtml/url')->addSessionParam()->getUrl('*/*/upload', $this->_params()));

            $browseConfig = $this->getUploader()->getButtonConfig();
            $browseConfig
                ->setAttributes(array(
                    'accept' => $browseConfig->getMimeTypesByExtensions('csv, txt')
                ));

            Mage::dispatchEvent('urapidflow_tab_upload_prepare_layout', array('block' => $this));

            return $this;
    }

    /**
     * @return array
     */
    protected function _params()
    {
        /** @var Unirgy_RapidFlow_Model_Profile $profile */
        $profile = $this->getData('profile');

        return $profile? array('id' => $profile->getId()): array();
    }
}
