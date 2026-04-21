/**
 * Two-step checkout stepper — navigation, UI state, and fade-up trigger.
 *
 * Rechecks step 1 validity before advancing (prevents bypass).
 * Blocks double-submit on the place-order button.
 */

import { runStep1Validation } from './checkout-validation.js';
import { acquireLock, releaseLock } from '../../core/security/request-guard.js';

const STEPS = [ 1, 2 ];
const LOCK_ORDER = 'place-order';

function getStepPanel( n ) {
	return document.querySelector( `[data-checkout-step="${ n }"]` );
}

function getStepTrigger( n ) {
	return document.querySelector( `[data-step-trigger="${ n }"]` );
}

function setStepPanelVisibility( activeStep ) {
	STEPS.forEach( ( n ) => {
		const panel = getStepPanel( n );
		if ( ! panel ) return;
		if ( n === activeStep ) {
			panel.removeAttribute( 'hidden' );
			panel.removeAttribute( 'aria-hidden' );
		} else {
			panel.setAttribute( 'hidden', '' );
			panel.setAttribute( 'aria-hidden', 'true' );
		}
	} );
}

function applyStepNumberClasses( numEl, isActive, isCompleted ) {
	numEl.classList.remove( 'is-step-active', 'is-step-completed' );
	if ( isActive ) numEl.classList.add( 'is-step-active' );
	else if ( isCompleted ) numEl.classList.add( 'is-step-completed' );
}

function updateStepTrigger( n, activeStep ) {
	const btn = getStepTrigger( n );
	if ( ! btn ) return;

	const isActive = n === activeStep;
	const isCompleted = n < activeStep;
	const isLocked = n > activeStep;

	btn.disabled = isLocked;
	isActive ? btn.setAttribute( 'aria-current', 'step' ) : btn.removeAttribute( 'aria-current' );
	isLocked ? btn.setAttribute( 'aria-disabled', 'true' ) : btn.removeAttribute( 'aria-disabled' );

	const numEl = btn.querySelector( '.checkout-step-number' );
	if ( numEl ) applyStepNumberClasses( numEl, isActive, isCompleted );
}

function updateStepNav( activeStep ) {
	STEPS.forEach( ( n ) => updateStepTrigger( n, activeStep ) );
}

function focusStepHeading( step ) {
	const panel = getStepPanel( step );
	if ( ! panel ) return;
	const heading = panel.querySelector( '[data-step-heading]' );
	if ( ! heading ) return;
	heading.setAttribute( 'tabindex', '-1' );
	heading.focus();
}

function revealStepFadeUp( step ) {
	const panel = getStepPanel( step );
	if ( ! panel ) return;
	panel.querySelectorAll( '[data-aos="fade-up"]' ).forEach( ( el ) => {
		el.classList.add( 'fp-aos-visible' );
	} );
}

function goToStep( step ) {
	if ( step === 2 && ! runStep1Validation() ) return;

	setStepPanelVisibility( step );
	updateStepNav( step );
	window.scrollTo( { top: 0, behavior: 'smooth' } );
	setTimeout( () => focusStepHeading( step ), 120 );
	setTimeout( () => revealStepFadeUp( step ), 300 );
}

function bindContinueBtn( goFn ) {
	const btn = document.getElementById( 'checkout-btn-continue' );
	if ( btn ) btn.addEventListener( 'click', () => goFn( 2 ) );
}

function bindBackBtn( goFn ) {
	const btn = document.getElementById( 'checkout-btn-back' );
	if ( btn ) btn.addEventListener( 'click', () => goFn( 1 ) );
}

function bindStepTriggers( goFn ) {
	STEPS.forEach( ( n ) => {
		const btn = getStepTrigger( n );
		if ( btn ) btn.addEventListener( 'click', () => ! btn.disabled && goFn( n ) );
	} );
}

function bindPlaceOrderGuard() {
	const form = document.querySelector( 'form.checkout' );
	if ( ! form ) return;

	form.addEventListener( 'submit', ( e ) => {
		if ( ! acquireLock( LOCK_ORDER ) ) {
			e.preventDefault();
			return;
		}
		const placeBtn = document.getElementById( 'place_order' );
		if ( placeBtn ) {
			placeBtn.disabled = true;
			placeBtn.setAttribute( 'aria-busy', 'true' );
		}
		setTimeout( () => releaseLock( LOCK_ORDER ), 30000 );
	} );
}

/**
 * Intercept Enter key on step 1 inputs so it triggers "Continuar al pago"
 * instead of submitting the WooCommerce form directly.
 *
 * @param {Function} goFn Step navigation function.
 */
function bindStep1EnterKey( goFn ) {
	const panel = getStepPanel( 1 );
	if ( ! panel ) return;

	panel.addEventListener( 'keydown', ( e ) => {
		if ( e.key !== 'Enter' ) return;

		const tag = e.target.tagName;
		if ( tag === 'TEXTAREA' || tag === 'SELECT' ) return;

		e.preventDefault();
		goFn( 2 );
	} );
}

export function initCheckoutStepper() {
	if ( ! getStepPanel( 1 ) || ! getStepPanel( 2 ) ) return;
	bindContinueBtn( goToStep );
	bindBackBtn( goToStep );
	bindStepTriggers( goToStep );
	bindPlaceOrderGuard();
	bindStep1EnterKey( goToStep );
	goToStep( 1 );
}
