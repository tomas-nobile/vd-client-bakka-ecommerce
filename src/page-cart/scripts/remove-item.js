/**
 * Remove Item Script
 *
 * Handles cart item removal with AJAX.
 */

import { removeCartItem } from './cart-ajax.js';

/**
 * Initialize remove item buttons
 */
export function initRemoveItem() {
	const container = document.getElementById( 'cart-items-container' );

	if ( ! container ) {
		return;
	}

	// Event delegation for remove buttons
	container.addEventListener( 'click', handleRemoveClick );
}

/**
 * Handle remove button clicks
 *
 * @param {Event} e Click event.
 */
function handleRemoveClick( e ) {
	const removeBtn = e.target.closest( '.remove-item' );

	if ( ! removeBtn ) {
		return;
	}

	e.preventDefault();

	const cartItemKey = removeBtn.dataset.cartItemKey;
	const cartItem = document.querySelector(
		`.cart-item[data-cart-item-key="${ cartItemKey }"]`
	);

	if ( ! cartItem ) {
		return;
	}

	// Add visual feedback
	cartItem.classList.add( 'opacity-50', 'pointer-events-none' );
	removeBtn.textContent =
		removeBtn.dataset.removingText ||
		wp.i18n.__( 'Eliminando...', 'etheme' );

	// Remove the item
	removeCartItem( cartItemKey )
		.then( () => {
			// Animate and remove the element
			cartItem.style.transition = 'all 0.3s ease-out';
			cartItem.style.opacity = '0';
			cartItem.style.height = cartItem.offsetHeight + 'px';

			setTimeout( () => {
				cartItem.style.height = '0';
				cartItem.style.padding = '0';
				cartItem.style.margin = '0';
				cartItem.style.overflow = 'hidden';
			}, 50 );

			setTimeout( () => {
				cartItem.remove();
				checkEmptyCart();
			}, 350 );
		} )
		.catch( () => {
			// Restore visual state on error
			cartItem.classList.remove( 'opacity-50', 'pointer-events-none' );
			removeBtn.textContent =
				wp.i18n.__( 'Eliminar', 'etheme' ) || 'Eliminar';
		} );
}

/**
 * Check if cart is empty and reload if needed
 */
function checkEmptyCart() {
	const container = document.getElementById( 'cart-items-container' );
	const items = container?.querySelectorAll( '.cart-item' );

	if ( ! items || items.length === 0 ) {
		// Reload the page to show empty cart state
		window.location.reload();
	}
}
