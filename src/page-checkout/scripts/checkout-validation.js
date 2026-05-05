/**
 * Step 1 required-field validation for the 2-step checkout.
 *
 * Enables/disables the "Continuar al pago" button in real time and shows
 * specific per-field errors both inline and in a global summary banner.
 * Uses centralized security modules from src/core/security/.
 */

import { isRegionBlocked, isCurrentProvinceBlocked } from './checkout-region-guard.js';
import { sanitizeText, sanitizeEmail, sanitizeDigits } from '../../core/security/sanitizers.js';
import {
	required,
	email,
	phoneDigits,
	postcodeAR,
	personName,
} from '../../core/security/validators.js';
import { FIELD, FIELD_LABELS, CHECKOUT } from '../../core/security/messages.js';
import {
	showFieldError,
	clearFieldError,
	focusFirstInvalid,
} from '../../core/security/ui-feedback.js';

const REQUIRED_FIELDS_SHIPPING = [
	'billing_email',
	'shipping_first_name',
	'shipping_last_name',
	'shipping_address_1',
	'shipping_city',
	'shipping_postcode',
	'checkout_phone_area',
	'checkout_phone_number',
	'checkout-province-display',
];

const REQUIRED_FIELDS_BILLING_ONLY = [
	'billing_email',
	'billing_first_name',
	'billing_last_name',
	'billing_address_1',
	'billing_city',
	'billing_postcode',
	'checkout_phone_area',
	'checkout_phone_number',
	'checkout-province-display',
];

function getRequiredStep1Fields() {
	return document.querySelector( '[name="shipping_first_name"]' )
		? REQUIRED_FIELDS_SHIPPING
		: REQUIRED_FIELDS_BILLING_ONLY;
}

let hasStep1Interaction = false;

function getFieldEl( name ) {
	return document.querySelector( `[name="${ name }"], #${ name }` );
}

function getGlobalErrorEl() {
	return document.getElementById( 'checkout-step1-global-error' );
}

/**
 * Render a list of specific errors in the global banner.
 * Uses DOM methods only — no innerHTML.
 *
 * @param {{ label: string, message: string }[]} errors
 */
function renderGlobalErrors( errors ) {
	const el = getGlobalErrorEl();
	if ( ! el ) return;

	while ( el.firstChild ) {
		el.removeChild( el.firstChild );
	}

	if ( ! errors.length ) {
		el.hidden = true;
		return;
	}

	const intro = document.createElement( 'p' );
	intro.className = 'checkout-step1-global-error__intro';
	intro.textContent = CHECKOUT.step1Incomplete;
	el.appendChild( intro );

	const list = document.createElement( 'ul' );
	list.className = 'checkout-step1-global-error__list';

	errors.forEach( ( { label, message } ) => {
		const li = document.createElement( 'li' );
		const strong = document.createElement( 'strong' );
		strong.textContent = label + ': ';
		li.appendChild( strong );
		li.appendChild( document.createTextNode( message ) );
		list.appendChild( li );
	} );

	el.appendChild( list );
	el.hidden = false;
}

function clearGlobalStepError() {
	renderGlobalErrors( [] );
}

function isNameField( name ) {
	return (
		name === 'shipping_first_name' ||
		name === 'shipping_last_name' ||
		name === 'billing_first_name' ||
		name === 'billing_last_name'
	);
}

/**
 * Pure field error check. Returns { label, message } if invalid, null if valid.
 *
 * @param {string} name
 * @param {string} val Trimmed value.
 * @returns {{ label: string, message: string } | null}
 */
function getFieldError( name, val ) {
	const label = FIELD_LABELS[ name ] || name;

	if ( ! required( val ) ) return { label, message: FIELD.required };

	if ( name === 'billing_email' && ! email( sanitizeEmail( val ) ) ) {
		return { label, message: FIELD.emailInvalid };
	}

	if ( isNameField( name ) && ! personName( sanitizeText( val ) ) ) {
		return { label, message: FIELD.nameInvalid };
	}

	if ( ( name === 'shipping_postcode' || name === 'billing_postcode' ) && ! postcodeAR( sanitizeDigits( val ) ) ) {
		return { label, message: FIELD.postcodeInvalid };
	}

	if (
		( name === 'checkout_phone_area' || name === 'checkout_phone_number' ) &&
		! phoneDigits( sanitizeDigits( val ) )
	) {
		return { label, message: FIELD.phoneInvalid };
	}

	return null;
}

/**
 * Validate one field: clear + show inline error, return error info or null.
 *
 * @param {string} name
 * @returns {{ label: string, message: string } | null}
 */
function validateField( name ) {
	const el = getFieldEl( name );
	if ( ! el ) return null;
	const val = el.value.trim();
	clearFieldError( el );
	const error = getFieldError( name, val );
	if ( error ) showFieldError( el, error.message );
	return error;
}

/**
 * Collect errors from fields that already have a value (touched fields).
 * Skips truly empty fields so the banner doesn't flood on page load.
 *
 * @returns {{ label: string, message: string }[]}
 */
function collectFilledErrors() {
	return getRequiredStep1Fields().reduce( ( acc, name ) => {
		const el = getFieldEl( name );
		if ( ! el ) return acc;
		const val = el.value.trim();
		if ( ! val ) return acc;
		const error = getFieldError( name, val );
		if ( error ) acc.push( error );
		return acc;
	}, [] );
}

function allStep1Valid() {
	if ( isRegionBlocked() || isCurrentProvinceBlocked() ) return false;
	return getRequiredStep1Fields().every( ( name ) => {
		const el = getFieldEl( name );
		if ( ! el ) return false;
		const val = el.value.trim();
		if ( ! required( val ) ) return false;
		if ( name === 'billing_email' ) return email( sanitizeEmail( val ) );
		if ( isNameField( name ) ) return personName( sanitizeText( val ) );
		if ( name === 'shipping_postcode' || name === 'billing_postcode' ) {
			return postcodeAR( sanitizeDigits( val ) );
		}
		if ( name === 'checkout_phone_area' || name === 'checkout_phone_number' ) {
			return phoneDigits( sanitizeDigits( val ) );
		}
		return true;
	} );
}

/**
 * Full validation pass: show all inline errors + specific global list.
 * Called when the user attempts to advance to step 2.
 *
 * @returns {boolean}
 */
export function runStep1Validation() {
	const step = document.querySelector( '[data-checkout-step="1"]' );
	if ( ! step ) return true;

	if ( isRegionBlocked() || isCurrentProvinceBlocked() ) {
		clearGlobalStepError();
		return false;
	}

	const errors = getRequiredStep1Fields().reduce( ( acc, name ) => {
		const error = validateField( name );
		if ( error ) acc.push( error );
		return acc;
	}, [] );

	renderGlobalErrors( errors );

	if ( errors.length ) {
		focusFirstInvalid( step );
		return false;
	}

	return true;
}

function setContinueState( isValid ) {
	const btn = document.getElementById( 'checkout-btn-continue' );
	if ( ! btn ) return;
	if ( isRegionBlocked() || isCurrentProvinceBlocked() ) {
		btn.disabled = true;
		btn.setAttribute( 'aria-disabled', 'true' );
		return;
	}
	btn.disabled = ! isValid;
	btn.setAttribute( 'aria-disabled', isValid ? 'false' : 'true' );
}

/**
 * @param {string|undefined} changedName The field name that triggered the change.
 */
function onFieldChange( changedName ) {
	hasStep1Interaction = true;

	if ( changedName ) validateField( changedName );

	const isValid = allStep1Valid();
	setContinueState( isValid );

	if ( isRegionBlocked() ) {
		clearGlobalStepError();
		return;
	}

	renderGlobalErrors( isValid ? [] : collectFilledErrors() );
}

function bindFieldListeners() {
	getRequiredStep1Fields().forEach( ( name ) => {
		const el = getFieldEl( name );
		if ( ! el ) return;
		el.addEventListener( 'input', () => onFieldChange( name ) );
		el.addEventListener( 'change', () => onFieldChange( name ) );
	} );
}

function bindWcListeners() {
	if ( ! window.jQuery ) return;
	window.jQuery( document.body ).on(
		'country_to_state_changed updated_checkout',
		() => onFieldChange( undefined )
	);
}

function bindRegionListener() {
	document.addEventListener( 'etheme:regionChanged', () => onFieldChange( undefined ) );
}

export function initCheckoutValidation() {
	if ( ! document.querySelector( '[data-checkout-step="1"]' ) ) return;
	hasStep1Interaction = false;
	clearGlobalStepError();
	bindFieldListeners();
	bindWcListeners();
	bindRegionListener();
	setContinueState( allStep1Valid() );
}
