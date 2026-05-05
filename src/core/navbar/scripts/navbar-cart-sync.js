/**
 * Navbar Cart Sync
 *
 * Reconciles the cart badge with the server on every page show. Covers
 * bfcache restores, normal navigations served from HTTP/page cache, and
 * races between WC session persistence and the next page render.
 */

export function initCartCountSync() {
	window.addEventListener( 'pageshow', function () {
		syncCartCount();
	} );
}

async function syncCartCount() {
	const header  = document.querySelector( '.wp-block-etheme-core-navbar' );
	const ajaxUrl = header?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';

	try {
		const body = new FormData();
		body.append( 'action', 'etheme_get_cart_count' );

		const res  = await fetch( ajaxUrl, { method: 'POST', body } );
		const data = await res.json();

		if ( data.success && typeof data.data.count === 'number' ) {
			updateBadge( data.data.count );
		}
	} catch {
		// Silently ignore network errors — stale badge is better than a broken UI.
	}
}

function updateBadge( count ) {
	try {
		sessionStorage.setItem( 'etheme_cart_count', String( count ) );
	} catch {}

	document.querySelectorAll( '.etheme-navbar-action__badge' ).forEach( ( el ) => {
		el.textContent = String( count );
		el.classList.toggle( 'etheme-navbar-action__badge--visible', count > 0 );
	} );
}
