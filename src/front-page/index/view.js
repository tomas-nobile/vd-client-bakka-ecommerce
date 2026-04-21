// front-page-index.
/**
 * Frontend JavaScript for Front Page Index
 *
 * Main entry point that initializes all component scripts.
 */

import { initFaqsAccordion } from '../scripts/home-faqs.accordion.js';
import { initColorDotSwitcher } from '../scripts/home-popular-products.product-tabs.js';
import { initFadeUp } from '../scripts/fp-fade-up.js';
import { initBlogModal } from '../scripts/home-blog-modal.js';
import { initCustomWorkParallax } from '../scripts/home-custom-work-parallax.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initFaqsAccordion();
	initColorDotSwitcher();
	initFadeUp();
	initBlogModal();
	initCustomWorkParallax();
} );
