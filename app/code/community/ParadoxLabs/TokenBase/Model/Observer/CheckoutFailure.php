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

class ParadoxLabs_TokenBase_Model_Observer_CheckoutFailure
{
	/**
	 * Check for any cards saved during a failed checkout. When this happens,
	 * the changes may be synced to the gateway, but Magento would roll back
	 * the database changes. Since we can't save it there, we register and do
	 * it here as needed.
	 */
	public function ensureCardSave( $observer )
	{
		try {
			$card = Mage::registry('tokenbase_ensure_checkout_card_save');
			
			if( $card instanceof ParadoxLabs_TokenBase_Model_Card && $card->getId() > 0 ) {
				$card->setNoSync( true )
					 ->save();
			}
		}
		catch( Exception $e ) {
			// Log and ignore any errors; we don't want to throw them in this context.
			Mage::helper('tokenbase')->log( $card ? $card->getMethod() : 'tokenbase', 'Checkout post-failure card save failed: ' . $e->getMessage() );
		}
	}
}
