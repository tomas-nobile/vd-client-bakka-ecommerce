// page-posteos-index/view.js
/**
 * Frontend JavaScript for the /posteos page block.
 *
 * - Initializes the shared blog modal (from core, uses event delegation).
 * - Initializes scroll fade-up animation.
 * - Handles "Mostrar más" AJAX load-more button.
 */

import { initBlogModal } from '../../core/scripts/blog-modal.js';
import { initFadeUp } from '../../front-page/scripts/fp-fade-up.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initBlogModal();
	initFadeUp( '.wp-block-etheme-page-posteos-index' );
	initLoadMore();
} );

// ─── Load more ────────────────────────────────────────────────────────────────

function initLoadMore() {
	const btn = document.getElementById( 'posteos-load-more' );
	if ( ! btn ) return;

	const grid    = document.getElementById( 'posteos-cards-grid' );
	const wrapper = btn.closest( '.wp-block-etheme-page-posteos-index' );

	let currentPage = 1;
	const perPage   = parseInt( btn.dataset.perPage, 10 ) || 15;
	const total     = parseInt( btn.dataset.total, 10 ) || 0;
	const ajaxurl   = wrapper?.dataset.ajaxurl || '';
	const nonce     = wrapper?.dataset.nonce || '';

	updateButtonState();

	btn.addEventListener( 'click', async () => {
		if ( btn.dataset.loading === 'true' ) return;

		btn.dataset.loading = 'true';
		btn.textContent     = '...';
		btn.disabled        = true;

		try {
			const formData = new FormData();
			formData.append( 'action', 'etheme_posteos_load_more' );
			formData.append( 'nonce', nonce );
			formData.append( 'page', String( currentPage + 1 ) );

			const res  = await fetch( ajaxurl, { method: 'POST', body: formData } );
			const data = await res.json();

			if ( data.success && data.data.html ) {
				const tmp = document.createElement( 'div' );
				tmp.innerHTML = data.data.html;
				while ( tmp.firstChild ) {
					grid.appendChild( tmp.firstChild );
				}
				currentPage++;
			}
		} catch ( _e ) {
			// Network error: restore button so user can retry.
		}

		btn.dataset.loading = 'false';
		btn.disabled        = false;
		btn.textContent     = 'Mostrar más';
		updateButtonState();
	} );

	function updateButtonState() {
		const loaded = currentPage * perPage;
		if ( loaded >= total ) {
			btn.style.display = 'none';
		}
	}
}
