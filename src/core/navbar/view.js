// navbar — frontend entry.
import { initNavbarMobile } from './scripts/navbar-mobile.js';
import { initNavbarSearch } from './scripts/navbar-search.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initNavbarMobile();
	initNavbarSearch();
} );
