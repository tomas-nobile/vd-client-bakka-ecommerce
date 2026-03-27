/**
 * Frontend JavaScript for Cart Page Block
 *
 * Main entry point that initializes all component scripts.
 * Each component has its own script file in the scripts/ directory.
 */

import { initQuantitySelectors } from '../scripts/quantity.js';
import { initRemoveItem } from '../scripts/remove-item.js';
import { initShippingCalculator } from '../scripts/shipping-calculator.js';
import { initCoupon, initCouponToggle } from '../scripts/coupon.js';
import { initFadeUp } from '../../front-page/scripts/fp-fade-up.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initQuantitySelectors();
	initRemoveItem();
	initShippingCalculator();
	initCoupon();
	initCouponToggle();
	initFadeUp( '.page-cart-block' );
} );
