/**
 * Quantity Selector Script
 *
 * Handles quantity increase/decrease buttons and input changes.
 */

import { updateCartItem } from './cart-ajax.js';

let debounceTimer = null;

/**
 * Initialize quantity selectors
 */
export function initQuantitySelectors() {
	const container = document.getElementById( 'cart-items-container' );

	if ( ! container ) {
		return;
	}

	// Event delegation for quantity buttons
	container.addEventListener( 'click', handleQuantityButtonClick );

	// Event delegation for quantity input changes
	container.addEventListener( 'change', handleQuantityInputChange );
}

/**
 * Handle quantity button clicks
 *
 * @param {Event} e Click event.
 */
function handleQuantityButtonClick( e ) {
	const button = e.target.closest( '.qty-btn' );

	if ( ! button ) {
		return;
	}

	const cartItemKey = button.dataset.cartItemKey;
	const action = button.dataset.action;
	const input = document.querySelector(
		`.qty-input[data-cart-item-key="${ cartItemKey }"]`
	);

	if ( ! input ) {
		return;
	}

	const currentQty = parseInt( input.value, 10 );
	const min = parseInt( input.min, 10 ) || 1;
	const max = parseInt( input.max, 10 ) || Infinity;

	let newQty = currentQty;

	if ( action === 'increase' && currentQty < max ) {
		newQty = currentQty + 1;
	} else if ( action === 'decrease' && currentQty > min ) {
		newQty = currentQty - 1;
	}

	if ( newQty !== currentQty ) {
		input.value = newQty;
		debouncedUpdateQuantity( cartItemKey, newQty );
		updateButtonStates( cartItemKey, newQty, min, max );
	}
}

/**
 * Handle quantity input changes
 *
 * @param {Event} e Change event.
 */
function handleQuantityInputChange( e ) {
	const input = e.target.closest( '.qty-input' );

	if ( ! input ) {
		return;
	}

	const cartItemKey = input.dataset.cartItemKey;
	let newQty = parseInt( input.value, 10 );
	const min = parseInt( input.min, 10 ) || 1;
	const max = parseInt( input.max, 10 ) || Infinity;

	// Validate quantity
	if ( isNaN( newQty ) || newQty < min ) {
		newQty = min;
	} else if ( newQty > max ) {
		newQty = max;
	}

	input.value = newQty;
	debouncedUpdateQuantity( cartItemKey, newQty );
	updateButtonStates( cartItemKey, newQty, min, max );
}

/**
 * Debounced quantity update to prevent excessive AJAX requests
 *
 * @param {string} cartItemKey Cart item key.
 * @param {number} quantity    New quantity.
 */
function debouncedUpdateQuantity( cartItemKey, quantity ) {
	clearTimeout( debounceTimer );

	debounceTimer = setTimeout( () => {
		updateCartItem( cartItemKey, quantity );
	}, 500 );
}

/**
 * Update button disabled states based on quantity
 *
 * @param {string} cartItemKey Cart item key.
 * @param {number} quantity    Current quantity.
 * @param {number} min         Minimum quantity.
 * @param {number} max         Maximum quantity.
 */
function updateButtonStates( cartItemKey, quantity, min, max ) {
	const decreaseBtn = document.querySelector(
		`.qty-decrease[data-cart-item-key="${ cartItemKey }"]`
	);
	const increaseBtn = document.querySelector(
		`.qty-increase[data-cart-item-key="${ cartItemKey }"]`
	);

	if ( decreaseBtn ) {
		decreaseBtn.disabled = quantity <= min;
	}

	if ( increaseBtn ) {
		increaseBtn.disabled = max !== Infinity && quantity >= max;
	}
}
