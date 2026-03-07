/**
 * Product Info Script
 *
 * Handles product information interactions and animations.
 */

export function initProductInfo() {
	const productInfo = document.querySelector( '.product-info' );

	if ( ! productInfo ) {
		return;
	}

	// Animate price changes (for variations)
	const priceElement = document.getElementById( 'product-price' );
	if ( priceElement ) {
		const observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				if ( mutation.type === 'childList' || mutation.type === 'characterData' ) {
					// Add a subtle animation when price changes
					priceElement.style.transform = 'scale(1.05)';
					priceElement.style.transition = 'transform 0.2s ease';
					
					setTimeout( () => {
						priceElement.style.transform = 'scale(1)';
					}, 200 );
				}
			} );
		} );

		observer.observe( priceElement, { 
			childList: true, 
			subtree: true, 
			characterData: true 
		} );
	}

	// Animate stock status changes
	const stockElement = document.getElementById( 'product-stock' );
	if ( stockElement ) {
		const observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				if ( mutation.type === 'childList' ) {
					// Add a subtle animation when stock status changes
					stockElement.style.opacity = '0.5';
					stockElement.style.transition = 'opacity 0.3s ease';
					
					setTimeout( () => {
						stockElement.style.opacity = '1';
					}, 150 );
				}
			} );
		} );

		observer.observe( stockElement, { childList: true } );
	}

	// Add click-to-copy functionality for SKU
	const skuElement = productInfo.querySelector( '.product-sku span:last-child' );
	if ( skuElement ) {
		skuElement.style.cursor = 'pointer';
		skuElement.title = 'Click to copy SKU';
		
		skuElement.addEventListener( 'click', function () {
			const sku = this.textContent;
			
			if ( navigator.clipboard ) {
				navigator.clipboard.writeText( sku ).then( () => {
					showCopyFeedback( this );
				} );
			} else {
				// Fallback for older browsers
				const textArea = document.createElement( 'textarea' );
				textArea.value = sku;
				document.body.appendChild( textArea );
				textArea.select();
				document.execCommand( 'copy' );
				document.body.removeChild( textArea );
				showCopyFeedback( this );
			}
		} );
	}
}

function showCopyFeedback( element ) {
	const originalText = element.textContent;
	element.textContent = 'Copied!';
	element.style.color = '#10b981';
	
	setTimeout( () => {
		element.textContent = originalText;
		element.style.color = '';
	}, 1500 );
}