/**
 * Mobile account sidebar nav: toggle + keyboard Esc close.
 * Progressive enhancement — nav links remain accessible without JS
 * because the list is only hidden on small screens via CSS media query.
 */

function bindEscClose( toggle, panel ) {
	document.addEventListener( 'keydown', ( e ) => {
		if ( 'Escape' === e.key && 'true' === toggle.getAttribute( 'aria-expanded' ) ) {
			toggle.setAttribute( 'aria-expanded', 'false' );
			panel.classList.remove( 'is-open' );
			toggle.focus();
		}
	} );
}

function bindToggle( toggle, panel ) {
	toggle.addEventListener( 'click', () => {
		const isOpen = 'true' === toggle.getAttribute( 'aria-expanded' );
		toggle.setAttribute( 'aria-expanded', String( ! isOpen ) );
		panel.classList.toggle( 'is-open', ! isOpen );
	} );
}

/**
 * Initialise the mobile nav toggle inside .page-account-block--logged-in.
 */
export function initAccountMobileNav() {
	const toggle = document.querySelector( '.account-nav__toggle' );
	if ( ! toggle ) {
		return;
	}
	const panel = document.getElementById( 'account-nav-panel' );
	if ( ! panel ) {
		return;
	}
	bindToggle( toggle, panel );
	bindEscClose( toggle, panel );
}
