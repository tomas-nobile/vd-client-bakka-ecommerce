/**
 * Two-step checkout stepper — navigation and UI state.
 */

const STEPS = [ 1, 2 ];

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
	const remove = [
		'border-gray-900', 'bg-gray-900',
		'border-green-600', 'bg-green-600',
		'border-gray-300', 'text-gray-400', 'text-white',
	];
	remove.forEach( ( c ) => numEl.classList.remove( c ) );

	if ( isActive ) {
		numEl.classList.add( 'border-gray-900', 'bg-gray-900', 'text-white' );
	} else if ( isCompleted ) {
		numEl.classList.add( 'border-green-600', 'bg-green-600', 'text-white' );
	} else {
		numEl.classList.add( 'border-gray-300', 'text-gray-400' );
	}
}

function updateStepTrigger( n, activeStep ) {
	const btn = getStepTrigger( n );
	if ( ! btn ) return;

	const isActive    = n === activeStep;
	const isCompleted = n < activeStep;
	const isLocked    = n > activeStep;

	btn.disabled = isLocked;
	isActive   ? btn.setAttribute( 'aria-current', 'step' )   : btn.removeAttribute( 'aria-current' );
	isLocked   ? btn.setAttribute( 'aria-disabled', 'true' )  : btn.removeAttribute( 'aria-disabled' );
	btn.classList.toggle( 'text-gray-900', isActive || isCompleted );
	btn.classList.toggle( 'text-gray-400', isLocked );

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

function goToStep( step ) {
	setStepPanelVisibility( step );
	updateStepNav( step );
	window.scrollTo( { top: 0, behavior: 'smooth' } );
	setTimeout( () => focusStepHeading( step ), 120 );
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

export function initCheckoutStepper() {
	if ( ! getStepPanel( 1 ) || ! getStepPanel( 2 ) ) return;
	bindContinueBtn( goToStep );
	bindBackBtn( goToStep );
	bindStepTriggers( goToStep );
	goToStep( 1 );
}
