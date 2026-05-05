/**
 * Removes WooCommerce's default submit button and legal text from #payment.
 * The custom checkout stepper owns placement of these elements — WC's copy
 * must be stripped to avoid duplicates.
 *
 * CSS hides them up-front to avoid the paint flash that occurred when WC
 * re-injected #payment via updated_checkout AJAX. JS removes them from the
 * DOM right after the event fires (no setTimeout — would let them paint).
 */
export function initPaymentCleanup() {
	function removePaymentPlaceOrder() {
		var payment = document.getElementById( 'payment' );
		if ( ! payment ) return;

		var placeOrder = payment.querySelector( '.form-row.place-order' ) || payment.querySelector( '.place-order' );
		if ( placeOrder ) placeOrder.remove();

		payment.querySelectorAll( '.woocommerce-terms-and-conditions-wrapper, .woocommerce-privacy-policy-text' ).forEach( function ( el ) {
			el.remove();
		} );

		payment.querySelectorAll( 'button#place_order, button.place_order' ).forEach( function ( el ) {
			el.remove();
		} );
	}

	removePaymentPlaceOrder();

	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'updated_checkout', removePaymentPlaceOrder );
	}
}
