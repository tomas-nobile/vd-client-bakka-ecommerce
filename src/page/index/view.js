/**
 * Frontend: My Account block.
 */

import { initFadeUp } from '../../front-page/scripts/fp-fade-up.js';
import { initAccountMobileNav } from './account-mobile-nav.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initFadeUp( '.page-account-block' );
	initAccountMobileNav();
} );
