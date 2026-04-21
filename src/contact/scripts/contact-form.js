/**
 * Contact form — client-side validation, anti-abuse, and API submit handling.
 *
 * Uses centralized security modules from src/core/security/.
 */

import { sanitizeText, sanitizeEmail, sanitizePhone } from '../../core/security/sanitizers.js';
import { required, email, length } from '../../core/security/validators.js';
import { FIELD, FORM } from '../../core/security/messages.js';
import {
	showFieldError,
	clearFieldError,
	clearAllFieldErrors,
	setFormNotice,
	focusFirstInvalid,
} from '../../core/security/ui-feedback.js';
import {
	guardedJsonPost,
	acquireLock,
	releaseLock,
} from '../../core/security/request-guard.js';

const LOCK_KEY = 'contact-submit';
const MIN_HUMAN_MS = 2500;
const COOLDOWN_MS = 10000;

let lastSubmitTs = 0;

function getField( form, id ) {
	return form.querySelector( `#${ id }` );
}

function validateContactForm( form ) {
	let valid = true;
	const fields = {
		name: getField( form, 'contact_name' ),
		phone: getField( form, 'contact_phone' ),
		email: getField( form, 'contact_email' ),
		msg: getField( form, 'contact_message' ),
	};

	clearAllFieldErrors( form );

	if ( ! required( fields.name?.value ) ) {
		showFieldError( fields.name, FIELD.nameRequired );
		valid = false;
	} else if ( ! length( sanitizeText( fields.name.value ), 2, 100 ) ) {
		showFieldError( fields.name, FIELD.lengthRange( 2, 100 ) );
		valid = false;
	}

	if ( ! required( fields.phone?.value ) ) {
		showFieldError( fields.phone, FIELD.phoneRequired );
		valid = false;
	}

	const emailVal = sanitizeEmail( fields.email?.value || '' );
	if ( ! required( emailVal ) ) {
		showFieldError( fields.email, FIELD.emailRequired );
		valid = false;
	} else if ( ! email( emailVal ) ) {
		showFieldError( fields.email, FIELD.emailInvalid );
		valid = false;
	}

	if ( ! required( fields.msg?.value ) ) {
		showFieldError( fields.msg, FIELD.messageRequired );
		valid = false;
	} else if ( ! length( sanitizeText( fields.msg.value ), 5, 2000 ) ) {
		showFieldError( fields.msg, FIELD.lengthRange( 5, 2000 ) );
		valid = false;
	}

	if ( ! valid ) {
		focusFirstInvalid( form );
	}

	return valid;
}

function isHoneypotFilled( form ) {
	const hp = form.querySelector( '[name="website_url"]' );
	return hp && hp.value.length > 0;
}

function isTimingGateBlocked( form ) {
	const ts = parseInt( form.dataset.loadedAt, 10 ) || 0;
	return ( Date.now() - ts ) < MIN_HUMAN_MS;
}

function isCooldownActive() {
	return lastSubmitTs > 0 && ( Date.now() - lastSubmitTs ) < COOLDOWN_MS;
}

function buildPayload( form ) {
	return {
		fullName: sanitizeText( getField( form, 'contact_name' ).value ),
		email: sanitizeEmail( getField( form, 'contact_email' ).value ),
		mobilePhone: sanitizePhone( getField( form, 'contact_phone' ).value ),
		comment: sanitizeText( getField( form, 'contact_message' ).value ),
		newsletter: !! form.querySelector( '#contact_newsletter' )?.checked,
	};
}

export function initContactForm() {
	const form = document.querySelector( '#etheme-contact-form' );
	if ( ! form ) return;

	form.dataset.loadedAt = String( Date.now() );

	const notice = form.querySelector( '.etheme-form-notice' );
	const submitBtn = form.querySelector( 'button[type="submit"]' );

	form.addEventListener( 'submit', async ( e ) => {
		e.preventDefault();

		if ( isHoneypotFilled( form ) ) return;

		if ( isTimingGateBlocked( form ) ) return;

		if ( isCooldownActive() ) {
			setFormNotice( notice, FORM.cooldown, 'is-error' );
			return;
		}

		if ( ! validateContactForm( form ) ) return;

		if ( ! acquireLock( LOCK_KEY ) ) return;

		const endpoint = form.dataset.endpoint;
		if ( ! endpoint ) {
			setFormNotice( notice, FORM.errorGeneric, 'is-error' );
			releaseLock( LOCK_KEY );
			return;
		}

		if ( submitBtn ) submitBtn.disabled = true;
		setFormNotice( notice, FORM.loading, 'is-loading' );

		const result = await guardedJsonPost( endpoint, buildPayload( form ) );

		if ( result.ok ) {
			setFormNotice( notice, FORM.success, 'is-success' );
			form.reset();
			form.dataset.loadedAt = String( Date.now() );
			lastSubmitTs = Date.now();
		} else if ( result.message === 'network' || result.message === 'timeout' ) {
			setFormNotice( notice, FORM.errorNetwork, 'is-error' );
		} else {
			setFormNotice( notice, FORM.errorServer, 'is-error' );
		}

		if ( submitBtn ) submitBtn.disabled = false;
		releaseLock( LOCK_KEY );
	} );
}
