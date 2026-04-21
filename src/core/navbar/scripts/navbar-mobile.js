/**
 * Navbar mobile — fullscreen menu panel.
 *
 * - Hamburger opens the panel; close button and Escape close it.
 * - Body scroll is locked while the panel is open.
 * - Dropdown items inside the mobile panel expand/collapse on click (accordion).
 * - Focus returns to the trigger element on close.
 */

const FOCUSABLE_SELECTORS =
	'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function initNavbarMobile() {
	const toggler = document.querySelector( '.etheme-navbar-toggler' );
	const panel   = document.getElementById( 'etheme-mobile-menu' );
	const closeBtn = panel ? panel.querySelector( '.etheme-mobile-menu__close' ) : null;

	if ( ! toggler || ! panel ) {
		return;
	}

	let lastFocus = null;

	function openMenu() {
		panel.classList.add( 'is-open' );
		panel.setAttribute( 'aria-hidden', 'false' );
		toggler.setAttribute( 'aria-expanded', 'true' );
		document.body.classList.add( 'etheme-scroll-locked' );
		lastFocus = document.activeElement;

		const firstFocusable = panel.querySelector( FOCUSABLE_SELECTORS );
		if ( firstFocusable ) {
			firstFocusable.focus();
		}
	}

	function closeMenu() {
		panel.classList.remove( 'is-open' );
		panel.setAttribute( 'aria-hidden', 'true' );
		toggler.setAttribute( 'aria-expanded', 'false' );
		document.body.classList.remove( 'etheme-scroll-locked' );

		if ( lastFocus ) {
			lastFocus.focus();
		}
	}

	toggler.addEventListener( 'click', openMenu );

	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', closeMenu );
	}

	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' && panel.classList.contains( 'is-open' ) ) {
			closeMenu();
		}
	} );

	// Desktop: clicking "Tienda" should toggle dropdown instead of navigating.
	document.querySelectorAll( '.etheme-navbar-nav .etheme-nav-item--shop > .etheme-nav-link' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( e ) {
			if ( window.matchMedia( '(max-width: 991px)' ).matches ) {
				return;
			}

			e.preventDefault();
			const item = link.closest( '.etheme-nav-item--shop' );
			const openItems = document.querySelectorAll( '.etheme-navbar-nav .etheme-nav-item--shop.is-open' );

			openItems.forEach( function ( openItem ) {
				if ( openItem !== item ) {
					openItem.classList.remove( 'is-open' );
					const openLink = openItem.querySelector( '.etheme-nav-link' );
					if ( openLink ) {
						openLink.setAttribute( 'aria-expanded', 'false' );
					}
				}
			} );

			if ( ! item ) {
				return;
			}

			const nextState = ! item.classList.contains( 'is-open' );
			item.classList.toggle( 'is-open', nextState );
			link.setAttribute( 'aria-expanded', String( nextState ) );
		} );
	} );

	document.addEventListener( 'click', function ( e ) {
		if ( e.target.closest( '.etheme-navbar-nav .etheme-nav-item--shop' ) ) {
			return;
		}

		document.querySelectorAll( '.etheme-navbar-nav .etheme-nav-item--shop.is-open' ).forEach( function ( item ) {
			item.classList.remove( 'is-open' );
			const link = item.querySelector( '.etheme-nav-link' );
			if ( link ) {
				link.setAttribute( 'aria-expanded', 'false' );
			}
		} );
	} );

	// Accordion-style dropdowns inside the mobile panel.
	panel.querySelectorAll( '.etheme-nav-link--dropdown' ).forEach( function ( link ) {
		link.addEventListener( 'click', function ( e ) {
			e.preventDefault();

			const item       = link.closest( '.etheme-nav-item--has-dropdown' );
			const isExpanded = link.getAttribute( 'aria-expanded' ) === 'true';

			// Collapse other open items at the same level.
			const siblings = item
				? item.parentElement.querySelectorAll( '.etheme-nav-item--has-dropdown' )
				: [];
			siblings.forEach( function ( sibling ) {
				if ( sibling !== item ) {
					sibling.classList.remove( 'is-expanded' );
					const sibLink = sibling.querySelector( '.etheme-nav-link--dropdown' );
					if ( sibLink ) {
						sibLink.setAttribute( 'aria-expanded', 'false' );
					}
				}
			} );

			link.setAttribute( 'aria-expanded', String( ! isExpanded ) );
			if ( item ) {
				item.classList.toggle( 'is-expanded', ! isExpanded );
			}
		} );
	} );

	// Nested mobile categories (shop -> subcategories) are closed by default.
	panel.querySelectorAll( '.etheme-dropdown__toggle' ).forEach( function ( toggle ) {
		toggle.addEventListener( 'click', function ( e ) {
			e.preventDefault();
			e.stopPropagation();

			const parent = toggle.closest( '.etheme-dropdown__item--has-sub' );
			if ( ! parent ) {
				return;
			}

			const nextState = ! parent.classList.contains( 'is-sub-expanded' );
			parent.classList.toggle( 'is-sub-expanded', nextState );
			toggle.setAttribute( 'aria-expanded', String( nextState ) );
		} );
	} );
}
