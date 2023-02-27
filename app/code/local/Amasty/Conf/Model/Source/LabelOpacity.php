<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */
class Amasty_Conf_Model_Source_LabelOpacity extends Varien_Object
{
    public function toOptionArray()
    {
        $options = array();
        for ($i=10; $i >= 1; --$i) {
            $options[] =array('value' => $i,   'label' => $i / 10);
        }
        return $options;
    }

}
