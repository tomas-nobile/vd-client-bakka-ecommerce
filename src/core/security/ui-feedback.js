/**
 * UX feedback utilities for form validation and state.
 *
 * Provides consistent error display, aria attributes, loading states,
 * and global form notices across coupon/checkout/contact.
 */

/**
 * Show an inline error beneath a field and mark it invalid.
 *
 * @param {HTMLElement} field   Input or textarea element.
 * @param {string}      message Error text.
 */
export function showFieldError( field, message ) {
	if ( ! field ) return;
	const span = field.closest( '.form-group, .form-row' )
		?.querySelector( '.etheme-field-error' );
	if ( span ) {
		span.textContent = message;
	}
	field.setAttribute( 'aria-invalid', 'true' );
}

/**
 * Clear inline error for a field.
 *
 * @param {HTMLElement} field Input or textarea element.
 */
export function clearFieldError( field ) {
	if ( ! field ) return;
	const span = field.closest( '.form-group, .form-row' )
		?.querySelector( '.etheme-field-error' );
	if ( span ) {
		span.textContent = '';
	}
	field.removeAttribute( 'aria-invalid' );
}

/**
 * Clear all field errors within a container.
 *
 * @param {HTMLElement} container Parent form or section.
 */
export function clearAllFieldErrors( container ) {
	if ( ! container ) return;
	container.querySelectorAll( '.etheme-field-error' ).forEach( ( el ) => {
		el.textContent = '';
	} );
	container.querySelectorAll( '[aria-invalid]' ).forEach( ( el ) => {
		el.removeAttribute( 'aria-invalid' );
	} );
}

/**
 * Set a global form notice (loading / success / error).
 *
 * @param {HTMLElement|null} notice  The `.etheme-form-notice` element.
 * @param {string}           message Text content.
 * @param {'is-loading'|'is-success'|'is-error'} state CSS modifier class.
 */
export function setFormNotice( notice, message, state ) {
	if ( ! notice ) return;
	notice.textContent = message;
	notice.className = `etheme-form-notice ${ state }`;
}

/**
 * Clear a global form notice.
 *
 * @param {HTMLElement|null} notice
 */
export function clearFormNotice( notice ) {
	if ( ! notice ) return;
	notice.textContent = '';
	notice.className = 'etheme-form-notice';
}

/**
 * Focus the first invalid field inside a container.
 *
 * @param {HTMLElement} container
 */
export function focusFirstInvalid( container ) {
	if ( ! container ) return;
	const first = container.querySelector( '[aria-invalid="true"]' );
	if ( first && typeof first.focus === 'function' ) {
		first.focus( { preventScroll: false } );
	}
}

/**
 * Toggle a button's loading state. Looks for `.button-text` / `.loading-spinner`
 * children, falling back to disabled toggle only.
 *
 * @param {HTMLButtonElement|null} btn
 * @param {boolean}                loading
 */
export function setButtonLoading( btn, loading ) {
	if ( ! btn ) return;
	btn.disabled = loading;
	btn.setAttribute( 'aria-busy', String( loading ) );
	const text = btn.querySelector( '.button-text' );
	const spinner = btn.querySelector( '.loading-spinner' );
	if ( text ) text.classList.toggle( 'hidden', loading );
	if ( spinner ) spinner.classList.toggle( 'hidden', ! loading );
}
