/**
 * Frontend entrypoint for checkout page block.
 */

import { initCheckoutPaymentTiles } from '../scripts/payment-tiles.js';
import { initCheckoutShippingOptions } from '../scripts/shipping-options.js';
import '../scripts/billing-sync.js';

document.addEventListener( 'DOMContentLoaded', function () {
	if ( document.querySelector( '.page-checkout-block' ) ) {
		document.body.classList.add( 'etheme-checkout-page' );
	}
	initCheckoutPaymentTiles();
	initCheckoutShippingOptions();
} );
