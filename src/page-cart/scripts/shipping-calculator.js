/**
 * Shipping Calculator Script
 *
 * Handles postal code shipping calculation.
 * Designed to be easily adaptable for different countries/carriers.
 */

/**
 * Initialize shipping calculator
 */
export function initShippingCalculator() {
	const form = document.getElementById( 'shipping-calculator-form' );

	if ( ! form ) {
		return;
	}

	form.addEventListener( 'submit', handleShippingCalculation );

	// Also handle shipping method selection
	initShippingMethodSelection();
}

/**
 * Handle shipping calculation form submission
 *
 * @param {Event} e Submit event.
 */
async function handleShippingCalculation( e ) {
	e.preventDefault();

	const form = e.target;
	const button = document.getElementById( 'calc-shipping-btn' );
	const resultsContainer = document.getElementById( 'shipping-results' );
	const optionsContainer = document.getElementById( 'shipping-options' );
	const errorContainer = document.getElementById( 'shipping-error' );

	const postcode = form.querySelector( '#calc_shipping_postcode' )?.value;
	const country =
		form.querySelector( '#calc_shipping_country' )?.value || 'AR';
	const nonce = form.querySelector( '#shipping_nonce' )?.value;

	if ( ! postcode ) {
		showError( errorContainer, wp.i18n.__( 'Ingresá un código postal', 'etheme' ) );
		return;
	}

	// Show loading state
	setLoadingState( button, true );
	hideError( errorContainer );

	try {
		const response = await calculateShipping( postcode, country, nonce );

		if ( response.success && response.data.shipping_options ) {
			renderShippingOptions(
				optionsContainer,
				response.data.shipping_options
			);
			resultsContainer.classList.remove( 'hidden' );

			// Update totals if provided
			if ( response.data.cart_totals ) {
				updateCartTotals( response.data.cart_totals );
			}
		} else {
			const message =
				response.data?.message ||
				wp.i18n.__( 'No hay opciones de envío para esta ubicación', 'etheme' );
			showError( errorContainer, message );
			resultsContainer.classList.add( 'hidden' );
		}
	} catch ( error ) {
		console.error( 'Shipping calculation error:', error );
		showError(
			errorContainer,
			wp.i18n.__( 'Error al calcular el envío. Intentá de nuevo.', 'etheme' )
		);
	} finally {
		setLoadingState( button, false );
	}
}

/**
 * Calculate shipping via AJAX
 *
 * @param {string} postcode Postal code.
 * @param {string} country  Country code.
 * @param {string} nonce    Security nonce.
 * @return {Promise} AJAX response.
 */
async function calculateShipping( postcode, country, nonce ) {
	const cartBlock = document.querySelector( '.page-cart-block' );
	const ajaxUrl = cartBlock?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';

	const formData = new FormData();
	formData.append( 'action', 'etheme_calculate_shipping' );
	formData.append( 'postcode', postcode );
	formData.append( 'country', country );
	formData.append( 'nonce', nonce );

	const response = await fetch( ajaxUrl, {
		method: 'POST',
		body: formData,
	} );

	return response.json();
}

/**
 * Render shipping options in the container
 *
 * @param {Element} container Container element.
 * @param {Array}   options   Shipping options array.
 */
function renderShippingOptions( container, options ) {
	if ( ! container || ! options.length ) {
		return;
	}

	container.innerHTML = options
		.map(
			( option, index ) => `
		<label class="flex items-center justify-between p-3 border border-gray-200 rounded cursor-pointer hover:border-amber-500 transition ${ index === 0 ? 'border-amber-500 bg-amber-50' : '' }">
			<div class="flex items-center">
				<input type="radio" 
					   name="shipping_method[0]" 
					   value="${ escapeHtml( option.id ) }"
					   class="shipping-method-radio h-4 w-4 text-amber-600 focus:ring-amber-500"
					   ${ index === 0 ? 'checked' : '' } />
				<span class="ml-3 text-sm text-gray-900">${ escapeHtml( option.label ) }</span>
			</div>
			<span class="text-sm font-medium text-gray-900">
				${ option.cost > 0 ? option.cost_html : wp.i18n.__( 'Gratis', 'etheme' ) }
			</span>
		</label>
	`
		)
		.join( '' );
}

/**
 * Initialize shipping method radio selection
 */
function initShippingMethodSelection() {
	const resultsContainer = document.getElementById( 'shipping-results' );

	if ( ! resultsContainer ) {
		return;
	}

	resultsContainer.addEventListener( 'change', handleShippingMethodChange );
}

/**
 * Handle shipping method selection change
 *
 * @param {Event} e Change event.
 */
async function handleShippingMethodChange( e ) {
	const radio = e.target.closest( '.shipping-method-radio' );

	if ( ! radio ) {
		return;
	}

	// Update visual selection
	const labels = document.querySelectorAll(
		'#shipping-options label'
	);
	labels.forEach( ( label ) => {
		label.classList.remove( 'border-amber-500', 'bg-amber-50' );
	} );
	radio.closest( 'label' )?.classList.add( 'border-amber-500', 'bg-amber-50' );

	// Update shipping method via AJAX
	const cartBlock = document.querySelector( '.page-cart-block' );
	const ajaxUrl = cartBlock?.dataset.ajaxUrl || '/wp-admin/admin-ajax.php';

	const formData = new FormData();
	formData.append( 'action', 'etheme_update_shipping_method' );
	formData.append( 'shipping_method', radio.value );

	try {
		const response = await fetch( ajaxUrl, {
			method: 'POST',
			body: formData,
		} );

		const data = await response.json();

		if ( data.success && data.data.cart_totals ) {
			updateCartTotals( data.data.cart_totals );
		}
	} catch ( error ) {
		console.error( 'Error updating shipping method:', error );
	}
}

/**
 * Update cart totals display
 *
 * @param {Object} totals Cart totals object.
 */
function updateCartTotals( totals ) {
	const subtotalEl = document.querySelector( '.subtotal-value' );
	const shippingEl = document.querySelector( '.shipping-value' );
	const discountEl = document.querySelector( '.discount-value' );
	const taxEl = document.querySelector( '.tax-value' );
	const totalEl = document.querySelector( '.total-value' );

	if ( subtotalEl && totals.subtotal_html ) {
		subtotalEl.innerHTML = totals.subtotal_html;
	}
	if ( shippingEl && totals.shipping_html ) {
		shippingEl.innerHTML = totals.shipping_html;
	}
	if ( discountEl && totals.discount_html ) {
		discountEl.innerHTML = '-' + totals.discount_html;
	}
	if ( taxEl && totals.tax_html ) {
		taxEl.innerHTML = totals.tax_html;
	}
	if ( totalEl && totals.total_html ) {
		totalEl.innerHTML = totals.total_html;
	}
}

/**
 * Set button loading state
 *
 * @param {Element} button  Button element.
 * @param {boolean} loading Loading state.
 */
function setLoadingState( button, loading ) {
	if ( ! button ) {
		return;
	}

	const text = button.querySelector( '.button-text' );
	const spinner = button.querySelector( '.loading-spinner' );

	button.disabled = loading;

	if ( text ) {
		text.classList.toggle( 'hidden', loading );
	}
	if ( spinner ) {
		spinner.classList.toggle( 'hidden', ! loading );
	}
}

/**
 * Show error message
 *
 * @param {Element} container Error container.
 * @param {string}  message   Error message.
 */
function showError( container, message ) {
	if ( container ) {
		container.textContent = message;
		container.classList.remove( 'hidden' );
	}
}

/**
 * Hide error message
 *
 * @param {Element} container Error container.
 */
function hideError( container ) {
	if ( container ) {
		container.classList.add( 'hidden' );
	}
}

/**
 * Escape HTML for safe rendering
 *
 * @param {string} str String to escape.
 * @return {string} Escaped string.
 */
function escapeHtml( str ) {
	const div = document.createElement( 'div' );
	div.textContent = str;
	return div.innerHTML;
}

// Export updateCartTotals for use in other modules
export { updateCartTotals };
