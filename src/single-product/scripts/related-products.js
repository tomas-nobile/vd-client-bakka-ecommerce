/**
 * Related Products Script
 *
 * Handles related products interactions and animations.
 */

export function initRelatedProducts() {
	const relatedProducts = document.querySelector( '.related-products' );

	if ( ! relatedProducts ) {
		return;
	}

	// Add intersection observer for scroll animations
	const productCards = relatedProducts.querySelectorAll( '.product-card' );
	
	if ( productCards.length > 0 ) {
		const observer = new IntersectionObserver( 
			( entries ) => {
				entries.forEach( ( entry ) => {
					if ( entry.isIntersecting ) {
						entry.target.style.opacity = '1';
						entry.target.style.transform = 'translateY(0)';
					}
				} );
			},
			{
				threshold: 0.1,
				rootMargin: '0px 0px -50px 0px'
			}
		);

		productCards.forEach( ( card, index ) => {
			// Initial state
			card.style.opacity = '0';
			card.style.transform = 'translateY(20px)';
			card.style.transition = `opacity 0.6s ease ${index * 0.1}s, transform 0.6s ease ${index * 0.1}s`;
			
			observer.observe( card );
		} );
	}

	// Add hover effects to related product cards
	productCards.forEach( ( card ) => {
		const image = card.querySelector( 'img' );
		
		if ( image ) {
			card.addEventListener( 'mouseenter', function () {
				image.style.transform = 'scale(1.05)';
			} );

			card.addEventListener( 'mouseleave', function () {
				image.style.transform = 'scale(1)';
			} );
		}
	} );
}