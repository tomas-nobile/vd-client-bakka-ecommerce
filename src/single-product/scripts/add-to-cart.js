/**
 * Add to Cart Script
 *
 * Handles add to cart via WooCommerce AJAX endpoint, quantity controls, and visual feedback.
 */

export function initAddToCart() {
	const button = document.getElementById( 'add-to-cart-button' );
	const form = document.getElementById( 'add-to-cart-form' ) || ( button ? button.form : null );

	if ( ! button ) {
		return;
	}

	// Initialize quantity controls
	initQuantity();

	// Handle form submission (skip variable product form)
	if ( form && ! form.classList.contains( 'variations_form' ) ) {
		form.addEventListener( 'submit', async function ( e ) {
			// Buy-now uses formaction + native submit so the PHP redirect filter fires.
			if ( e.submitter && e.submitter.id === 'buy-now-button' ) {
				return;
			}

			e.preventDefault();

			const buttonText = button.querySelector( '.button-text' );
			const addingText = button.dataset.addingText || 'Agregando...';
			const addedText  = button.dataset.addedText  || 'Agregado';
			const addText    = button.dataset.addText    || 'Agregar al carrito';

			if ( buttonText ) {
				buttonText.textContent = addingText;
			}
			button.disabled = true;

			const block     = document.querySelector( '.wp-block-etheme-single-product-index' );
			const wcAjaxUrl = block?.dataset.wcAddToCartUrl || ( window.location.origin + '/?wc-ajax=add_to_cart' );
			const productId = button.value;
			const qtyInput  = document.getElementById( 'quantity' );
			const quantity  = qtyInput ? qtyInput.value : '1';

			const formData = new FormData();
			// Only send 'product_id', not 'add-to-cart': WC_Form_Handler::add_to_cart_action()
			// runs on wp_loaded and would add a second item if 'add-to-cart' is in the POST body.
			formData.append( 'product_id', productId );
			formData.append( 'quantity', quantity );

			try {
				const response = await fetch( wcAjaxUrl, { method: 'POST', body: formData } );
				const data     = await response.json();

				if ( ! data.error ) {
					if ( buttonText ) {
						buttonText.textContent = addedText;
					}
					button.classList.add( 'bg-green-600' );
					button.classList.remove( 'bg-[#2b5756]' );
					updateNavbarCount( parseInt( quantity, 10 ) || 1 );

					setTimeout( () => {
						if ( buttonText ) {
							buttonText.textContent = addText;
						}
						button.disabled = false;
						button.classList.remove( 'bg-green-600' );
						button.classList.add( 'bg-[#2b5756]' );
					}, 2000 );
				} else {
					if ( buttonText ) {
						buttonText.textContent = addText;
					}
					button.disabled = false;
				}
			} catch {
				if ( buttonText ) {
					buttonText.textContent = addText;
				}
				button.disabled = false;
			}
		} );
	}
}

/**
 * Increment navbar cart badge by the given quantity.
 *
 * @param {number} qty Items just added.
 */
function updateNavbarCount( qty ) {
	const badges  = document.querySelectorAll( '.etheme-navbar-action__badge' );
	const current = badges[0] ? ( parseInt( badges[0].textContent, 10 ) || 0 ) : 0;
	const newCount = current + qty;

	try {
		sessionStorage.setItem( 'etheme_cart_count', String( newCount ) );
	} catch {}

	badges.forEach( ( el ) => {
		el.textContent = String( newCount );
		el.classList.toggle( 'etheme-navbar-action__badge--visible', newCount > 0 );
	} );

	const cartBadgeEl = document.querySelector( '.cart-badge' );
	if ( cartBadgeEl ) {
		cartBadgeEl.textContent = String( newCount );
		cartBadgeEl.classList.toggle( 'hidden', newCount <= 0 );
	}
}

/**
 * Initialize MercadoLibre-style quantity dropdown
 */
function initQuantity() {
	const selector    = document.querySelector( '.quantity-ml-selector' );
	const hiddenInput = document.getElementById( 'quantity' );

	if ( ! selector || ! hiddenInput ) {
		return;
	}

	const trigger     = selector.querySelector( '.quantity-ml-trigger' );
	const dropdown    = selector.querySelector( '.quantity-ml-dropdown' );
	const display     = selector.querySelector( '.quantity-ml-display' );
	const chevron     = selector.querySelector( '.quantity-ml-chevron' );
	const customPanel = selector.querySelector( '.quantity-ml-custom' );
	const customInput = selector.querySelector( '.quantity-ml-custom-input' );
	const cancelBtn   = selector.querySelector( '.quantity-ml-custom-cancel' );

	if ( ! trigger || ! dropdown ) {
		return;
	}

	const limit = parseInt( hiddenInput.dataset.limit, 10 ) || 6;
	const max   = hiddenInput.dataset.max ? parseInt( hiddenInput.dataset.max, 10 ) : Infinity;

	function openDropdown() {
		dropdown.classList.remove( 'hidden' );
		trigger.setAttribute( 'aria-expanded', 'true' );
		chevron.style.transform = 'rotate(180deg)';
	}

	function closeDropdown() {
		dropdown.classList.add( 'hidden' );
		trigger.setAttribute( 'aria-expanded', 'false' );
		chevron.style.transform = '';
	}

	function setQuantity( value ) {
		hiddenInput.value = value;
		display.textContent = value === 1 ? `${ value } unidad` : `${ value } unidades`;

		dropdown.querySelectorAll( '.quantity-ml-option' ).forEach( ( opt ) => {
			opt.setAttribute( 'aria-selected', opt.dataset.value === String( value ) ? 'true' : 'false' );
		} );

		hiddenInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
	}

	// Toggle dropdown on trigger click
	trigger.addEventListener( 'click', () => {
		if ( dropdown.classList.contains( 'hidden' ) ) {
			openDropdown();
		} else {
			closeDropdown();
		}
	} );

	// Handle option selection
	dropdown.addEventListener( 'click', ( e ) => {
		const option = e.target.closest( '.quantity-ml-option' );
		if ( ! option ) {
			return;
		}

		closeDropdown();

		if ( option.dataset.value === 'more' ) {
			trigger.classList.add( 'hidden' );
			customPanel.classList.remove( 'hidden' );
			customPanel.classList.add( 'flex' );

			if ( customInput ) {
				const minMore = limit + 1;
				customInput.min   = minMore;
				customInput.value = minMore;
				hiddenInput.value = minMore;
				display.textContent = `${ minMore } unidades`;
				customInput.focus();
			}
		} else {
			setQuantity( parseInt( option.dataset.value, 10 ) );
		}
	} );

	// Sync custom number input → hidden input
	if ( customInput ) {
		customInput.addEventListener( 'input', () => {
			const val = parseInt( customInput.value, 10 );
			if ( ! isNaN( val ) && val > limit ) {
				const clamped = max !== Infinity ? Math.min( max, val ) : val;
				hiddenInput.value   = clamped;
				display.textContent = `${ clamped } unidades`;
				hiddenInput.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			}
		} );
	}

	// Cancel custom input — return to dropdown mode
	if ( cancelBtn ) {
		cancelBtn.addEventListener( 'click', () => {
			customPanel.classList.add( 'hidden' );
			customPanel.classList.remove( 'flex' );
			trigger.classList.remove( 'hidden' );
			setQuantity( limit );
		} );
	}

	// Close dropdown on outside click
	document.addEventListener( 'click', ( e ) => {
		if ( ! selector.contains( e.target ) ) {
			closeDropdown();
		}
	} );
}
