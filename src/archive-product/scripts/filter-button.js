/**
 * Product Filter Button Component Script
 * 
 * Handles filter menu toggle button functionality.
 * Shows/hides the filter menu and rotates the arrow icon.
 */

export function initFilterButton() {
	const toggleBtn = document.getElementById( 'toggle-filters' );
	const filtersContent = document.getElementById( 'filters-content' );
	const closeBtn = document.getElementById( 'close-filters' );
	if ( ! toggleBtn || ! filtersContent ) {
		return;
	}
	
	toggleBtn.addEventListener( 'click', function () {
		const shouldOpen = filtersContent.classList.contains( 'hidden' );
		setFiltersOpen( filtersContent, toggleBtn, shouldOpen );
	} );
	
	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', function () {
			setFiltersOpen( filtersContent, toggleBtn, false );
		} );
	}
}

function setFiltersOpen( filtersContent, toggleBtn, shouldOpen ) {
	filtersContent.classList.toggle( 'hidden', ! shouldOpen );
	const arrow = toggleBtn.querySelector( '.arrow' );
	if ( arrow ) {
		arrow.classList.toggle( 'rotate-180', shouldOpen );
	}
}

