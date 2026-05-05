/**
 * Frontend entrypoint for checkout page block.
 */

import { initCheckoutPaymentTiles } from '../scripts/payment-tiles.js';
import { initCheckoutShippingOptions } from '../scripts/shipping-options.js';
import { initCheckoutStepper } from '../scripts/checkout-stepper.js';
import { initCheckoutValidation } from '../scripts/checkout-validation.js';
import { initCheckoutRegionGuard } from '../scripts/checkout-region-guard.js';
import { initCheckoutLegalModal } from '../scripts/checkout-legal-modal.js';
import { initPaymentCleanup } from '../scripts/payment-cleanup.js';
import { initFadeUp } from '../../front-page/scripts/fp-fade-up.js';
import '../scripts/billing-sync.js';

document.addEventListener( 'DOMContentLoaded', function () {
	if ( ! document.querySelector( '.page-checkout-block' ) ) {
		return;
	}

	document.body.classList.add( 'etheme-checkout-page' );

	// WooCommerce's $.scroll_to_notices animates html/body scrollTop with a 1s
	// jQuery animation after every update_order_review. On initial page load
	// the AJAX fires automatically and this animation collides with the user's
	// own scroll, producing a flash that looks like a browser notification
	// dropping in. Replace with a no-op — manual scroll is always preferable;
	// real submission errors still surface via the notice elements themselves.
	if ( window.jQuery ) {
		const $ = window.jQuery;

		if ( typeof $.scroll_to_notices === 'function' ) {
			$.scroll_to_notices = function () {};
		}

		let hasFormInteraction = false;
		const checkoutForm = document.querySelector( 'form.checkout' );
		if ( checkoutForm ) {
			checkoutForm.addEventListener( 'input', () => { hasFormInteraction = true; }, { once: true } );
			checkoutForm.addEventListener( 'change', () => { hasFormInteraction = true; }, { once: true } );
		}

		$( document.body ).on( 'updated_checkout', function () {
			// Re-stomp in case WC re-defined scroll_to_notices late.
			$.scroll_to_notices = function () {};
			// Cancel any animation already in flight (belt-and-suspenders).
			$( 'html, body' ).stop( true );
			if ( ! hasFormInteraction ) {
				$( '.woocommerce-NoticeGroup-updateOrderReview' ).remove();
			}
		} );
	}

	initCheckoutStepper();
	initCheckoutRegionGuard();
	initCheckoutValidation();
	initCheckoutPaymentTiles();
	initCheckoutShippingOptions();
	initCheckoutLegalModal();
	initPaymentCleanup();
	initFadeUp( '.page-checkout-block' );
} );
