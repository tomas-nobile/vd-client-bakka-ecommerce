/**
 * Coupon Script
 *
 * Handles coupon application and removal.
 */

import { updateCartTotals } from './shipping-calculator.js';

/**
 * Initialize coupon trigger toggle (MercadoLibre-style show/hide panel)
 */
export function initCouponToggle() {
	const trigger = document.getElementById( 'coupon-trigger' );
	const panel   = document.getElementById( 'coupon-form-panel' );

	if ( ! trigger || ! panel ) {
		return;
	}

	trigger.addEventListener( 'click', () => {
		const isExpanded = trigger.getAttribute( 'aria-expanded' ) === 'true';
		setCouponPanelOpen( ! isExpanded, trigger, panel );
	} );
}

/**
 * Open or close the coupon form panel
 *
 * @param {boolean} open    Target open state.
 * @param {Element} trigger Trigger button element.
 * @param {Element} panel   Panel element.
 */
function setCouponPanelOpen( open, trigger, panel ) {
	trigger.setAttribute( 'aria-expanded', String( open ) );
	panel.classList.toggle( 'hidden', ! open );

	const chevron = trigger.querySelector( '.coupon-chevron' );
	if ( chevron ) {
		chevron.classList.toggle( 'rotate-180', open );
	}

	if ( open ) {
		const input = panel.querySelector( '#coupon_code' );
		if ( input ) {
			input.focus();
		}
	}
}

/**
 * Initialize coupon functionality
 */
export function initCoupon() {
	const couponForm = document.getElementById( 'coupon-form' );
	const appliedCoupons = document.getElementById( 'applied-coupons' );

	if ( couponForm ) {
		couponForm.addEventListener( 'submit', handleCouponApply );
	}

	if ( appliedCoupons ) {
		appliedCoupons.addEventListener( 'click', handleCouponRemove );
	}
}

/**
 * Handle coupon form submission
 *
 * @param {Event} e Submit event.
 */
async function handleCouponApply( e ) {
	e.preventDefault();

	const form = e.target;
	const input = form.querySelector( '#coupon_code' );
	const button = document.getElementById( 'apply-coupon-btn' );
	const messageEl = document.getElementById( 'coupon-message' );
	const couponCode = input?.value.trim();

	if ( ! couponCode ) {
		showMessage( messageEl, wp.i18n.__( 'Please enter a coupon code', 'etheme' ), 'error' );
		return;
	}

	// Show loading state
	setLoadingState( button, true );
	hideMessage( messageEl );

	try {
		const cartBlock = document.querySelector( '.page-cart-block' );
		const ajaxUrl =
			cartBlock?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';
		const nonce = form.querySelector( '#coupon_nonce' )?.value;

		const formData = new FormData();
		formData.append( 'action', 'etheme_apply_coupon' );
		formData.append( 'coupon_code', couponCode );
		formData.append( 'nonce', nonce );

		const response = await fetch( ajaxUrl, {
			method: 'POST',
			body: formData,
		} );

		const data = await response.json();

		if ( data.success ) {
			showMessage( messageEl, data.data.message, 'success' );
			input.value = '';

			// Add coupon tag to applied coupons
			addCouponTag( couponCode, data.data.discount_html );

			// Update cart totals
			if ( data.data.cart_totals ) {
				updateCartTotals( data.data.cart_totals );
			}

			// Reload to update discount display
			setTimeout( () => window.location.reload(), 1000 );
		} else {
			showMessage(
				messageEl,
				data.data?.message || wp.i18n.__( 'Invalid coupon code', 'etheme' ),
				'error'
			);
		}
	} catch ( error ) {
		console.error( 'Coupon apply error:', error );
		showMessage( messageEl, wp.i18n.__( 'Error applying coupon', 'etheme' ), 'error' );
	} finally {
		setLoadingState( button, false );
	}
}

/**
 * Handle coupon removal
 *
 * @param {Event} e Click event.
 */
async function handleCouponRemove( e ) {
	const removeBtn = e.target.closest( '.remove-coupon' );

	if ( ! removeBtn ) {
		return;
	}

	const couponCode = removeBtn.dataset.coupon;
	const couponTag = removeBtn.closest( '.coupon-tag' );

	if ( ! couponCode ) {
		return;
	}

	// Show loading state
	couponTag?.classList.add( 'opacity-50' );

	try {
		const cartBlock = document.querySelector( '.page-cart-block' );
		const ajaxUrl =
			cartBlock?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';

		const formData = new FormData();
		formData.append( 'action', 'etheme_remove_coupon' );
		formData.append( 'coupon_code', couponCode );

		const response = await fetch( ajaxUrl, {
			method: 'POST',
			body: formData,
		} );

		const data = await response.json();

		if ( data.success ) {
			// Remove coupon tag with animation
			if ( couponTag ) {
				couponTag.style.transition = 'all 0.3s ease-out';
				couponTag.style.opacity = '0';
				couponTag.style.transform = 'translateX(-10px)';

				setTimeout( () => {
					couponTag.remove();
					checkEmptyCoupons();
				}, 300 );
			}

			// Update cart totals
			if ( data.data.cart_totals ) {
				updateCartTotals( data.data.cart_totals );
			}

			// Reload to update discount display
			setTimeout( () => window.location.reload(), 500 );
		} else {
			couponTag?.classList.remove( 'opacity-50' );
		}
	} catch ( error ) {
		console.error( 'Coupon remove error:', error );
		couponTag?.classList.remove( 'opacity-50' );
	}
}

/**
 * Add a coupon tag to the applied coupons section
 *
 * @param {string} code         Coupon code.
 * @param {string} discountHtml Discount HTML.
 */
function addCouponTag( code, discountHtml ) {
	let appliedCoupons = document.getElementById( 'applied-coupons' );

	// Create applied coupons container if it doesn't exist
	if ( ! appliedCoupons ) {
		const couponSection = document.getElementById( 'coupon-section' );
		if ( ! couponSection ) {
			return;
		}

		appliedCoupons = document.createElement( 'div' );
		appliedCoupons.id = 'applied-coupons';
		appliedCoupons.className = 'applied-coupons mb-4';
		appliedCoupons.innerHTML = `
			<p class="text-sm font-medium text-gray-700 mb-2">
				${ wp.i18n.__( 'Applied Coupons', 'etheme' ) }
			</p>
			<div class="space-y-2"></div>
		`;
		couponSection.insertBefore(
			appliedCoupons,
			document.getElementById( 'coupon-form' )
		);

		// Re-attach event listener
		appliedCoupons.addEventListener( 'click', handleCouponRemove );
	}

	const container = appliedCoupons.querySelector( '.space-y-2' );
	if ( ! container ) {
		return;
	}

	const tagHtml = `
		<div class="coupon-tag flex items-center justify-between bg-green-50 border border-green-200 rounded px-3 py-2" 
			 data-coupon="${ escapeHtml( code ) }">
			<div class="flex items-center">
				<svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
				</svg>
				<span class="text-sm font-medium text-green-800 uppercase">${ escapeHtml( code ) }</span>
				<span class="text-sm text-green-600 ml-2">
					-${ discountHtml || '' }
				</span>
			</div>
			<button type="button" 
					class="remove-coupon text-green-600 hover:text-green-800 transition"
					data-coupon="${ escapeHtml( code ) }"
					aria-label="${ wp.i18n.__( 'Remove coupon', 'etheme' ) }">
				<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
				</svg>
			</button>
		</div>
	`;

	container.insertAdjacentHTML( 'beforeend', tagHtml );
}

/**
 * Check if there are no coupons and hide container
 */
function checkEmptyCoupons() {
	const appliedCoupons = document.getElementById( 'applied-coupons' );
	const tags = appliedCoupons?.querySelectorAll( '.coupon-tag' );

	if ( appliedCoupons && ( ! tags || tags.length === 0 ) ) {
		appliedCoupons.remove();
	}
}

/**
 * Set button loading state
 *
 * @param {Element} button  Button element.
 * @param {boolean} loading Loading state.
 */
function setLoadingState( button, loading ) {
	if ( ! button ) {
		return;
	}

	const text = button.querySelector( '.button-text' );
	const spinner = button.querySelector( '.loading-spinner' );

	button.disabled = loading;

	if ( text ) {
		text.classList.toggle( 'hidden', loading );
	}
	if ( spinner ) {
		spinner.classList.toggle( 'hidden', ! loading );
	}
}

/**
 * Show message
 *
 * @param {Element} el      Message element.
 * @param {string}  message Message text.
 * @param {string}  type    Message type (success/error).
 */
function showMessage( el, message, type ) {
	if ( ! el ) {
		return;
	}

	el.textContent = message;
	el.className = `mt-2 text-sm ${ type === 'error' ? 'text-red-600' : 'text-green-600' }`;
	el.classList.remove( 'hidden' );
}

/**
 * Hide message
 *
 * @param {Element} el Message element.
 */
function hideMessage( el ) {
	if ( el ) {
		el.classList.add( 'hidden' );
	}
}

/**
 * Escape HTML for safe rendering
 *
 * @param {string} str String to escape.
 * @return {string} Escaped string.
 */
function escapeHtml( str ) {
	const div = document.createElement( 'div' );
	div.textContent = str;
	return div.innerHTML;
}
