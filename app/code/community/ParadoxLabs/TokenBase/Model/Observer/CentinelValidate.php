<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 *
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 *
 * Want to customize or need help with your store?
 *  Phone: 717-431-3330
 *  Email: sales@paradoxlabs.com
 *
 * @category	ParadoxLabs
 * @package		TokenBase
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_TokenBase_Model_Observer_CentinelValidate extends Mage_Catalog_Model_Observer
{
    /**
     * When hitting the centinel validate request, update payment info on the instance before
     * checking availability. For some reason it doesn't do this until after.
     */
    public function updatePaymentInfo( $observer )
    {
        try {
            $paymentData = Mage::app()->getRequest()->getParam('payment');

            if (!empty($paymentData)) {
                $payment = Mage::getSingleton('adminhtml/sales_order_create')->getQuote()->getPayment();
                $payment->importData($paymentData);
            }
        } catch( Exception $e ) {
        }

        return $this;
    }
}
