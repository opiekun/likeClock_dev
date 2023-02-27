<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Model_Source_CountOfCarouselItems extends Varien_Object
{
    public function toOptionArray()
    {
        $hlp = Mage::helper('amconf');
        return array(
            array('value' => '1', 'label' => $hlp->__('One')),
            array('value' => '2', 'label' => $hlp->__('Two')),
            array('value' => '3', 'label' => $hlp->__('Three')),
            array('value' => '4', 'label' => $hlp->__('Four')),
            array('value' => '5', 'label' => $hlp->__('Five')),
        );
    }
}
