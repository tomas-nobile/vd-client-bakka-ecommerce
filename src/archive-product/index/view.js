/**
 * Frontend JavaScript for Product Archive Index
 *
 * Main entry point that initializes all component scripts.
 * Each component has its own script file in the scripts/ directory.
 */

import { initFilterMenu } from '../scripts/filter-menu.js';
import { initFilterButton } from '../scripts/filter-button.js';

document.addEventListener( 'DOMContentLoaded', function () {
	// Initialize filter menu form handling
	initFilterMenu();
	
	// Initialize filter button toggle
	initFilterButton();
} );
