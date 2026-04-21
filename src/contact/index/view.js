// contact-index — frontend entry.
import { initFadeUp } from '../../front-page/scripts/fp-fade-up.js';
import { initContactForm } from '../scripts/contact-form.js';

document.addEventListener( 'DOMContentLoaded', function () {
	initFadeUp( '.wp-block-etheme-contact-index' );
	initContactForm();
} );
