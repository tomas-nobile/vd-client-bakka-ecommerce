/**
 * Frontend JavaScript for Product Archive Index
 *
 * Main entry point that initializes all component scripts.
 * Each component has its own script file in the scripts/ directory.
 */

import { initFilterMenu } from '../scripts/filter-menu.js';
import { initFilterButton } from '../scripts/filter-button.js';
import { initFadeUp } from '../../front-page/scripts/fp-fade-up.js';
import { initArchiveCategoryChips } from '../scripts/archive-category-chips.js';
import { initColorDotSwitcher } from '../../front-page/scripts/home-popular-products.product-tabs.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initFilterMenu();
	initFilterButton();
	initFadeUp( '.wp-block-etheme-archive-product-index' );
	initArchiveCategoryChips();
	initColorDotSwitcher();
} );
