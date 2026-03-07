/**
 * Frontend JavaScript for Single Product Block
 *
 * Main entry point that initializes all component scripts.
 * Each component has its own script file in the scripts/ directory.
 */

import { initAddToCart } from '../scripts/add-to-cart.js';
import { initBreadcrumb } from '../scripts/breadcrumb.js';
import { initGallery } from '../scripts/gallery.js';
import { initImageModal } from '../scripts/image-modal.js';
import { initProductInfo } from '../scripts/product-info.js';
import { initRelatedProducts } from '../scripts/related-products.js';
import { initTabs } from '../scripts/tabs.js';
import { initVariations, validateVariations } from '../scripts/variations.js';

document.addEventListener( 'DOMContentLoaded', function () {
	// Initialize breadcrumb interactions
	initBreadcrumb();

	// Initialize gallery (includes thumbnails and modal)
	initGallery();

	// Initialize image modal (functionality merged into gallery)
	initImageModal();

	// Initialize product info interactions
	initProductInfo();

	// Initialize variation selectors for variable products
	initVariations();

	// Initialize add to cart functionality (includes quantity controls)
	initAddToCart();

	// Initialize product tabs
	initTabs();

	// Initialize related products animations
	initRelatedProducts();

	// Form submission validation for variable products
	initFormValidation();
} );

/**
 * Initialize form validation for add to cart
 */
function initFormValidation() {
	const form = document.getElementById( 'add-to-cart-form' );
	const variationsContainer = document.getElementById( 'product-variations' );

	if ( ! form ) {
		return;
	}

	form.addEventListener( 'submit', function ( e ) {
		// If this is a variable product, validate variations
		if ( variationsContainer ) {
			const isValid = validateVariations();

			if ( ! isValid ) {
				e.preventDefault();
				return false;
			}
		}

		// Show loading state on button
		const button = document.getElementById( 'add-to-cart-button' );
		if ( button ) {
			const addingText = button.dataset.addingText || 'Adding...';
			button.querySelector( '.button-text' ).textContent = addingText;
			button.disabled = true;
		}
	} );
}
