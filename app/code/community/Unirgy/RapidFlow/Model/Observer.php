<?php

class Unirgy_RapidFlow_Model_Observer
{
    public function adminhtml_version($observer)
    {
        Mage::helper('urapidflow')->addAdminhtmlVersion('Unirgy_RapidFlow');
    }

    /**
     * Check for extension update news
     *
     * @param Varien_Event_Observer $observer
     */
    public function adminhtml_controller_action_predispatch(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('urapidflow/admin/notifications')) {
            try {
                Mage::getModel('urapidflow/feed')->checkUpdate();
            } catch(Exception $e) {
                // silently ignore
            }
        }
    }

    public function controller_action_layout_load_before(Varien_Event_Observer $observer)
    {
        /** @var Mage_Adminhtml_Controller_Action $action */
        $action = $observer->getAction();
        if ($action && $action->getFullActionName() === 'adminhtml_urapidflowadmin_profile_edit') {
            if (Mage::helper('urapidflow')->hasMageFeature('multiple_uploader')) {
                $action->getLayout()->getUpdate()->addHandle('_urf_multiple_uploader');
            }
        }

    }
}
