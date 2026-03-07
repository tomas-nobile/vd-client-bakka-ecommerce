/**
 * Cart AJAX Script
 *
 * Handles cart update operations via AJAX.
 */

import { updateCartTotals } from './shipping-calculator.js';

/**
 * Update a cart item quantity
 *
 * @param {string} cartItemKey Cart item key.
 * @param {number} quantity    New quantity.
 * @return {Promise} AJAX response promise.
 */
export async function updateCartItem( cartItemKey, quantity ) {
	const cartBlock = document.querySelector( '.page-cart-block' );
	const ajaxUrl = cartBlock?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';
	const nonce = cartBlock?.dataset.cartNonce;

	// Show loading state on the item
	const cartItem = document.querySelector(
		`.cart-item[data-cart-item-key="${ cartItemKey }"]`
	);
	cartItem?.classList.add( 'opacity-70' );

	try {
		const formData = new FormData();
		formData.append( 'action', 'etheme_update_cart_item' );
		formData.append( 'cart_item_key', cartItemKey );
		formData.append( 'quantity', quantity );
		formData.append( 'nonce', nonce );

		const response = await fetch( ajaxUrl, {
			method: 'POST',
			body: formData,
		} );

		const data = await response.json();

		if ( data.success ) {
			// Update line total
			const lineTotalEl = cartItem?.querySelector( '.line-total' );
			if ( lineTotalEl && data.data.line_total_html ) {
				lineTotalEl.innerHTML = data.data.line_total_html;
			}

			// Update cart totals
			if ( data.data.cart_totals ) {
				updateCartTotals( data.data.cart_totals );
			}

			// Update cart count in navbar
			updateNavbarCartCount( data.data.cart_count );

			// Trigger WooCommerce cart updated event
			triggerCartUpdated();
		} else {
			console.error( 'Cart update error:', data.data?.message );
		}

		return data;
	} catch ( error ) {
		console.error( 'Cart update error:', error );
		throw error;
	} finally {
		cartItem?.classList.remove( 'opacity-70' );
	}
}

/**
 * Remove a cart item
 *
 * @param {string} cartItemKey Cart item key.
 * @return {Promise} AJAX response promise.
 */
export async function removeCartItem( cartItemKey ) {
	const cartBlock = document.querySelector( '.page-cart-block' );
	const ajaxUrl = cartBlock?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';
	const nonce = cartBlock?.dataset.cartNonce;

	try {
		const formData = new FormData();
		formData.append( 'action', 'etheme_remove_cart_item' );
		formData.append( 'cart_item_key', cartItemKey );
		formData.append( 'nonce', nonce );

		const response = await fetch( ajaxUrl, {
			method: 'POST',
			body: formData,
		} );

		const data = await response.json();

		if ( data.success ) {
			// Update cart totals
			if ( data.data.cart_totals ) {
				updateCartTotals( data.data.cart_totals );
			}

			// Update cart count in navbar
			updateNavbarCartCount( data.data.cart_count );

			// Update item count display
			updateItemCount( data.data.cart_count );

			// Trigger WooCommerce cart updated event
			triggerCartUpdated();
		}

		return data;
	} catch ( error ) {
		console.error( 'Cart remove error:', error );
		throw error;
	}
}

/**
 * Update the navbar cart count
 *
 * @param {number} count New cart count.
 */
function updateNavbarCartCount( count ) {
	const cartCountEl = document.querySelector( '.cart-count' );
	const cartBadgeEl = document.querySelector( '.cart-badge' );

	if ( cartCountEl ) {
		cartCountEl.textContent = count;
	}

	if ( cartBadgeEl ) {
		if ( count > 0 ) {
			cartBadgeEl.classList.remove( 'hidden' );
			cartBadgeEl.textContent = count;
		} else {
			cartBadgeEl.classList.add( 'hidden' );
		}
	}

	// Also update WooCommerce cart fragments if available
	if ( typeof wc_cart_fragments_params !== 'undefined' ) {
		jQuery( document.body ).trigger( 'wc_fragment_refresh' );
	}
}

/**
 * Update the item count display on the cart page
 *
 * @param {number} count New item count.
 */
function updateItemCount( count ) {
	const countEl = document.getElementById( 'cart-item-count' );

	if ( countEl ) {
		if ( count > 0 ) {
			// Handle pluralization
			const text =
				count === 1
					? wp.i18n.__( '1 item', 'etheme' )
					: wp.i18n.sprintf(
							wp.i18n.__( '%d items', 'etheme' ),
							count
					  );
			countEl.textContent = text;
		} else {
			countEl.textContent = '';
		}
	}
}

/**
 * Trigger WooCommerce cart updated event
 */
function triggerCartUpdated() {
	// Dispatch custom event for any listeners
	document.dispatchEvent(
		new CustomEvent( 'etheme_cart_updated', {
			bubbles: true,
		} )
	);

	// Trigger jQuery event for WooCommerce compatibility
	if ( typeof jQuery !== 'undefined' ) {
		jQuery( document.body ).trigger( 'updated_cart_totals' );
	}
}

/**
 * Get current cart contents
 *
 * @return {Promise} Cart contents promise.
 */
export async function getCartContents() {
	const cartBlock = document.querySelector( '.page-cart-block' );
	const ajaxUrl = cartBlock?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';

	try {
		const formData = new FormData();
		formData.append( 'action', 'etheme_get_cart_contents' );

		const response = await fetch( ajaxUrl, {
			method: 'POST',
			body: formData,
		} );

		return response.json();
	} catch ( error ) {
		console.error( 'Get cart error:', error );
		throw error;
	}
}
