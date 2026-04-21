/**
 * FAQ accordion — animación de altura al abrir/cerrar (Web Animations API).
 * El cierre usa medición en px entera y sin cancel() al terminar para evitar saltos.
 */

const DURATION_MS = 260;
const EASING_OPEN  = 'cubic-bezier(0.22, 1, 0.36, 1)';
const EASING_CLOSE = 'cubic-bezier(0.4, 0, 1, 1)';

/**
 * @param {HTMLElement|null} el
 * @return {void}
 */
function clearAnswerStyles( el ) {
	if ( ! el ) {
		return;
	}
	el.style.height   = '';
	el.style.overflow = '';
	el.style.willChange = '';
}

/**
 * Altura útil para animar (evita subpíxeles y padding residual).
 *
 * @param {HTMLElement} answer
 * @return {number}
 */
function measureFullHeight( answer ) {
	return Math.ceil( answer.scrollHeight );
}

/**
 * @param {HTMLElement} answer
 * @param {number}      fromPx
 * @param {number}      toPx
 * @param {string}      easing
 * @return {Promise<void>}
 */
function animateHeightPx( answer, fromPx, toPx, easing ) {
	answer.style.willChange = 'height';

	const anim = answer.animate(
		[
			{ height: `${ fromPx }px`, overflow: 'hidden' },
			{ height: `${ toPx }px`, overflow: 'hidden' },
		],
		{ duration: DURATION_MS, easing, fill: 'forwards' }
	);

	return anim.finished
		.then( () => {
			answer.style.willChange = '';
		} )
		.catch( () => {} );
}

/**
 * @param {HTMLDetailsElement} details
 */
function attachItem( details ) {
	const summary = details.querySelector( 'summary' );
	if ( ! summary ) {
		return;
	}

	summary.addEventListener( 'click', ( e ) => {
		e.preventDefault();

		const wasOpen = details.hasAttribute( 'open' );
		const answer  = details.querySelector( '.faq-accordion__answer' );

		if ( wasOpen ) {
			if ( ! answer ) {
				details.removeAttribute( 'open' );
				return;
			}

			const fullH = measureFullHeight( answer );
			if ( fullH <= 0 ) {
				details.removeAttribute( 'open' );
				clearAnswerStyles( answer );
				return;
			}

			answer.style.overflow = 'hidden';
			answer.style.height   = `${ fullH }px`;

			requestAnimationFrame( () => {
				requestAnimationFrame( () => {
					animateHeightPx( answer, fullH, 0, EASING_CLOSE ).then( () => {
						details.removeAttribute( 'open' );
						clearAnswerStyles( answer );
					} );
				} );
			} );
		} else {
			details.setAttribute( 'open', '' );

			if ( ! answer ) {
				return;
			}

			const fullH = measureFullHeight( answer );
			answer.style.overflow = 'hidden';
			answer.style.height   = '0px';

			requestAnimationFrame( () => {
				requestAnimationFrame( () => {
					animateHeightPx( answer, 0, fullH, EASING_OPEN ).then( () => {
						clearAnswerStyles( answer );
					} );
				} );
			} );
		}
	} );
}

/**
 * @return {void}
 */
export function initFaqsAccordion() {
	document.querySelectorAll( '.faq-accordion__item' ).forEach( attachItem );
}
