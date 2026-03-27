/**
 * Step 1 required-field validation for the 2-step checkout.
 * Enables/disables the "Continuar al pago" button in real time.
 */

const REQUIRED_FIELDS = [
	'billing_email',
	'shipping_first_name',
	'shipping_last_name',
	'shipping_address_1',
	'shipping_city',
	'shipping_postcode',
	'billing_phone',
];

function getFieldEl( name ) {
	return document.querySelector( `[name="${ name }"]` );
}

function isEmailValid( value ) {
	return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value );
}

function isFieldFilled( name ) {
	const el = getFieldEl( name );
	if ( ! el ) return false;
	const val = el.value.trim();
	if ( ! val ) return false;
	return name === 'billing_email' ? isEmailValid( val ) : true;
}

function allStep1Valid() {
	return REQUIRED_FIELDS.every( isFieldFilled );
}

function setContinueState( isValid ) {
	const btn = document.getElementById( 'checkout-btn-continue' );
	if ( ! btn ) return;
	btn.disabled = ! isValid;
	btn.setAttribute( 'aria-disabled', isValid ? 'false' : 'true' );
}

function onFieldChange() {
	setContinueState( allStep1Valid() );
}

function bindFieldListeners() {
	REQUIRED_FIELDS.forEach( ( name ) => {
		const el = getFieldEl( name );
		if ( ! el ) return;
		el.addEventListener( 'input', onFieldChange );
		el.addEventListener( 'change', onFieldChange );
	} );
}

function bindWcListeners() {
	if ( ! window.jQuery ) return;
	window.jQuery( document.body ).on(
		'country_to_state_changed updated_checkout',
		onFieldChange
	);
}

export function initCheckoutValidation() {
	if ( ! document.querySelector( '[data-checkout-step="1"]' ) ) return;
	bindFieldListeners();
	bindWcListeners();
	setContinueState( allStep1Valid() );
}
