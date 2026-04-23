// page-posteos-index/view.js
/**
 * Frontend JavaScript for the /posteos page block.
 *
 * - Initializes the shared blog modal (from core, uses event delegation).
 * - Initializes scroll fade-up animation.
 * - Handles category filter chips + "Mostrar más" AJAX load-more.
 */

import { initBlogModal } from '../../core/scripts/blog-modal.js';
import { initFadeUp } from '../../front-page/scripts/fp-fade-up.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initBlogModal();
	initFadeUp( '.wp-block-etheme-page-posteos-index' );
	initPosteos();
} );

// ─── Posteos: filter chips + load more ───────────────────────────────────────

function initPosteos() {
	const wrapper = document.querySelector( '.wp-block-etheme-page-posteos-index' );
	if ( ! wrapper ) return;

	const grid    = document.getElementById( 'posteos-cards-grid' );
	const btn     = document.getElementById( 'posteos-load-more' );
	const ajaxurl = wrapper.dataset.ajaxurl || '';
	const nonce   = wrapper.dataset.nonce   || '';

	const state = {
		page:     1,
		perPage:  parseInt( btn?.dataset.perPage, 10 ) || 15,
		total:    parseInt( btn?.dataset.total,   10 ) || 0,
		category: wrapper.dataset.activeCategory || '',
		loading:  false,
	};

	// ── Filter chips ──────────────────────────────────────────────────────────
	const chips = wrapper.querySelectorAll( '.posteos-filter__chip' );

	chips.forEach( chip => {
		chip.addEventListener( 'click', async () => {
			if ( state.loading ) return;

			const next = chip.dataset.category || '';
			if ( next === state.category ) return;

			chips.forEach( c => {
				c.classList.remove( 'posteos-filter__chip--active' );
				c.setAttribute( 'aria-selected', 'false' );
			} );
			chip.classList.add( 'posteos-filter__chip--active' );
			chip.setAttribute( 'aria-selected', 'true' );

			state.category = next;
			state.page     = 1;

			if ( grid ) grid.classList.add( 'is-switching' );
			await fetchPosts( true );
		} );
	} );

	// ── Load more button ──────────────────────────────────────────────────────
	if ( btn ) {
		btn.addEventListener( 'click', async () => {
			if ( state.loading ) return;
			await fetchPosts( false );
		} );
	}

	// ── Shared fetch ──────────────────────────────────────────────────────────
	async function fetchPosts( replace ) {
		state.loading = true;
		if ( btn ) {
			btn.disabled    = true;
			btn.textContent = '...';
		}

		const pageToFetch = replace ? 1 : state.page + 1;

		const formData = new FormData();
		formData.append( 'action',   'etheme_posteos_load_more' );
		formData.append( 'nonce',    nonce );
		formData.append( 'page',     String( pageToFetch ) );
		formData.append( 'category', state.category );

		try {
			const res  = await fetch( ajaxurl, { method: 'POST', body: formData } );
			const data = await res.json();

			if ( data.success ) {
				if ( replace ) {
					grid.innerHTML = data.data.html || '';
					requestAnimationFrame( () => requestAnimationFrame( () => grid.classList.remove( 'is-switching' ) ) );
				} else if ( data.data.html ) {
					const tmp = document.createElement( 'div' );
					tmp.innerHTML = data.data.html;
					while ( tmp.firstChild ) {
						grid.appendChild( tmp.firstChild );
					}
				}

				state.page  = pageToFetch;
				state.total = data.data.total ?? state.total;
				updateBtn();
			}
		} catch ( _e ) {
			// Network error: restore button so user can retry.
		}

		state.loading = false;
		if ( btn ) {
			btn.disabled    = false;
			btn.textContent = 'Mostrar más';
			updateBtn();
		}
	}

	function updateBtn() {
		if ( ! btn ) return;
		btn.style.display = ( state.page * state.perPage ) >= state.total ? 'none' : '';
	}

	updateBtn();
}
