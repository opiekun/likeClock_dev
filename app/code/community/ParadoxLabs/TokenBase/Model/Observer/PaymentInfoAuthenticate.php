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
 * @author		Ryan Hoerr / Ryan Hammett <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_TokenBase_Model_Observer_PaymentInfoAuthenticate
{
	/**
	 * Try to stop CC validation abuse by requiring a valid order before giving access to the 'My Payment Data' section.
	 *
	 * @param $observer
	 * @return void
	 */
	public function paymentInfoAuthenticate( $observer )
	{
		/** @var Mage_Core_Controller_Front_Action $action */
		$action = $observer->getEvent()->getData('controller_action');
		if( $action instanceof Mage_Core_Controller_Front_Action ) {
			$preventAccess = false;
			
			if( $this->_customerHasOrdered() === false ) {
				$preventAccess = true;
				
				Mage::getSingleton('core/session')->addError(
					Mage::helper('tokenbase')->__(
						'My Payment Data will be available after you\'ve placed an order.'
					)
				);
			}
			elseif( $this->_customerHasTooManyFailures() === true ) {
				$preventAccess = true;
				
				Mage::getSingleton('core/session')->addError(
					Mage::helper('tokenbase')->__(
						'My Payment Data is currently unavailable. Please try again later.'
					)
				);
			}
			
			if( $preventAccess === true ) {
				// No orders: prevent access
				$action->setFlag(
					'',
					Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH,
					true
				);
				
				/** @var Mage_Core_Controller_Response_Http $response */
				$response = $action->getResponse();
				$response->setRedirect( Mage::getUrl('customer/account') );
			}
		}
	}
	
	/**
	 * Determine whether the current logged-in customer has placed a valid order.
	 *
	 * @return bool
	 */
	protected function _customerHasOrdered()
	{
		// Allow this restriction to be turned off
		$active = Mage::app()->getStore()->getConfig('payment_services/tokenbase/paymentinfo_require_order');
		if( $active != 1 ) {
			return true;
		}
		
		$orderCount = Mage::getSingleton('customer/session')->getData('customer_order_count');
		
		if( empty( $orderCount ) ) {
			// Find an order belonging to this customer. Skip any that are canceled, refunded, held for review, etc.
			$orders = Mage::getModel('sales/order')->getCollection()
							->addAttributeToFilter( 'customer_id', Mage::getSingleton('customer/session')->getCustomerId() )
							->addAttributeToFilter( 'status', array( 'in' => array( 'processing', 'pending', 'complete' ) ) );
			
			$orderCount = $orders->getSize();
			
			Mage::getSingleton('customer/session')->setData( 'customer_order_count', $orderCount );
		}
		
		if( $orderCount > 0 ) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Determine whether the customer has more than the allowed number of recent failures.
	 *
	 * @return bool
	 */
	protected function _customerHasTooManyFailures()
	{
		$failures = Mage::getSingleton('customer/session')->getData('tokenbase_failures');
		
		// Number of failures to block after (default 5)
		$failureLimit = Mage::app()->getStore()->getConfig('payment_services/tokenbase/failure_limit');
		
		// Number of seconds to keep failures (default 86400, 1 day)
		$failureWindow = Mage::app()->getStore()->getConfig('payment_services/tokenbase/failure_window');
		
		if( is_array( $failures ) && count( $failures ) >= $failureLimit ) {
			$countInWindow = 0;
			foreach( $failures as $time => $message ) {
				if( $time > time() - $failureWindow ) {
					$countInWindow++;
				}
			}
			
			if( $countInWindow >= $failureLimit ) {
				return true;
			}
		}
		
		return false;
	}
}
