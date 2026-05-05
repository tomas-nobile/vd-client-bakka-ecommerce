/**
 * Keep payment tiles and boxes in sync with selected radio input.
 * Hide duplicate "Place order" buttons inside payment (e.g. Mercado Pago).
 */
let hasUserSelectedPayment = false;

function getPlaceOrderBtn() {
	return document.querySelector( '.page-checkout-block #place_order' );
}

function setPlaceOrderEnabled( enabled ) {
	const btn = getPlaceOrderBtn();
	if ( ! btn ) {
		return;
	}
	btn.disabled = ! enabled;
	if ( enabled ) {
		btn.removeAttribute( 'aria-disabled' );
	} else {
		btn.setAttribute( 'aria-disabled', 'true' );
	}
}

function hasAnyPaymentMethodChecked( root ) {
	const inputs = root.querySelectorAll( 'input[name="payment_method"]' );
	for ( const input of inputs ) {
		if ( input.checked ) {
			return true;
		}
	}
	return false;
}

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

		item.classList.toggle( 'is-selected', checked );

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
		'.form-row.place-order, button#place_order, input#place_order'
	);
	buttons.forEach( ( btn ) => {
		btn.style.setProperty( 'display', 'none', 'important' );
	} );
}

/**
 * MercadoPago renders sensitive card fields (number, expiry, CVV) as secure
 * iframes hosted at secure-fields.mercadopago.com. The visible "input" is
 * <div class="mp-checkout-custom-card-input"> wrapping <iframe name="X">,
 * and a parallel <input id="X" name="X"> lives elsewhere in the DOM. The
 * SDK forwards keystrokes from that parallel input to the iframe — so
 * focus must land on the parallel input, not on the iframe.
 *
 * Clicks on the iframe area work natively (cross-origin click → iframe
 * focuses internally). The problem is clicks on the visible input area
 * OUTSIDE the iframe (the wrapper's right padding, brand-icon area, or
 * any sibling element MP renders alongside the iframe). Those clicks land
 * on the wrapper or sibling elements, never on the parallel input.
 *
 * This handler walks up from the click target and at each ancestor level
 * collects candidate fields (MP secure containers + regular text inputs).
 * If multiple candidates exist at the same level (e.g. cardNumber and
 * cardholderName in the same row), it picks the one geometrically closest
 * to the click — so clicks on the far right of the cardNumber visual box
 * still focus cardNumber, not the cardholder field that happens to be in
 * the same parent.
 *
 * Listener is on document (survives WC AJAX re-renders) and runs in
 * capture phase (wins against MP SDK stopPropagation).
 */
function initCardBrandClickthrough() {
	const findInputByName = ( name ) => document.querySelector(
		`input#${ CSS.escape( name ) }:not([type="hidden"]), input[name="${ name }"]:not([type="hidden"])`
	);

	document.addEventListener( 'click', function ( e ) {
		const payBox = e.target.closest( '.page-checkout-block #payment .payment_box' );
		if ( ! payBox ) return;
		if ( e.target.closest( 'input, button, select, textarea, a' ) ) return;

		const MP_SECURE_CONTAINER = '.mp-checkout-custom-card-input, .mp-checkout-custom-security-code-input';

		const focusCandidate = ( c ) => {
			if ( ! c ) return false;
			if ( c.matches( MP_SECURE_CONTAINER ) ) {
				const iframe = c.querySelector( 'iframe[name]' );
				if ( iframe ) {
					// Prefer the parallel <input> the SDK uses for keystroke forwarding
					const target = findInputByName( iframe.getAttribute( 'name' ) );
					if ( target ) { target.focus(); return true; }
					// No parallel input in DOM — focus the iframe directly. focus() on
					// contentWindow is allowed cross-origin (only access is restricted)
					// and lets MP's secure-fields page receive focus inside the iframe.
					iframe.focus();
					try { iframe.contentWindow?.focus(); } catch ( err ) {}
					return true;
				}
				if ( c.tagName === 'INPUT' ) { c.focus(); return true; }
				return false;
			}
			c.focus();
			return true;
		};

		const pickClosest = ( candidates ) => {
			if ( candidates.length === 0 ) return null;
			if ( candidates.length === 1 ) return candidates[ 0 ];
			let best = null, minDist = Infinity;
			for ( const c of candidates ) {
				const r = c.getBoundingClientRect();
				const d = Math.hypot(
					e.clientX - ( r.left + r.right ) / 2,
					e.clientY - ( r.top + r.bottom ) / 2
				);
				if ( d < minDist ) { minDist = d; best = c; }
			}
			return best;
		};

		const SELECTOR =
			MP_SECURE_CONTAINER + ',' +
			' input:not([type="hidden"]):not([type="radio"]):not([type="checkbox"]):not([type="submit"])';

		let node = e.target;
		while ( node && node !== payBox.parentElement ) {
			// Include node itself if it matches (querySelectorAll only finds descendants)
			const selfMatch = node.matches?.( SELECTOR ) ? [ node ] : [];
			const descendantMatches = Array.from( node.querySelectorAll?.( SELECTOR ) || [] );
			const candidates = [ ...selfMatch, ...descendantMatches ];

			if ( focusCandidate( pickClosest( candidates ) ) ) return;
			node = node.parentElement;
		}
	}, true );
}

export function initCheckoutPaymentTiles() {
	const initialPaymentRoot = document.querySelector( '.page-checkout-block #payment' );
	if ( ! initialPaymentRoot ) {
		return;
	}

	clearDefaultPaymentSelection( initialPaymentRoot );
	syncPaymentTiles( initialPaymentRoot );
	hideDuplicatePlaceOrderButtons();
	initCardBrandClickthrough();
	setPlaceOrderEnabled( hasAnyPaymentMethodChecked( initialPaymentRoot ) );

	document.addEventListener( 'change', function ( event ) {
		if ( event.target.matches( 'input[name="payment_method"]' ) ) {
			hasUserSelectedPayment = true;
			const paymentRoot = document.querySelector( '.page-checkout-block #payment' );
			if ( paymentRoot ) {
				syncPaymentTiles( paymentRoot );
				setPlaceOrderEnabled( hasAnyPaymentMethodChecked( paymentRoot ) );
			}
		}
	} );

	// Mercado Pago (and others) may inject the button after load; watch for new nodes
	const observer = new MutationObserver( function () {
		const paymentRoot = document.querySelector( '.page-checkout-block #payment' );
		if ( paymentRoot ) {
			clearDefaultPaymentSelection( paymentRoot );
			syncPaymentTiles( paymentRoot );
			setPlaceOrderEnabled( hasAnyPaymentMethodChecked( paymentRoot ) );
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
			clearDefaultPaymentSelection( paymentRoot );
			syncPaymentTiles( paymentRoot );
			hideDuplicatePlaceOrderButtons();
			setPlaceOrderEnabled( hasAnyPaymentMethodChecked( paymentRoot ) );
		} );
	}
}
