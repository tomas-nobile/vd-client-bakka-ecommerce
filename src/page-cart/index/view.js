/**
 * Frontend JavaScript for Cart Page Block
 *
 * Main entry point that initializes all component scripts.
 * Each component has its own script file in the scripts/ directory.
 */

import { initQuantitySelectors } from '../scripts/quantity.js';
import { initRemoveItem } from '../scripts/remove-item.js';
import { initShippingCalculator } from '../scripts/shipping-calculator.js';
import { initCoupon } from '../scripts/coupon.js';

document.addEventListener( 'DOMContentLoaded', function () {
	// Initialize quantity selectors
	initQuantitySelectors();

	// Initialize remove item functionality
	initRemoveItem();

	// Initialize shipping calculator
	initShippingCalculator();

	// Initialize coupon functionality
	initCoupon();
} );
