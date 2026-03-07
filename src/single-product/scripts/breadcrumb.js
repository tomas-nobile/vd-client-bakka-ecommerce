/**
 * Breadcrumb Script
 *
 * Handles breadcrumb navigation interactions (if any needed).
 */

export function initBreadcrumb() {
	const breadcrumb = document.querySelector( '.product-breadcrumb' );

	if ( ! breadcrumb ) {
		return;
	}

	// Add smooth scrolling to breadcrumb links
	const links = breadcrumb.querySelectorAll( 'a' );
	
	links.forEach( ( link ) => {
		link.addEventListener( 'click', function ( e ) {
			// Add a subtle animation on click
			this.style.transform = 'scale(0.95)';
			setTimeout( () => {
				this.style.transform = '';
			}, 150 );
		} );
	} );
}