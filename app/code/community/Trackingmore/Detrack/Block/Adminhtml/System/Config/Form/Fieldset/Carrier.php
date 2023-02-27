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
class Trackingmore_Detrack_Block_Adminhtml_System_Config_Form_Fieldset_Carrier extends
    Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
    protected $_setFieldValue;
    protected $_sourceData;
    protected $_conceal = '';

    /**
     * [render description]
     * 
     * @param Varien_Data_Form_Element_Abstract $element [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]                                     [description]
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $adminCarrierTpl = $this->_getHeaderHtml($element);
        $carriers = Mage::getModel('detrack/carrier')->getList(true);
        $adminCarrierTpl .= '<tr><td class="label"></td><td>';
        $adminCarrierTpl .= $this->_getAdminHtmlCheckAllButton('$$(\'.tr_carrier\').forEach(function(el){el.writeAttribute(\'checked\', true)});', $this->helper('detrack')->__('Select All'));
        $adminCarrierTpl .= ' ';
        $adminCarrierTpl .= $this->_getAdminHtmlCheckAllButton('$$(\'.tr_carrier\').forEach(function(el){el.writeAttribute(\'checked\', false)});', $this->helper('detrack')->__('Unselect All'));
        $adminCarrierTpl .= '</td></tr>';

        $fields = '';
        foreach ($carriers as $carrier) {
            $fields .= $this->_getCarriersFieldHtml($element, $carrier);
        }
        $adminCarrierTpl .= $fields;
        $adminCarrierTpl .= $this->_getFooterHtml($element);

        return $this->_conceal . $adminCarrierTpl;
    }

    /**
     * [_getConfigFormFieldRender description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    protected function _getConfigFormFieldRender()
    {
        if (empty($this->_setFieldValue)) {
            $this->_setFieldValue = Mage::getBlockSingleton('adminhtml/system_config_form_field');
        }
        return $this->_setFieldValue;
    }

    /**
     * [_getValues description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type] [description]
     */
    protected function _getValues()
    {
        if (!$this->_sourceData) {
            $this->_sourceData = Mage::getSingleton('adminhtml/system_config_source_enabledisable')->toOptionArray();
        }

        return $this->_sourceData;
    }

    /**
     * [_getCarriersFieldHtml description]
     * 
     * @param [type] $fieldset [description]
     * @param [type] $carrier  [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]           [description]
     */
    protected function _getCarriersFieldHtml($fieldset, $carrier)
    {
        $name = 'groups[carriers][fields][' . $carrier->getCode() . '][value]';
        $this->_conceal .= '<input type="hidden" name="'. $name .'" value="0">';

        $field = $fieldset->addField(
            $carrier->getCode(), 'checkbox',
            array(
                'name' => $name,
                'label' => $carrier->getName(),
                'class' => 'tr_carrier',
                'value' => 1,
                'checked' => $carrier->getEnabled(),
                'can_use_default_value' => 0,
                'can_use_website_value' => 0,
            )
        )->setRenderer($this->_getConfigFormFieldRender());

        return $field->toHtml();
    }

    /**
     * [_getAdminHtmlCheckAllButton description]
     * 
     * @param [type] $action [description]
     * @param [type] $label  [description]
     * 
     * @author Trackingmore 2017-11-08
     * @return [type]         [description]
     */
    protected function _getAdminHtmlCheckAllButton($action, $label)
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'     => $label,
                'onclick'   => 'javascript:'. $action .'; return false;')
            );

        return $button->toHtml();
    }
}