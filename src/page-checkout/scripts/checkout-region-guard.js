/**
 * Region guard — restricts checkout step 1 to allowed Argentine provinces.
 * Self-contained and easy to disable: comment out initCheckoutRegionGuard() in view.js.
 */

const ALLOWED = new Set( [ 'C', 'BA_GBA' ] );

let blocked = false;

/** @returns {boolean} Whether the current province selection is blocked. */
export function isRegionBlocked() {
	return blocked;
}

const sel = ( id ) => document.getElementById( id );

function getProvinceDisplay() {
	return sel( 'checkout-province-display' );
}

/**
 * Map custom province UI values to WooCommerce state codes for shipping/tax/rates.
 * Must stay in sync with etheme_checkout_remap_province() in functions.php.
 *
 * @param {string} displayValue
 * @returns {string}
 */
function toWooCommerceStateCode( displayValue ) {
	const map = {
		BA_GBA: 'B',
		BA_INTERIOR: 'B',
	};
	return Object.prototype.hasOwnProperty.call( map, displayValue )
		? map[ displayValue ]
		: displayValue;
}

function syncProvinceToHiddenState() {
	const display = getProvinceDisplay();
	if ( ! display ) return;
	const wcState = toWooCommerceStateCode( display.value );
	document.querySelectorAll( '[name="shipping_state"], [name="billing_state"]' ).forEach( ( hidden ) => {
		if ( ! hidden ) return;
		hidden.value = wcState;
		hidden.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	} );
}

function dispatchRegionChanged( isBlocked ) {
	document.dispatchEvent(
		new CustomEvent( 'etheme:regionChanged', { detail: { blocked: isBlocked } } )
	);
}

function setModalVisibility( visible ) {
	const modal = sel( 'checkout-region-modal' );
	if ( ! modal ) return;
	modal.hidden = ! visible;
	modal.setAttribute( 'aria-hidden', visible ? 'false' : 'true' );
	if ( visible ) {
		modal.querySelector( '.checkout-region-modal__close' )?.focus();
	}
}

function setAlertVisibility( visible ) {
	const alert = sel( 'checkout-region-alert' );
	if ( ! alert ) return;
	alert.hidden = ! visible;
	alert.setAttribute( 'aria-hidden', visible ? 'false' : 'true' );
}

function blockRegion() {
	blocked = true;
	const btn = sel( 'checkout-btn-continue' );
	if ( btn ) {
		btn.disabled = true;
		btn.setAttribute( 'aria-disabled', 'true' );
		btn.dataset.regionBlocked = '1';
	}
	setAlertVisibility( true );
	setModalVisibility( true );
	dispatchRegionChanged( true );
}

function unblockRegion() {
	blocked = false;
	const btn = sel( 'checkout-btn-continue' );
	if ( btn ) {
		delete btn.dataset.regionBlocked;
	}
	setAlertVisibility( false );
	dispatchRegionChanged( false );
}

function onProvinceChange() {
	const display = getProvinceDisplay();
	if ( ! display ) return;
	const value = display.value;
	if ( ! value || ALLOWED.has( value ) ) {
		unblockRegion();
	} else {
		blockRegion();
	}
}

function bindModalClose() {
	const modal = sel( 'checkout-region-modal' );
	if ( ! modal ) return;

	modal.querySelector( '.checkout-region-modal__close' )
		?.addEventListener( 'click', () => setModalVisibility( false ) );

	modal.querySelector( '.checkout-region-modal__backdrop' )
		?.addEventListener( 'click', () => setModalVisibility( false ) );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' && ! modal.hidden ) setModalVisibility( false );
	} );
}

function rehydrateAfterWcUpdate() {
	if ( ! window.jQuery ) return;
	window.jQuery( document.body ).on( 'updated_checkout', () => {
		syncProvinceToHiddenState();
	} );
}

export function initCheckoutRegionGuard() {
	const display = getProvinceDisplay();
	if ( ! display ) return;
	bindModalClose();
	rehydrateAfterWcUpdate();
	display.addEventListener( 'change', () => {
		syncProvinceToHiddenState();
		onProvinceChange();
	} );
	if ( display.value ) {
		syncProvinceToHiddenState();
		onProvinceChange();
	}
}
