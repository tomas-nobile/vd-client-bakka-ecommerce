// navbar — frontend entry.
import { initNavbarMobile } from './scripts/navbar-mobile.js';
import { initNavbarSearch } from './scripts/navbar-search.js';
import { initCartCountSync } from './scripts/navbar-cart-sync.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initNavbarMobile();
	initNavbarSearch();
	initCartCountSync();
} );
