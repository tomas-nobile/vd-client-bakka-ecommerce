/**
 * Frontend JavaScript for My Account Page Block
 *
 * Main entry point that initializes all component scripts.
 * Each component has its own script file in the scripts/ directory.
 */

import { initFormToggle } from '../scripts/form-toggle.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initFormToggle();
} );
