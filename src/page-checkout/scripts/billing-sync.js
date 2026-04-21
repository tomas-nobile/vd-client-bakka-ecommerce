/**
 * Billing address synchronization script.
 * Synchronizes shipping fields with hidden billing fields.
 * Composes billing_phone from split checkout_phone_area + checkout_phone_number inputs.
 */

(function() {
	'use strict';

	let isSyncing = false;

	const FIELD_MAPPING = {
		'shipping_first_name': 'billing_first_name',
		'shipping_last_name': 'billing_last_name',
		'shipping_company': 'billing_company',
		'shipping_country': 'billing_country',
		'shipping_address_1': 'billing_address_1',
		'shipping_address_2': 'billing_address_2',
		'shipping_city': 'billing_city',
		'shipping_state': 'billing_state',
		'shipping_postcode': 'billing_postcode',
	};

	function getFieldValue( fieldName ) {
		const field = document.querySelector( `[name="${ fieldName }"]` );
		return field ? ( field.value || '' ) : '';
	}

	function setFieldValue( fieldName, value, contextEl ) {
		const scope = contextEl || document;
		const field = scope.querySelector( `[name="${ fieldName }"]` );
		if ( ! field || field.value === value ) return;
		field.value = value;
		if ( ! isSyncing ) {
			field.dispatchEvent( new Event( 'change', { bubbles: true } ) );
		}
	}

	function composePhoneValue() {
		const area = getFieldValue( 'checkout_phone_area' ).replace( /\D/g, '' );
		const num  = getFieldValue( 'checkout_phone_number' ).replace( /\D/g, '' );
		return area + num;
	}

	function syncPhoneToHiddenField( syncRoot ) {
		const phone = composePhoneValue();
		setFieldValue( 'billing_phone', phone, syncRoot );
	}

	function syncAllFields() {
		if ( isSyncing ) return;
		isSyncing = true;
		try {
			const syncRoot = document.getElementById( 'billing-address-sync' );
			const hasShippingNames = document.querySelector( '[name="shipping_first_name"]' );
			if ( hasShippingNames ) {
				Object.entries( FIELD_MAPPING ).forEach( ( [ src, dest ] ) => {
					setFieldValue( dest, getFieldValue( src ), syncRoot );
				} );
			}
			syncPhoneToHiddenField( syncRoot );
		} finally {
			isSyncing = false;
		}
	}

	function debounce( fn, ms ) {
		let id;
		return () => { clearTimeout( id ); id = setTimeout( fn, ms ); };
	}

	function bindFieldListeners() {
		const debouncedSync = debounce( syncAllFields, 50 );
		const allFields = [
			...Object.keys( FIELD_MAPPING ),
			'checkout_phone_area',
			'checkout_phone_number',
			'billing_email',
		];
		allFields.forEach( ( name ) => {
			const el = document.querySelector( `[name="${ name }"]` );
			if ( ! el ) return;
			el.addEventListener( 'change', debouncedSync );
			el.addEventListener( 'blur', debouncedSync );
			el.addEventListener( 'input', debouncedSync );
		} );
	}

	function bindFormSubmit() {
		const form = document.querySelector( 'form.checkout' );
		if ( form ) form.addEventListener( 'submit', syncAllFields );
	}

	function bindWcUpdate() {
		if ( ! window.jQuery ) return;
		window.jQuery( document.body ).on( 'updated_checkout', () => {
			setTimeout( syncAllFields, 200 );
		} );
	}

	function initSync() {
		setTimeout( syncAllFields, 500 );
		bindFieldListeners();
		bindFormSubmit();
		bindWcUpdate();
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', initSync );
	} else {
		initSync();
	}
	window.addEventListener( 'load', initSync );
	setTimeout( initSync, 1000 );
})();
