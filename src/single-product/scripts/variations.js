/**
 * Product Variations Script
 *
 * Handles variable product attribute selection and price/stock updates.
 */

import { updateMainImageFromThumbnail } from './gallery.js';

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
		// Remove invalid highlight as user interacts.
		select.addEventListener( 'change', () => {
			const wrapper = select.closest( '.variation-input' );
			if ( wrapper ) {
				wrapper.classList.remove( 'border-red-500', 'bg-red-50' );
			}
		} );
	} );

	initCustomVariationDropdowns( variationsContainer );

	// Reset link handler
	if ( resetLink ) {
		resetLink.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			resetVariations();
		} );
	}

	// AJAX add to cart — always prevent native submit to avoid plugin interception
	if ( variationsForm ) {
		variationsForm.addEventListener( 'submit', async ( e ) => {
			e.preventDefault();

			if ( ! validateVariations() ) {
				return;
			}

			const addBtn     = document.getElementById( 'add-to-cart-button' );
			const buttonText = addBtn?.querySelector( '.button-text' );
			const addingText = addBtn?.dataset.addingText || 'Agregando...';
			const addedText  = addBtn?.dataset.addedText  || 'Agregado';
			const addText    = addBtn?.dataset.addText    || 'Agregar al carrito';

			if ( buttonText ) {
				buttonText.textContent = addingText;
			}
			if ( addBtn ) {
				addBtn.disabled = true;
			}

			const block       = document.querySelector( '.wp-block-etheme-single-product-index' );
			const wcAjaxUrl   = block?.dataset.wcAddToCartUrl || ( window.location.origin + '/?wc-ajax=add_to_cart' );
			const productId   = variationsForm.dataset.product_id;
			const variationId = document.getElementById( 'variation_id' )?.value || '';
			const qtyInput    = document.getElementById( 'quantity' );
			const quantity    = qtyInput ? qtyInput.value : '1';

			const formData = new FormData();
			formData.append( 'add-to-cart', productId );
			formData.append( 'product_id', productId );
			formData.append( 'variation_id', variationId );
			formData.append( 'quantity', quantity );

			variationsContainer.querySelectorAll( '.variation-select' ).forEach( ( sel ) => {
				if ( sel.name && sel.value ) {
					formData.append( sel.name, sel.value );
				}
			} );

			try {
				const response = await fetch( wcAjaxUrl, { method: 'POST', body: formData } );
				const data     = await response.json();

				if ( ! data.error ) {
					if ( buttonText ) {
						buttonText.textContent = addedText;
					}
					if ( addBtn ) {
						addBtn.classList.add( 'bg-green-600' );
						addBtn.classList.remove( 'bg-[#2b5756]' );
					}
					updateNavbarCountVariation( parseInt( quantity, 10 ) || 1 );

					setTimeout( () => {
						if ( buttonText ) {
							buttonText.textContent = addText;
						}
						if ( addBtn ) {
							addBtn.disabled = false;
							addBtn.classList.remove( 'bg-green-600' );
							addBtn.classList.add( 'bg-[#2b5756]' );
						}
					}, 2000 );
				} else {
					if ( buttonText ) {
						buttonText.textContent = addText;
					}
					if ( addBtn ) {
						addBtn.disabled = false;
					}
				}
			} catch {
				if ( buttonText ) {
					buttonText.textContent = addText;
				}
				if ( addBtn ) {
					addBtn.disabled = false;
				}
			}
		} );
	}

	applyDefaultSelections( selects );

	// Check initial state
	handleVariationChange();
}

function initCustomVariationDropdowns( container ) {
	const dropdowns = container.querySelectorAll( '[data-etheme-variation-dropdown]' );
	if ( ! dropdowns.length ) {
		return;
	}

	dropdowns.forEach( ( dd ) => {
		const btn = dd.querySelector( '[data-etheme-dd-button]' );
		const menu = dd.querySelector( '[data-etheme-dd-menu]' );
		const label = dd.querySelector( '[data-etheme-dd-label]' );
		if ( ! btn || ! menu || ! label ) {
			return;
		}
		if ( dd.dataset.ddBound === '1' ) {
			return;
		}
		dd.dataset.ddBound = '1';

		const targetId = btn.dataset.targetSelect;
		const select = targetId ? document.getElementById( targetId ) : null;
		if ( ! select ) {
			return;
		}

		function close() {
			menu.classList.add( 'hidden' );
			btn.setAttribute( 'aria-expanded', 'false' );
		}

		function open() {
			menu.classList.remove( 'hidden' );
			btn.setAttribute( 'aria-expanded', 'true' );
		}

		btn.addEventListener( 'click', ( e ) => {
			e.preventDefault();
			e.stopPropagation();
			const isOpen = ! menu.classList.contains( 'hidden' );
			if ( isOpen ) {
				close();
			} else {
				// Close any other open dropdowns
				container.querySelectorAll( '[data-etheme-dd-menu]' ).forEach( ( other ) => {
					other.classList.add( 'hidden' );
				} );
				open();
			}
		} );

		menu.querySelectorAll( '[data-etheme-dd-option]' ).forEach( ( optBtn ) => {
			optBtn.addEventListener( 'click', ( e ) => {
				e.preventDefault();
				e.stopPropagation();
				const value = optBtn.dataset.value || '';
				select.value = value;
				label.textContent = optBtn.textContent || label.textContent;
				select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
				close();
			} );
		} );

		document.addEventListener( 'click', ( e ) => {
			if ( dd.contains( e.target ) ) {
				return;
			}
			close();
		} );

		document.addEventListener( 'keydown', ( e ) => {
			if ( 'Escape' === e.key ) {
				close();
			}
		} );
	} );
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

	updatePurchaseButtonsState( {
		disabled: ! variation.is_in_stock,
		text: variation.is_in_stock
			? undefined
			: ( getButtonDataText( 'outOfStockText', 'Sin stock' ) ),
	} );
	initLockedPurchaseHandlers();
	setPurchaseButtonsLocked( false );

	setVariationMessageVisible( false );
	// No inline message; rely on tooltip + highlight/shake.

	// Update main image if variation has one
	if ( variation.image && variation.image.src ) {
		updateGalleryForVariation( variation );
	}
}

function setVariationMessageVisible( visible ) {
	const msg = document.getElementById( 'variation-message' );
	if ( ! msg ) {
		return;
	}

	msg.classList.toggle( 'hidden', ! visible );

	if ( visible ) {
		// Keep copy short and action-oriented.
		msg.textContent = 'Seleccioná tus opciones para continuar.';
	}
}

function getButtonDataText( key, fallback ) {
	const btn = document.getElementById( 'add-to-cart-button' );
	if ( ! btn ) {
		return fallback;
	}
	return btn.dataset[ key ] || fallback;
}

function setButtonText( button, text ) {
	if ( ! button ) {
		return;
	}
	const span = button.querySelector( '.button-text' );
	if ( span ) {
		span.textContent = text;
		return;
	}
	button.textContent = text;
}

function updatePurchaseButtonsState( { disabled, text } ) {
	const buttons = [
		document.getElementById( 'add-to-cart-button' ),
		document.getElementById( 'buy-now-button' ),
	].filter( Boolean );

	buttons.forEach( ( btn ) => {
		const isDisabled = Boolean( disabled );
		btn.disabled = isDisabled;
		btn.dataset.purchaseLocked = '0';
		btn.setAttribute( 'aria-disabled', isDisabled ? 'true' : 'false' );
		btn.classList.toggle( 'opacity-50', isDisabled );
		btn.classList.toggle( 'cursor-not-allowed', isDisabled );

		if ( typeof text === 'string' ) {
			setButtonText( btn, text );
			return;
		}

		// Restore each button's default text when enabling.
		if ( ! disabled ) {
			const defaultText = btn.dataset.addText || btn.textContent || 'Agregar al carrito';
			setButtonText( btn, defaultText );
		}
	} );
}

function setPurchaseButtonsLocked( locked ) {
	const buttons = [
		document.getElementById( 'add-to-cart-button' ),
		document.getElementById( 'buy-now-button' ),
	].filter( Boolean );

	buttons.forEach( ( btn ) => {
		if ( btn.disabled ) {
			// Disabled (e.g. out of stock) always wins.
			return;
		}
		btn.dataset.purchaseLocked = locked ? '1' : '0';
		btn.setAttribute( 'aria-disabled', locked ? 'true' : 'false' );
		btn.classList.toggle( 'cursor-not-allowed', locked );
		btn.classList.toggle( 'purchase-locked', locked );
	} );
}

function initLockedPurchaseHandlers() {
	const buttons = [
		document.getElementById( 'add-to-cart-button' ),
		document.getElementById( 'buy-now-button' ),
	].filter( Boolean );

	buttons.forEach( ( btn ) => {
		if ( btn.dataset.lockHandlersBound === '1' ) {
			return;
		}
		btn.dataset.lockHandlersBound = '1';

		btn.addEventListener( 'click', ( e ) => {
			if ( btn.disabled ) {
				return;
			}
			if ( btn.dataset.purchaseLocked !== '1' ) {
				return;
			}
			e.preventDefault();
			e.stopPropagation();
			validateVariations();
		} );
	} );
}

function updateStockDisplay( stockEl, variation ) {
	let statusClass = '';
	let statusText = '';
	let iconPath = '';

	if ( variation.is_in_stock ) {
		statusClass = 'text-green-600 bg-green-100';
		statusText = 'En stock';
		iconPath =
			'M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z';

		if ( variation.stock_quantity !== null && variation.stock_quantity > 0 ) {
			statusText = `${ variation.stock_quantity } en stock`;
		}
	} else if ( variation.stock_status === 'onbackorder' ) {
		statusClass = 'text-yellow-600 bg-yellow-100';
		statusText = 'Disponible por encargo';
		iconPath =
			'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z';
	} else {
		statusClass = 'text-red-600 bg-red-100';
		statusText = 'Sin stock';
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
				No disponible
			</span>
		`;
	}

	updatePurchaseButtonsState( { disabled: true, text: 'No disponible' } );

	updateVariationId( '' );
	restoreGalleryDefaults();
}

function resetProductDisplay() {
	// Reset variation ID
	updateVariationId( '' );

	// Keep buttons colored but block action until selection is complete.
	initLockedPurchaseHandlers();
	updatePurchaseButtonsState( { disabled: false } );
	setPurchaseButtonsLocked( true );
	setVariationMessageVisible( true );
	// No inline message; rely on tooltip + highlight/shake.

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
	const thumbnails = container.querySelectorAll( '[data-thumbnail]' );
	thumbnails.forEach( ( thumbnail ) => {
		thumbnail.addEventListener( 'click', () => {
			updateMainImageFromThumbnail( thumbnail );
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
		const wrapper = select.closest( '.variation-input' );
		if ( ! select.value ) {
			allSelected = false;
			if ( wrapper ) {
				wrapper.classList.add( 'border-red-500', 'bg-red-50' );
			}
			shakeElement( select );
		} else {
			if ( wrapper ) {
				wrapper.classList.remove( 'border-red-500', 'bg-red-50' );
			}
		}
	} );

	const validationMessage = document.getElementById( 'variation-message' );
	if ( validationMessage ) {
		validationMessage.classList.toggle( 'hidden', allSelected );
	}

	return allSelected;
}

function shakeElement( el ) {
	const target = el && el.offsetParent ? el : ( el ? el.closest( '.variation-input' ) : null );
	if ( ! target || ! target.animate ) {
		return;
	}
	target.animate(
		[
			{ transform: 'translateX(0px)' },
			{ transform: 'translateX(-4px)' },
			{ transform: 'translateX(4px)' },
			{ transform: 'translateX(-3px)' },
			{ transform: 'translateX(3px)' },
			{ transform: 'translateX(0px)' },
		],
		{ duration: 260, easing: 'ease-in-out' }
	);
}

function updateNavbarCountVariation( qty ) {
	document.querySelectorAll( '.etheme-navbar-action__badge' ).forEach( ( el ) => {
		const current  = parseInt( el.textContent, 10 ) || 0;
		const newCount = current + qty;
		el.textContent = String( newCount );
		el.classList.toggle( 'etheme-navbar-action__badge--visible', newCount > 0 );
	} );

	const cartBadgeEl = document.querySelector( '.cart-badge' );
	if ( cartBadgeEl ) {
		const current  = parseInt( cartBadgeEl.textContent, 10 ) || 0;
		const newCount = current + qty;
		cartBadgeEl.textContent = String( newCount );
		cartBadgeEl.classList.toggle( 'hidden', newCount <= 0 );
	}
}
