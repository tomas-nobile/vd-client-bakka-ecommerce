/**
 * Client-side security hardening for the WooCommerce login form.
 *
 * SANITIZE → VALIDATE → GUARD
 *
 * Protections applied:
 * - Email format validation + password required
 * - Honeypot check (server-side PHP also validates)
 * - Timing gate: blocks submissions faster than MIN_HUMAN_MS
 * - Cooldown: prevents rapid re-submission
 * - Double-submit lock: prevents concurrent submits
 * - Inline field errors with aria-invalid
 * - Global notice for cooldown/anti-abuse messages
 */

import { sanitizeEmail } from '../../core/security/sanitizers.js';
import { required, email } from '../../core/security/validators.js';
import {
	showFieldError,
	clearFieldError,
	setFormNotice,
	clearFormNotice,
	focusFirstInvalid,
} from '../../core/security/ui-feedback.js';
import { acquireLock, releaseLock } from '../../core/security/request-guard.js';
import { LOGIN } from '../../core/security/messages.js';

const LOCK_KEY = 'login-submit';
const COOLDOWN_MS = 8000;
const MIN_HUMAN_MS = 2000;

const formLoadTime = Date.now();
let lastAttemptTime = 0;

/**
 * Inject .etheme-field-error spans into WooCommerce form rows
 * that don't already have one, so showFieldError can find them.
 *
 * @param {HTMLElement} form
 */
function injectErrorSpans( form ) {
	form.querySelectorAll( '.form-row' ).forEach( ( row ) => {
		if ( ! row.querySelector( '.etheme-field-error' ) ) {
			const span = document.createElement( 'span' );
			span.className = 'etheme-field-error';
			span.setAttribute( 'aria-live', 'polite' );
			row.appendChild( span );
		}
	} );
}

/**
 * Get or create the global notice element above the submit button.
 *
 * @param {HTMLElement} form
 * @returns {HTMLElement}
 */
function getOrCreateNotice( form ) {
	let notice = form.querySelector( '.etheme-form-notice--login' );
	if ( ! notice ) {
		notice = document.createElement( 'p' );
		notice.className = 'etheme-form-notice etheme-form-notice--login';
		notice.setAttribute( 'role', 'alert' );
		notice.setAttribute( 'aria-live', 'polite' );
		const submitRow = form.querySelector( '.form-row:last-of-type' );
		if ( submitRow ) {
			form.insertBefore( notice, submitRow );
		} else {
			form.appendChild( notice );
		}
	}
	return notice;
}

/**
 * Validate the username/email field.
 *
 * @param {HTMLInputElement} field
 * @returns {boolean}
 */
function validateUsername( field ) {
	if ( ! field ) return true;
	clearFieldError( field );
	const value = sanitizeEmail( field.value );
	if ( ! required( value ) ) {
		showFieldError( field, LOGIN.usernameRequired );
		return false;
	}
	if ( ! email( value ) ) {
		showFieldError( field, LOGIN.emailInvalid );
		return false;
	}
	return true;
}

/**
 * Validate the password field (required only — never inspect the value).
 *
 * @param {HTMLInputElement} field
 * @returns {boolean}
 */
function validatePassword( field ) {
	if ( ! field ) return true;
	clearFieldError( field );
	if ( ! required( field.value ) ) {
		showFieldError( field, LOGIN.passwordRequired );
		return false;
	}
	return true;
}

/**
 * Initialize login form security. Safe to call on any page — exits early
 * if the WooCommerce login form is not present.
 */
export function initLoginSecurity() {
	const form = document.querySelector( '.woocommerce-form-login' );
	if ( ! form ) return;

	injectErrorSpans( form );

	const usernameField = form.querySelector( '#username' );
	const passwordField = form.querySelector( '#password' );

	if ( usernameField ) {
		usernameField.addEventListener( 'blur', () =>
			validateUsername( usernameField )
		);
	}
	if ( passwordField ) {
		passwordField.addEventListener( 'blur', () =>
			validatePassword( passwordField )
		);
	}

	form.addEventListener( 'submit', ( e ) => {
		const notice = getOrCreateNotice( form );
		clearFormNotice( notice );

		// Honeypot: PHP injects the field; bots fill it in, humans don't.
		const hp = form.querySelector( '[name="etheme_hp_website"]' );
		if ( hp && hp.value.trim() !== '' ) {
			e.preventDefault();
			return;
		}

		// Timing gate: humans take >2 s to fill in a form.
		if ( Date.now() - formLoadTime < MIN_HUMAN_MS ) {
			e.preventDefault();
			return;
		}

		// Cooldown: throttle repeated submissions.
		const now = Date.now();
		if ( lastAttemptTime !== 0 && now - lastAttemptTime < COOLDOWN_MS ) {
			e.preventDefault();
			setFormNotice( notice, LOGIN.cooldown, 'is-error' );
			return;
		}

		// Field validation.
		const userOk = validateUsername( usernameField );
		const passOk = validatePassword( passwordField );

		if ( ! userOk || ! passOk ) {
			e.preventDefault();
			focusFirstInvalid( form );
			return;
		}

		// Double-submit lock.
		if ( ! acquireLock( LOCK_KEY ) ) {
			e.preventDefault();
			return;
		}

		lastAttemptTime = now;

		// Release lock when the page navigates away (after server response).
		window.addEventListener( 'pagehide', () => releaseLock( LOCK_KEY ), {
			once: true,
		} );
	} );
}
