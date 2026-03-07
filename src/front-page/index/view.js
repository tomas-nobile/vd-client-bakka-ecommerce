// front-page-index.
/**
 * Frontend JavaScript for Front Page Index
 *
 * Main entry point that initializes all component scripts.
 */

import { initNewsletter } from '../scripts/home-newsletter.newsletter.js';
import { initProductTabs, initColorDotSwitcher } from '../scripts/home-popular-products.product-tabs.js';
import { initFadeUp } from '../scripts/fp-fade-up.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initNewsletter();
	initProductTabs();
	initColorDotSwitcher();
	initFadeUp();
} );
