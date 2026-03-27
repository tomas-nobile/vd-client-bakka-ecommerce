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
import { initTabs } from '../scripts/tabs.js';
import { initVariations, validateVariations } from '../scripts/variations.js';
import { initFadeUp } from '../../front-page/scripts/fp-fade-up.js';
import { initColorDotSwitcher } from '../../front-page/scripts/home-popular-products.product-tabs.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initBreadcrumb();
	initGallery();
	initImageModal();
	initProductInfo();
	initVariations();
	initAddToCart();
	initTabs();

	// Related products: fade-up + color-dot switcher via shared core scripts.
	initFadeUp( '.wp-block-etheme-single-product-index' );
	initColorDotSwitcher();

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
			const addingText = button.dataset.addingText || 'Agregando...';
			button.querySelector( '.button-text' ).textContent = addingText;
			button.disabled = true;
		}
	} );
}
