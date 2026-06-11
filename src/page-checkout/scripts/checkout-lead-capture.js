/**
 * Lead capture on "Continuar al pago" — fire-and-forget.
 *
 * Watches step panel 2's visibility: the stepper only reveals it when step 1
 * validation passed, regardless of how the user navigated (button, Enter key
 * or step trigger). Never blocks or delays navigation: a failed capture is
 * logged and the checkout continues untouched. Captures once per page load;
 * a server-side failure re-arms it for the next attempt.
 */

import { sanitizeEmail } from '../../core/security/sanitizers.js';
import { email as isValidEmail } from '../../core/security/validators.js';
import { guardedFormPost } from '../../core/security/request-guard.js';

let captured = false;

function maybeCapture( ajaxUrl, nonce ) {
	if ( captured ) return;

	const emailField = document.getElementById( 'billing_email' );
	const emailValue = sanitizeEmail( emailField ? emailField.value : '' );
	if ( ! isValidEmail( emailValue ) ) return;

	captured = true;

	const formData = new FormData();
	formData.append( 'action', 'etheme_capture_checkout_lead' );
	formData.append( 'nonce', nonce );
	formData.append( 'email', emailValue );

	const nameField =
		document.getElementById( 'billing_first_name' ) ||
		document.getElementById( 'shipping_first_name' );
	if ( nameField && nameField.value ) {
		formData.append( 'name', nameField.value );
	}

	guardedFormPost( ajaxUrl, formData ).then( ( res ) => {
		if ( ! res.ok ) {
			captured = false;
			console.error( 'etheme lead capture failed:', res.message || res.status );
		}
	} );
}

export function initCheckoutLeadCapture() {
	const block = document.querySelector( '.page-checkout-block' );
	if ( ! block ) return;

	const nonce = block.dataset.leadNonce;
	const ajaxUrl = block.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';
	if ( ! nonce ) return;

	const panel2 = document.querySelector( '[data-checkout-step="2"]' );
	if ( ! panel2 ) return;

	// The stepper toggles `hidden` on the panel only after step 1 validates —
	// observing it covers every navigation path without coupling to validation.
	const observer = new MutationObserver( () => {
		if ( ! panel2.hasAttribute( 'hidden' ) ) {
			maybeCapture( ajaxUrl, nonce );
		}
	} );
	observer.observe( panel2, { attributes: true, attributeFilter: [ 'hidden' ] } );
}
