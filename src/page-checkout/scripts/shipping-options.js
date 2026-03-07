/**
 * Keep shipping option cards selected state in sync.
 */

function syncShippingOptions( root ) {
	const options = root.querySelectorAll( '[data-shipping-option]' );

	options.forEach( ( option ) => {
		const input = option.querySelector( 'input.shipping_method' );
		const checked = Boolean( input && input.checked );
		option.classList.toggle( 'is-selected', checked );
	} );
}

export function initCheckoutShippingOptions() {
	const root = document.querySelector( '.page-checkout-block' );
	if ( ! root ) {
		return;
	}

	syncShippingOptions( root );

	root.addEventListener( 'change', function ( event ) {
		if ( event.target.matches( 'input.shipping_method' ) ) {
			syncShippingOptions( root );
		}
	} );
}
