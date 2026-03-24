/**
 * Parent category chips: horizontal scroll + prev/next on mobile/tablet (< lg).
 */

const BLOCK_SELECTOR = '.wp-block-etheme-archive-product-index';
const WRAP_SELECTOR = '[data-archive-category-nav]';
const SCROLL_SELECTOR = '.archive-category-scroll';
const PREV_SELECTOR = '[data-archive-category-prev]';
const NEXT_SELECTOR = '[data-archive-category-next]';

const mqMobileTablet = window.matchMedia( '(max-width: 1023px)' );

/**
 * @param {HTMLElement} scrollEl
 * @returns {number}
 */
function scrollStep( scrollEl ) {
	return Math.max( 120, Math.round( scrollEl.clientWidth * 0.85 ) );
}

/**
 * @param {HTMLElement} wrap
 * @param {HTMLElement} scrollEl
 * @param {HTMLButtonElement} prevBtn
 * @param {HTMLButtonElement} nextBtn
 */
function updateNavState( wrap, scrollEl, prevBtn, nextBtn ) {
	if ( ! mqMobileTablet.matches ) {
		wrap.classList.remove( 'is-scrollable' );
		prevBtn.hidden = true;
		nextBtn.hidden = true;
		return;
	}

	const overflow = scrollEl.scrollWidth > scrollEl.clientWidth + 1;
	wrap.classList.toggle( 'is-scrollable', overflow );

	if ( ! overflow ) {
		prevBtn.hidden = true;
		nextBtn.hidden = true;
		return;
	}

	const left = scrollEl.scrollLeft;
	const maxScroll = scrollEl.scrollWidth - scrollEl.clientWidth;
	prevBtn.hidden = left <= 1;
	nextBtn.hidden = left >= maxScroll - 1;
}

/**
 * Initialize category chip scroll + nav for one block instance.
 *
 * @param {HTMLElement} block Root `.wp-block-etheme-archive-product-index`.
 */
function bindArchiveCategoryChips( block ) {
	const wrap = block.querySelector( WRAP_SELECTOR );
	if ( ! wrap ) {
		return;
	}

	const scrollEl = wrap.querySelector( SCROLL_SELECTOR );
	const prevBtn = wrap.querySelector( PREV_SELECTOR );
	const nextBtn = wrap.querySelector( NEXT_SELECTOR );

	if ( ! scrollEl || ! prevBtn || ! nextBtn ) {
		return;
	}

	const update = () => {
		updateNavState( wrap, scrollEl, prevBtn, nextBtn );
	};

	prevBtn.addEventListener( 'click', () => {
		scrollEl.scrollBy( { left: -scrollStep( scrollEl ), behavior: 'smooth' } );
	} );

	nextBtn.addEventListener( 'click', () => {
		scrollEl.scrollBy( { left: scrollStep( scrollEl ), behavior: 'smooth' } );
	} );

	scrollEl.addEventListener( 'scroll', update, { passive: true } );
	window.addEventListener( 'resize', update );

	if ( typeof mqMobileTablet.addEventListener === 'function' ) {
		mqMobileTablet.addEventListener( 'change', update );
	} else {
		mqMobileTablet.addListener( update );
	}

	if ( document.fonts && document.fonts.ready ) {
		document.fonts.ready.then( update );
	}

	update();
}

/**
 * Find archive blocks and bind chips behavior.
 */
export function initArchiveCategoryChips() {
	document.querySelectorAll( BLOCK_SELECTOR ).forEach( bindArchiveCategoryChips );
}
