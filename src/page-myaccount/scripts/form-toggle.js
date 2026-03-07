/**
 * Password visibility toggle script for My Account page.
 *
 * Handles password field show/hide via [data-toggle-password] buttons.
 */

/**
 * Initialize form interactive features.
 */
export function initFormToggle() {
	initPasswordToggle();
}

/**
 * Toggle password field visibility.
 */
function initPasswordToggle() {
	document.addEventListener( 'click', function ( e ) {
		const btn = e.target.closest( '[data-toggle-password]' );
		if ( ! btn ) {
			return;
		}

		const targetId = btn.getAttribute( 'data-toggle-password' );
		const input = document.getElementById( targetId );
		if ( ! input ) {
			return;
		}

		const isPassword = input.type === 'password';
		input.type = isPassword ? 'text' : 'password';

		// Swap icon — eye-open vs eye-off
		const eyeOpen =
			'<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
		const eyeOff =
			'<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>';

		btn.innerHTML = isPassword ? eyeOff : eyeOpen;
		btn.setAttribute(
			'aria-label',
			isPassword ? 'Hide password' : 'Show password'
		);
	} );
}
