/**
 * Coupon Script
 *
 * Handles coupon application and removal with centralized security.
 */

import { updateCartTotals } from './shipping-calculator.js';
import { sanitizeCoupon } from '../../core/security/sanitizers.js';
import { required, couponCode } from '../../core/security/validators.js';
import { COUPON } from '../../core/security/messages.js';
import { setButtonLoading } from '../../core/security/ui-feedback.js';
import {
	guardedFormPost,
	acquireLock,
	releaseLock,
} from '../../core/security/request-guard.js';

const LOCK_APPLY = 'coupon-apply';
const LOCK_REMOVE = 'coupon-remove';

/**
 * Initialize coupon trigger toggle (show/hide panel)
 */
export function initCouponToggle() {
	const trigger = document.getElementById( 'coupon-trigger' );
	const panel = document.getElementById( 'coupon-form-panel' );
	if ( ! trigger || ! panel ) return;

	trigger.addEventListener( 'click', () => {
		const isExpanded = trigger.getAttribute( 'aria-expanded' ) === 'true';
		setCouponPanelOpen( ! isExpanded, trigger, panel );
	} );
}

function setCouponPanelOpen( open, trigger, panel ) {
	trigger.setAttribute( 'aria-expanded', String( open ) );
	panel.classList.toggle( 'hidden', ! open );
	const chevron = trigger.querySelector( '.coupon-chevron' );
	if ( chevron ) chevron.classList.toggle( 'rotate-180', open );
	if ( open ) panel.querySelector( '#coupon_code' )?.focus();
}

/**
 * Initialize coupon functionality
 */
export function initCoupon() {
	const couponForm = document.getElementById( 'coupon-form' );
	const appliedCoupons = document.getElementById( 'applied-coupons' );
	if ( couponForm ) couponForm.addEventListener( 'submit', handleCouponApply );
	if ( appliedCoupons ) appliedCoupons.addEventListener( 'click', handleCouponRemove );
}

function getAjaxUrl() {
	const cartBlock = document.querySelector( '.page-cart-block' );
	return cartBlock?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';
}

async function handleCouponApply( e ) {
	e.preventDefault();

	const form = e.target;
	const input = form.querySelector( '#coupon_code' );
	const button = document.getElementById( 'apply-coupon-btn' );
	const messageEl = document.getElementById( 'coupon-message' );
	const rawValue = input?.value || '';

	const sanitized = sanitizeCoupon( rawValue );

	if ( ! required( sanitized ) ) {
		showCouponMsg( messageEl, COUPON.empty, 'error' );
		return;
	}

	if ( ! couponCode( sanitized ) ) {
		showCouponMsg( messageEl, COUPON.format, 'error' );
		return;
	}

	if ( ! acquireLock( LOCK_APPLY ) ) return;

	setButtonLoading( button, true );
	hideCouponMsg( messageEl );

	const nonce = form.querySelector( '#coupon_nonce' )?.value;
	const formData = new FormData();
	formData.append( 'action', 'etheme_apply_coupon' );
	formData.append( 'coupon_code', sanitized );
	if ( nonce ) formData.append( 'nonce', nonce );

	const result = await guardedFormPost( getAjaxUrl(), formData );

	if ( result.ok ) {
		showCouponMsg( messageEl, result.data?.message || COUPON.applied, 'success' );
		if ( input ) input.value = '';
		addCouponTag( sanitized, result.data?.discount_html );
		if ( result.data?.cart_totals ) updateCartTotals( result.data.cart_totals );
		setTimeout( () => window.location.reload(), 1000 );
	} else {
		const msg = result.data?.message || COUPON.errorApply;
		showCouponMsg( messageEl, msg, 'error' );
	}

	setButtonLoading( button, false );
	releaseLock( LOCK_APPLY );
}

async function handleCouponRemove( e ) {
	const removeBtn = e.target.closest( '.remove-coupon' );
	if ( ! removeBtn ) return;

	const code = removeBtn.dataset.coupon;
	const couponTag = removeBtn.closest( '.coupon-tag' );
	if ( ! code ) return;

	if ( ! acquireLock( LOCK_REMOVE ) ) return;

	couponTag?.classList.add( 'opacity-50' );

	const formData = new FormData();
	formData.append( 'action', 'etheme_remove_coupon' );
	formData.append( 'coupon_code', code );

	const result = await guardedFormPost( getAjaxUrl(), formData );

	if ( result.ok ) {
		if ( couponTag ) {
			couponTag.style.transition = 'all 0.3s ease-out';
			couponTag.style.opacity = '0';
			couponTag.style.transform = 'translateX(-10px)';
			setTimeout( () => {
				couponTag.remove();
				checkEmptyCoupons();
			}, 300 );
		}
		if ( result.data?.cart_totals ) updateCartTotals( result.data.cart_totals );
		setTimeout( () => window.location.reload(), 500 );
	} else {
		couponTag?.classList.remove( 'opacity-50' );
	}

	releaseLock( LOCK_REMOVE );
}

function addCouponTag( code, discountHtml ) {
	let appliedCoupons = document.getElementById( 'applied-coupons' );

	if ( ! appliedCoupons ) {
		const couponSection = document.getElementById( 'coupon-section' );
		if ( ! couponSection ) return;

		appliedCoupons = document.createElement( 'div' );
		appliedCoupons.id = 'applied-coupons';
		appliedCoupons.className = 'applied-coupons mb-4';

		const label = document.createElement( 'p' );
		label.className = 'text-sm font-medium text-gray-700 mb-2';
		label.textContent = wp.i18n.__( 'Applied Coupons', 'etheme' );

		const list = document.createElement( 'div' );
		list.className = 'space-y-2';

		appliedCoupons.appendChild( label );
		appliedCoupons.appendChild( list );

		couponSection.insertBefore( appliedCoupons, document.getElementById( 'coupon-form' ) );
		appliedCoupons.addEventListener( 'click', handleCouponRemove );
	}

	const container = appliedCoupons.querySelector( '.space-y-2' );
	if ( ! container ) return;

	const tag = document.createElement( 'div' );
	tag.className = 'coupon-tag flex items-center justify-between bg-green-50 border border-green-200 rounded px-3 py-2';
	tag.dataset.coupon = code;

	const info = document.createElement( 'div' );
	info.className = 'flex items-center';

	const tagIcon = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
	tagIcon.setAttribute( 'class', 'w-4 h-4 text-green-600 mr-2' );
	tagIcon.setAttribute( 'fill', 'none' );
	tagIcon.setAttribute( 'stroke', 'currentColor' );
	tagIcon.setAttribute( 'viewBox', '0 0 24 24' );
	const tagPath = document.createElementNS( 'http://www.w3.org/2000/svg', 'path' );
	tagPath.setAttribute( 'stroke-linecap', 'round' );
	tagPath.setAttribute( 'stroke-linejoin', 'round' );
	tagPath.setAttribute( 'stroke-width', '2' );
	tagPath.setAttribute( 'd', 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' );
	tagIcon.appendChild( tagPath );
	info.appendChild( tagIcon );

	const nameSpan = document.createElement( 'span' );
	nameSpan.className = 'text-sm font-medium text-green-800 uppercase';
	nameSpan.textContent = code;
	info.appendChild( nameSpan );

	if ( discountHtml ) {
		const discountSpan = document.createElement( 'span' );
		discountSpan.className = 'text-sm text-green-600 ml-2';
		discountSpan.textContent = `-${ discountHtml }`;
		info.appendChild( discountSpan );
	}

	tag.appendChild( info );

	const removeButton = document.createElement( 'button' );
	removeButton.type = 'button';
	removeButton.className = 'remove-coupon text-green-600 hover:text-green-800 transition';
	removeButton.dataset.coupon = code;
	removeButton.setAttribute( 'aria-label', wp.i18n.__( 'Remove coupon', 'etheme' ) );

	const removeIcon = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
	removeIcon.setAttribute( 'class', 'w-4 h-4' );
	removeIcon.setAttribute( 'fill', 'none' );
	removeIcon.setAttribute( 'stroke', 'currentColor' );
	removeIcon.setAttribute( 'viewBox', '0 0 24 24' );
	const removePath = document.createElementNS( 'http://www.w3.org/2000/svg', 'path' );
	removePath.setAttribute( 'stroke-linecap', 'round' );
	removePath.setAttribute( 'stroke-linejoin', 'round' );
	removePath.setAttribute( 'stroke-width', '2' );
	removePath.setAttribute( 'd', 'M6 18L18 6M6 6l12 12' );
	removeIcon.appendChild( removePath );
	removeButton.appendChild( removeIcon );

	tag.appendChild( removeButton );
	container.appendChild( tag );
}

function checkEmptyCoupons() {
	const appliedCoupons = document.getElementById( 'applied-coupons' );
	const tags = appliedCoupons?.querySelectorAll( '.coupon-tag' );
	if ( appliedCoupons && ( ! tags || tags.length === 0 ) ) {
		appliedCoupons.remove();
	}
}

function showCouponMsg( el, message, type ) {
	if ( ! el ) return;
	el.textContent = message;
	el.className = `mt-2 text-sm ${ type === 'error' ? 'text-red-600' : 'text-green-600' }`;
	el.classList.remove( 'hidden' );
}

function hideCouponMsg( el ) {
	if ( el ) el.classList.add( 'hidden' );
}
