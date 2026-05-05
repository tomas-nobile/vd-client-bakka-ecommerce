/**
 * Product Filter Button / Drawer Script
 *
 * Manages the mobile offcanvas drawer:
 * - Open on toggle button click
 * - Close on close button, backdrop click, or ESC key
 */

export function initFilterButton() {
	const toggleBtn  = document.getElementById( 'toggle-filters' );
	const drawer     = document.getElementById( 'filters-content' );
	const closeBtn   = document.getElementById( 'close-filters' );
	const backdrop   = document.getElementById( 'filters-backdrop' );

	if ( ! toggleBtn || ! drawer ) {
		return;
	}

	toggleBtn.addEventListener( 'click', () => setDrawerOpen( true ) );

	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', () => setDrawerOpen( false ) );
	}

	if ( backdrop ) {
		backdrop.addEventListener( 'click', () => setDrawerOpen( false ) );
	}

	document.addEventListener( 'keydown', onKeyDown );
}

function setDrawerOpen( open ) {
	const drawer   = document.getElementById( 'filters-content' );
	const backdrop = document.getElementById( 'filters-backdrop' );
	const toggleBtn = document.getElementById( 'toggle-filters' );

	if ( ! drawer ) {
		return;
	}

	drawer.classList.toggle( 'is-open', open );

	if ( backdrop ) {
		backdrop.classList.toggle( 'is-visible', open );
	}

	if ( toggleBtn ) {
		toggleBtn.setAttribute( 'aria-expanded', String( open ) );
	}

}

function onKeyDown( e ) {
	if ( 'Escape' === e.key || 'Esc' === e.key ) {
		const drawer = document.getElementById( 'filters-content' );
		if ( drawer && drawer.classList.contains( 'is-open' ) ) {
			setDrawerOpen( false );
		}
	}
}

