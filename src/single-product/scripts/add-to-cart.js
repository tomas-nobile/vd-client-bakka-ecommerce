/**
 * Add to Cart Script
 *
 * Handles add to cart form submission, quantity controls, and visual feedback.
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
		form.addEventListener( 'submit', function () {
		// Show loading state
		const buttonText = button.querySelector( '.button-text' );
		const addingText = button.dataset.addingText || 'Agregando...';
		const addedText = button.dataset.addedText || 'Agregado';
		const addText = button.dataset.addText || 'Agregar al carrito';

		if ( buttonText ) {
			buttonText.textContent = addingText;
		}
		button.disabled = true;

		// Simulate success feedback (in real implementation, this would be handled by WooCommerce)
		setTimeout( () => {
			if ( buttonText ) {
				buttonText.textContent = addedText;
			}
			button.classList.add( 'bg-green-600' );
			button.classList.remove( 'bg-[#fb704f]' );

			// Reset after 2 seconds
			setTimeout( () => {
				if ( buttonText ) {
					buttonText.textContent = addText;
				}
				button.disabled = false;
				button.classList.remove( 'bg-green-600' );
				button.classList.add( 'bg-[#fb704f]' );
			}, 2000 );
		}, 500 );
		} );
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