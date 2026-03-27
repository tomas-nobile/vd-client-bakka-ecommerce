/**
 * Frontend entrypoint for checkout page block.
 */

import { initCheckoutPaymentTiles } from '../scripts/payment-tiles.js';
import { initCheckoutShippingOptions } from '../scripts/shipping-options.js';
import { initCheckoutStepper } from '../scripts/checkout-stepper.js';
import { initCheckoutValidation } from '../scripts/checkout-validation.js';
import { initFadeUp } from '../../front-page/scripts/fp-fade-up.js';
import '../scripts/billing-sync.js';

document.addEventListener( 'DOMContentLoaded', function () {
	if ( document.querySelector( '.page-checkout-block' ) ) {
		document.body.classList.add( 'etheme-checkout-page' );
	}

	initCheckoutStepper();
	initCheckoutValidation();
	initCheckoutPaymentTiles();
	initCheckoutShippingOptions();
	initFadeUp( '.page-checkout-block' );
} );
