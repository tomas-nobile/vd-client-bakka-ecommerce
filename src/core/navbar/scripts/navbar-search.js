/**
 * Navbar search — modal overlay with focus trap.
 *
 * - Search icon opens the modal; close button, backdrop click and Escape close it.
 * - Input field receives focus after open.
 * - Tab key is trapped inside the modal while open.
 * - Body scroll is locked while the modal is open.
 */

export function initNavbarSearch() {
	const trigger  = document.querySelector( '.etheme-navbar-action--search' );
	const modal    = document.getElementById( 'etheme-search-modal' );
	const closeBtn = modal ? modal.querySelector( '.etheme-search-modal__close' ) : null;
	const backdrop = modal ? modal.querySelector( '.etheme-search-modal__backdrop' ) : null;
	const input    = modal ? modal.querySelector( '#etheme-search-input' ) : null;

	if ( ! trigger || ! modal ) {
		return;
	}

	let lastFocus = null;

	function openSearch() {
		modal.classList.add( 'is-open' );
		modal.setAttribute( 'aria-hidden', 'false' );
		trigger.setAttribute( 'aria-expanded', 'true' );
		document.body.classList.add( 'etheme-scroll-locked' );
		lastFocus = document.activeElement;

		if ( input ) {
			// Slight delay to allow CSS transition to start.
			setTimeout( () => input.focus(), 60 );
		}
	}

	function closeSearch() {
		modal.classList.remove( 'is-open' );
		modal.setAttribute( 'aria-hidden', 'true' );
		trigger.setAttribute( 'aria-expanded', 'false' );
		document.body.classList.remove( 'etheme-scroll-locked' );

		if ( lastFocus ) {
			lastFocus.focus();
		}
	}

	trigger.addEventListener( 'click', openSearch );

	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', closeSearch );
	}

	if ( backdrop ) {
		backdrop.addEventListener( 'click', closeSearch );
	}

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && modal.classList.contains( 'is-open' ) ) {
			closeSearch();
		}
	} );

	// Focus trap: cycle through focusable elements within the modal.
	modal.addEventListener( 'keydown', function ( e ) {
		if ( e.key !== 'Tab' ) {
			return;
		}

		const focusable = Array.from(
			modal.querySelectorAll( 'button:not([disabled]), input:not([disabled]), a[href], [tabindex]:not([tabindex="-1"])' )
		);

		if ( ! focusable.length ) {
			return;
		}

		const first = focusable[ 0 ];
		const last  = focusable[ focusable.length - 1 ];

		if ( e.shiftKey ) {
			if ( document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			}
		} else {
			if ( document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		}
	} );
}
