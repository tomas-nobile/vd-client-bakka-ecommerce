// home-popular-products.
/**
 * Product category tabs handler for the Popular Products section.
 *
 * Reads data-tab on each .pp-tab button and toggles .pp-tab--active /
 * .pp-panel--active classes; no jQuery or Bootstrap required.
 */
export function initProductTabs() {
	const tabs   = document.querySelectorAll( '.pp-tab' );
	const panels = document.querySelectorAll( '.pp-panel' );

	if ( ! tabs.length || ! panels.length ) {
		return;
	}

	tabs.forEach( ( tab ) => {
		tab.addEventListener( 'click', () => switchTab( tab, tabs, panels ) );
	} );
}

/**
 * Attach color-dot click handler to all dots inside a given container selector.
 *
 * @param {string} containerSelector CSS selector for the parent scope.
 */
function attachColorDotHandlers( containerSelector ) {
	document.querySelectorAll( containerSelector + ' .pp-color-dot' ).forEach( ( dot ) => {
		dot.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			const box = dot.closest( '.pp-feature-box' );
			const img = box?.querySelector( '.pp-feature-box__img' );
			if ( ! box || ! img ) {
				return;
			}
			const src    = dot.dataset.src;
			const srcset = dot.dataset.srcset;
			const defaultSrc    = box.dataset.defaultSrc;
			const defaultSrcset = box.dataset.defaultSrcset;

			if ( src ) {
				img.src = src;
				img.srcset = srcset || '';
			} else if ( defaultSrc ) {
				img.src = defaultSrc;
				img.srcset = defaultSrcset || '';
			}

			box.querySelectorAll( '.pp-color-dot' ).forEach( ( d ) => d.classList.remove( 'pp-color-dot--active' ) );
			dot.classList.add( 'pp-color-dot--active' );
		} );
	} );
}

/**
 * Color dot image switcher: on dot click, swap the card image to that color's variation image.
 * Scoped to Home popular products, Archive, and Related Products (single product).
 */
export function initColorDotSwitcher() {
	attachColorDotHandlers( '.popular-products-section' );
	attachColorDotHandlers( '.wp-block-etheme-archive-product-index' );
	attachColorDotHandlers( '.related-products' );
}

function switchTab( activeTab, allTabs, allPanels ) {
	const targetSlug = activeTab.dataset.tab;

	allTabs.forEach( ( tab ) => {
		const isActive = tab === activeTab;
		tab.setAttribute( 'aria-selected', isActive ? 'true' : 'false' );
		tab.classList.toggle( 'pp-tab--active', isActive );
	} );

	allPanels.forEach( ( panel ) => {
		const panelSlug = panel.id.replace( 'products-panel-', '' );
		panel.classList.toggle( 'pp-panel--active', panelSlug === targetSlug );
	} );
}
