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
 * @package		AuthorizeNetCim
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_AuthorizeNetCim_Model_Observer_CheckoutFailure
{
	/**
	 * On order place failure, clear any now-invalid Accept.js tokens still outstanding.
	 * We can't do this during the transaction, becaues any changes would be rolled back.
	 */
	public function clearAcceptJs( $observer )
	{
		try {
			$this->_clearAcceptJsTokens( $observer->getEvent()->getOrder() );
			$this->_clearAcceptJsTokens( $observer->getEvent()->getQuote() );
		}
		catch( Exception $e ) {
			// Ignore any errors; we don't want to throw them in this context.
		}
	}
	
	/**
	 * Unset payment object values, to ensure they will not be reused.
	 */
	protected function _clearAcceptJsTokens( $object )
	{
		if( $object instanceof Varien_Object ) {
			$payment = $object->getPayment();
			
			if( $payment instanceof Varien_Object ) {
				$acceptJsKey	= $payment->getAdditionalInformation('acceptjs_key');
				$acceptJsValue	= $payment->getAdditionalInformation('acceptjs_value');
				
				if( !empty( $acceptJsKey ) || !empty( $acceptJsValue ) ) {
					$payment->setAdditionalInformation( 'acceptjs_key', null );
					$payment->setAdditionalInformation( 'acceptjs_value', null );
					
					if( $payment->getId() > 0 ) {
						$payment->save();
					}
				}
			}
		}
		
		return $this;
	}
}
