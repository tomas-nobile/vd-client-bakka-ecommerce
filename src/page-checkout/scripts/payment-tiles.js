/**
 * Keep payment tiles and boxes in sync with selected radio input.
 * Hide duplicate "Place order" buttons inside payment (e.g. Mercado Pago).
 */
let hasUserSelectedPayment = false;

function syncPaymentTiles( root ) {
	const paymentItems = root.querySelectorAll( '.wc_payment_method' );

	paymentItems.forEach( ( item ) => {
		const input = item.querySelector( 'input[name="payment_method"]' );
		const tile = item.querySelector( '[data-payment-tile]' );
		const box = item.querySelector( '.payment_box' );
		const checked = Boolean( input && input.checked );

		if ( tile ) {
			tile.classList.toggle( 'is-selected', checked );
		}

		if ( box ) {
			box.style.display = checked ? '' : 'none';
		}
	} );
}

/**
 * Remove default auto-selection until the user chooses a method.
 *
 * @param {HTMLElement} root Payment root.
 */
function clearDefaultPaymentSelection( root ) {
	if ( ! root || hasUserSelectedPayment ) {
		return;
	}

	const inputs = root.querySelectorAll( 'input[name="payment_method"]' );
	inputs.forEach( ( input ) => {
		input.checked = false;
	} );

	const boxes = root.querySelectorAll( '.payment_box' );
	boxes.forEach( ( box ) => {
		box.style.display = 'none';
	} );
}

/**
 * Hide any submit / place order button inside the payment section (Mercado Pago, etc.).
 */
function hideDuplicatePlaceOrderButtons() {
	const paymentSection = document.querySelector( '.page-checkout-block #payment' );
	if ( ! paymentSection ) {
		return;
	}

	const buttons = paymentSection.querySelectorAll(
		'button[type="submit"], input[type="submit"], .place_order'
	);
	buttons.forEach( ( btn ) => {
		btn.style.setProperty( 'display', 'none', 'important' );
	} );
}

export function initCheckoutPaymentTiles() {
	const initialPaymentRoot = document.querySelector( '.page-checkout-block #payment' );
	if ( ! initialPaymentRoot ) {
		return;
	}

	syncPaymentTiles( initialPaymentRoot );
	clearDefaultPaymentSelection( initialPaymentRoot );
	hideDuplicatePlaceOrderButtons();

	document.addEventListener( 'change', function ( event ) {
		if ( event.target.matches( 'input[name="payment_method"]' ) ) {
			hasUserSelectedPayment = true;
			const paymentRoot = document.querySelector( '.page-checkout-block #payment' );
			if ( paymentRoot ) {
				syncPaymentTiles( paymentRoot );
			}
		}
	} );

	// Mercado Pago (and others) may inject the button after load; watch for new nodes
	const observer = new MutationObserver( function () {
		const paymentRoot = document.querySelector( '.page-checkout-block #payment' );
		if ( paymentRoot ) {
			syncPaymentTiles( paymentRoot );
			clearDefaultPaymentSelection( paymentRoot );
		}
		hideDuplicatePlaceOrderButtons();
	} );
	observer.observe( initialPaymentRoot, { childList: true, subtree: true } );

	if ( window.jQuery ) {
		window.jQuery( document.body ).on( 'updated_checkout', function () {
			const paymentRoot = document.querySelector( '.page-checkout-block #payment' );
			if ( ! paymentRoot ) {
				return;
			}
			syncPaymentTiles( paymentRoot );
			clearDefaultPaymentSelection( paymentRoot );
			hideDuplicatePlaceOrderButtons();
		} );
	}
}
