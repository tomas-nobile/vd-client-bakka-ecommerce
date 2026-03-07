/**
 * Product Variations Script
 *
 * Handles variable product attribute selection and price/stock updates.
 */

let variationData = null;
let galleryDefaults = null;

export function initVariations() {
	const variationsContainer = document.getElementById( 'product-variations' );
	const variationDataEl = document.getElementById( 'variation-data' );
	const variationsForm = document.getElementById( 'variations-form' );

	if ( ! variationsContainer || ! variationDataEl ) {
		return;
	}

	// Parse variation data
	try {
		variationData = JSON.parse( variationDataEl.textContent );
	} catch ( e ) {
		console.error( 'Failed to parse variation data:', e );
		return;
	}

	// Get all variation selects
	const selects = variationsContainer.querySelectorAll( '.variation-select' );
	const resetLink = document.getElementById( 'reset-variations' );

	cacheGalleryDefaults();

	// Listen for changes on each select
	selects.forEach( ( select ) => {
		select.addEventListener( 'change', handleVariationChange );
	} );

	// Reset link handler
	if ( resetLink ) {
		resetLink.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			resetVariations();
		} );
	}

	// Validate before submit
	if ( variationsForm ) {
		variationsForm.addEventListener( 'submit', ( e ) => {
			if ( ! validateVariations() ) {
				e.preventDefault();
			}
		} );
	}

	applyDefaultSelections( selects );

	// Check initial state
	handleVariationChange();
}

function handleVariationChange() {
	const selects = document.querySelectorAll( '.variation-select' );
	const selectedAttributes = {};
	let allSelected = true;

	// Collect selected attributes
	selects.forEach( ( select ) => {
		const attrName = select.dataset.attribute_name;
		const value = select.value;

		if ( value ) {
			selectedAttributes[ attrName ] = value;
		} else {
			allSelected = false;
		}
	} );

	// Show/hide reset link
	const resetLink = document.getElementById( 'reset-variations' );
	const hasSelection = Object.keys( selectedAttributes ).length > 0;
	if ( resetLink ) {
		resetLink.classList.toggle( 'hidden', ! hasSelection );
	}

	// Hide validation message when user makes a selection
	const validationMessage = document.getElementById( 'variation-message' );
	if ( validationMessage ) {
		validationMessage.classList.add( 'hidden' );
	}

	if ( allSelected ) {
		// Find matching variation
		const matchingVariation = findMatchingVariation( selectedAttributes );

		if ( matchingVariation ) {
			updateProductDisplay( matchingVariation );
			updateVariationId( matchingVariation.variation_id );
		} else {
			// No matching variation - show unavailable
			showUnavailable();
		}
	} else {
		// Not all attributes selected - reset to default
		resetProductDisplay();
	}

	updateOptionAvailability( selects, selectedAttributes );
}

function findMatchingVariation( selectedAttributes ) {
	if ( ! variationData || ! variationData.variations ) {
		return null;
	}

	return variationData.variations.find( ( variation ) => {
		// Check if all selected attributes match this variation
		for ( const [ attrName, attrValue ] of Object.entries( selectedAttributes ) ) {
			const variationAttrValue = variation.attributes[ attrName ];

			// Empty string in variation means "any" value is accepted
			if ( variationAttrValue !== '' && variationAttrValue !== attrValue ) {
				return false;
			}
		}
		return true;
	} );
}

function updateProductDisplay( variation ) {
	// Update price
	const priceEl = document.getElementById( 'product-price' );
	if ( priceEl && variation.price_html ) {
		priceEl.innerHTML = variation.price_html;
	}

	// Update stock status
	const stockEl = document.getElementById( 'product-stock' );
	if ( stockEl ) {
		updateStockDisplay( stockEl, variation );
	}

	// Update add to cart button
	const addToCartBtn = document.getElementById( 'add-to-cart-button' );
	if ( addToCartBtn ) {
		if ( variation.is_in_stock ) {
			addToCartBtn.disabled = false;
			addToCartBtn.classList.remove( 'opacity-50', 'cursor-not-allowed' );
			addToCartBtn.textContent = addToCartBtn.dataset.addText || 'Add to Cart';
		} else {
			addToCartBtn.disabled = true;
			addToCartBtn.classList.add( 'opacity-50', 'cursor-not-allowed' );
			addToCartBtn.textContent = addToCartBtn.dataset.outOfStockText || 'Out of Stock';
		}
	}

	// Update main image if variation has one
	if ( variation.image && variation.image.src ) {
		updateGalleryForVariation( variation );
	}
}

function updateStockDisplay( stockEl, variation ) {
	let statusClass = '';
	let statusText = '';
	let iconPath = '';

	if ( variation.is_in_stock ) {
		statusClass = 'text-green-600 bg-green-100';
		statusText = 'In Stock';
		iconPath =
			'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z';

		if ( variation.stock_quantity !== null && variation.stock_quantity > 0 ) {
			statusText = `${ variation.stock_quantity } in stock`;
		}
	} else if ( variation.stock_status === 'onbackorder' ) {
		statusClass = 'text-yellow-600 bg-yellow-100';
		statusText = 'Available on Backorder';
		iconPath =
			'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z';
	} else {
		statusClass = 'text-red-600 bg-red-100';
		statusText = 'Out of Stock';
		iconPath =
			'M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z';
	}

	stockEl.innerHTML = `
		<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${ statusClass }">
			<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
				<path fill-rule="evenodd" d="${ iconPath }" clip-rule="evenodd" />
			</svg>
			${ statusText }
		</span>
	`;
}

function showUnavailable() {
	const stockEl = document.getElementById( 'product-stock' );
	if ( stockEl ) {
		stockEl.innerHTML = `
			<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-gray-600 bg-gray-100">
				<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
					<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
				</svg>
				Unavailable
			</span>
		`;
	}

	const addToCartBtn = document.getElementById( 'add-to-cart-button' );
	if ( addToCartBtn ) {
		addToCartBtn.disabled = true;
		addToCartBtn.classList.add( 'opacity-50', 'cursor-not-allowed' );
		addToCartBtn.textContent = 'Unavailable';
	}

	updateVariationId( '' );
	restoreGalleryDefaults();
}

function resetProductDisplay() {
	// Reset variation ID
	updateVariationId( '' );

	// Disable add to cart until all options selected
	const addToCartBtn = document.getElementById( 'add-to-cart-button' );
	if ( addToCartBtn ) {
		addToCartBtn.disabled = true;
		addToCartBtn.classList.add( 'opacity-50', 'cursor-not-allowed' );
		addToCartBtn.textContent = 'Select options';
	}

	restoreGalleryDefaults();
}

function resetVariations() {
	const selects = document.querySelectorAll( '.variation-select' );
	selects.forEach( ( select ) => {
		select.value = '';
	} );

	const resetLink = document.getElementById( 'reset-variations' );
	if ( resetLink ) {
		resetLink.classList.add( 'hidden' );
	}

	resetProductDisplay();
}

function applyDefaultSelections( selects ) {
	selects.forEach( ( select ) => {
		if ( select.value ) {
			return;
		}

		const firstOption = Array.from( select.options ).find( ( option ) => option.value );
		if ( firstOption ) {
			select.value = firstOption.value;
		}
	} );
}

function cacheGalleryDefaults() {
	const mainImage = document.getElementById( 'main-gallery-image' );
	const thumbnailsContainer = document.getElementById( 'gallery-thumbnails' );
	const modalDataEl = document.getElementById( 'modal-gallery-data' );

	if ( ! mainImage || ! modalDataEl ) {
		return;
	}

	galleryDefaults = {
		main: {
			src: mainImage.src,
			srcset: mainImage.srcset,
			imageId: mainImage.dataset.imageId,
			fullSrc: mainImage.dataset.fullSrc,
		},
		thumbnailsHtml: thumbnailsContainer ? thumbnailsContainer.innerHTML : null,
		modalData: modalDataEl.textContent,
	};
}

function restoreGalleryDefaults() {
	if ( ! galleryDefaults ) {
		return;
	}

	const mainImage = document.getElementById( 'main-gallery-image' );
	const thumbnailsContainer = document.getElementById( 'gallery-thumbnails' );
	const modalDataEl = document.getElementById( 'modal-gallery-data' );

	if ( mainImage && galleryDefaults.main ) {
		mainImage.src = galleryDefaults.main.src;
		if ( galleryDefaults.main.srcset ) {
			mainImage.srcset = galleryDefaults.main.srcset;
		}
		mainImage.dataset.imageId = galleryDefaults.main.imageId || '';
		mainImage.dataset.fullSrc = galleryDefaults.main.fullSrc || '';
	}

	if ( thumbnailsContainer && galleryDefaults.thumbnailsHtml !== null ) {
		thumbnailsContainer.innerHTML = galleryDefaults.thumbnailsHtml;
		thumbnailsContainer.classList.remove( 'hidden' );
		bindVariationThumbnailClicks( thumbnailsContainer );
	}

	if ( modalDataEl && galleryDefaults.modalData ) {
		modalDataEl.textContent = galleryDefaults.modalData;
	}
}

function updateGalleryForVariation( variation ) {
	const mainImage = document.getElementById( 'main-gallery-image' );
	const thumbnailsContainer = document.getElementById( 'gallery-thumbnails' );
	const modalDataEl = document.getElementById( 'modal-gallery-data' );

	if ( ! mainImage || ! modalDataEl ) {
		return;
	}

	const imageId = variation.variation_id;
	const imageSrc = variation.image.src;
	const imageThumb = variation.image.thumb || variation.image.src;

	mainImage.src = imageSrc;
	if ( variation.image.srcset ) {
		mainImage.srcset = variation.image.srcset;
	}
	mainImage.dataset.imageId = String( imageId );
	mainImage.dataset.fullSrc = imageSrc;

	modalDataEl.textContent = JSON.stringify( [
		{
			id: imageId,
			full: imageSrc,
			large: imageSrc,
			alt: '',
		},
	] );

	if ( thumbnailsContainer ) {
		thumbnailsContainer.innerHTML = '';
		thumbnailsContainer.classList.add( 'hidden' );
	}
}

function bindVariationThumbnailClicks( container ) {
	const mainImageContainer = document.getElementById( 'product-main-image' );
	if ( ! mainImageContainer ) {
		return;
	}

	const thumbnails = container.querySelectorAll( '[data-thumbnail]' );
	thumbnails.forEach( ( thumbnail ) => {
		thumbnail.addEventListener( 'click', () => {
			mainImageContainer.click();
		} );
	} );
}

function updateOptionAvailability( selects, selectedAttributes ) {
	if ( ! variationData || ! variationData.variations ) {
		return;
	}

	selects.forEach( ( select ) => {
		const attrName = select.dataset.attribute_name;

		Array.from( select.options ).forEach( ( option ) => {
			if ( ! option.value ) {
				option.disabled = false;
				return;
			}

			const testSelection = { ...selectedAttributes, [ attrName ]: option.value };
			const isValid = variationData.variations.some( ( variation ) => {
				return Object.entries( testSelection ).every( ( [ key, value ] ) => {
					const variationValue = variation.attributes[ key ];
					return variationValue === '' || variationValue === value;
				} );
			} );

			option.disabled = ! isValid;
		} );
	} );
}

function getAddToCartForm() {
	return document.getElementById( 'add-to-cart-form' );
}

function updateVariationId( value ) {
	const variationIdInput = document.getElementById( 'variation_id' );
	if ( variationIdInput ) {
		variationIdInput.value = value || '';
	}
}

function syncSelectedAttributesToCart() {
	const addToCartForm = getAddToCartForm();
	if ( ! addToCartForm ) {
		return;
	}

	const selects = document.querySelectorAll( '.variation-select' );
	selects.forEach( ( select ) => {
		const name = select.name;
		if ( ! name ) {
			return;
		}

		let input = addToCartForm.querySelector( `input[name="${ name }"]` );
		if ( select.value ) {
			if ( ! input ) {
				input = document.createElement( 'input' );
				input.type = 'hidden';
				input.name = name;
				addToCartForm.appendChild( input );
			}
			input.value = select.value;
		} else if ( input ) {
			input.remove();
		}
	} );
}

/**
 * Validate that all variations are selected before form submission
 *
 * @return {boolean} True if all variations are selected
 */
export function validateVariations() {
	const selects = document.querySelectorAll( '.variation-select' );
	let allSelected = true;

	selects.forEach( ( select ) => {
		if ( ! select.value ) {
			allSelected = false;
			select.classList.add( 'border-red-500' );
		} else {
			select.classList.remove( 'border-red-500' );
		}
	} );

	const validationMessage = document.getElementById( 'variation-message' );
	if ( validationMessage ) {
		validationMessage.classList.toggle( 'hidden', allSelected );
	}

	return allSelected;
}
