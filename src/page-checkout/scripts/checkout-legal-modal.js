/**
 * Checkout legal modal.
 *
 * Opens a <dialog id="checkout-legal-modal"> when the user clicks
 * a [data-legal-trigger] button. Manages focus, Escape key, and
 * restores focus on close. Does not depend on checkout-stepper.js.
 *
 * Markup expected in render.php:
 *   <button type="button" data-legal-trigger="privacy">…</button>
 *   <dialog id="checkout-legal-modal" aria-modal="true" aria-labelledby="legal-modal-title">
 *     <div class="checkout-legal-modal__dialog" role="document">
 *       <button class="checkout-legal-modal__close">…</button>
 *       <h2 id="legal-modal-title" class="checkout-legal-modal__title">…</h2>
 *       <div class="checkout-legal-modal__body">…</div>
 *     </div>
 *   </dialog>
 */

const MODAL_ID   = 'checkout-legal-modal';
const TRIGGER    = '[data-legal-trigger]';
const CLOSE_BTN  = '.checkout-legal-modal__close';
const TITLE_ID   = 'legal-modal-title';

let previousFocus = null;

/**
 * Return all focusable descendants of an element.
 *
 * @param {HTMLElement} container
 * @return {HTMLElement[]}
 */
function getFocusable( container ) {
	return Array.from(
		container.querySelectorAll(
			'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
		)
	).filter( ( el ) => ! el.hasAttribute( 'disabled' ) );
}

/**
 * Trap Tab/Shift+Tab inside the modal.
 *
 * @param {HTMLElement} modal
 * @param {KeyboardEvent} e
 */
function trapFocus( modal, e ) {
	if ( e.key !== 'Tab' ) return;
	const focusable = getFocusable( modal );
	if ( ! focusable.length ) return;

	const first = focusable[ 0 ];
	const last  = focusable[ focusable.length - 1 ];

	if ( e.shiftKey ) {
		if ( document.activeElement === first ) {
			e.preventDefault();
			last.focus();
		}
	} else if ( document.activeElement === last ) {
		e.preventDefault();
		first.focus();
	}
}

/**
 * Open the modal and swap content from a <template> store.
 *
 * @param {HTMLDialogElement} modal
 * @param {string}            key   e.g. 'privacy' or 'terms'
 */
function openModal( modal, key ) {
	const titleEl  = modal.querySelector( `#${ TITLE_ID }` );
	const bodyEl   = modal.querySelector( '.checkout-legal-modal__body' );
	const template = modal.querySelector( `template[data-legal-section="${ key }"]` );

	if ( titleEl && template ) {
		titleEl.textContent = template.dataset.title || '';
	}

	if ( bodyEl && template ) {
		bodyEl.innerHTML = '';
		bodyEl.appendChild( template.content.cloneNode( true ) );
	}

	modal.showModal();
	modal.removeAttribute( 'hidden' );

	const firstFocusable = getFocusable( modal )[ 0 ];
	if ( firstFocusable ) firstFocusable.focus();

	modal.addEventListener( 'keydown', handleKeyDown );
}

/**
 * Close the modal and restore focus.
 *
 * @param {HTMLDialogElement} modal
 */
function closeModal( modal ) {
	modal.close();
	modal.setAttribute( 'hidden', '' );
	modal.removeEventListener( 'keydown', handleKeyDown );

	if ( previousFocus && document.body.contains( previousFocus ) ) {
		previousFocus.focus();
	}
	previousFocus = null;
}

/**
 * Keydown handler while modal is open (Escape + Tab trap).
 *
 * @param {KeyboardEvent} e
 */
function handleKeyDown( e ) {
	const modal = document.getElementById( MODAL_ID );
	if ( ! modal ) return;

	if ( e.key === 'Escape' ) {
		closeModal( modal );
		return;
	}

	trapFocus( modal, e );
}

/**
 * Initialize checkout legal modals.
 *
 * @return {void}
 */
export function initCheckoutLegalModal() {
	const modal = document.getElementById( MODAL_ID );
	if ( ! modal ) return;

	document.querySelectorAll( TRIGGER ).forEach( ( btn ) => {
		btn.addEventListener( 'click', () => {
			previousFocus = btn;
			openModal( modal, btn.dataset.legalTrigger );
		} );
	} );

	const closeBtn = modal.querySelector( CLOSE_BTN );
	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', () => closeModal( modal ) );
	}

	modal.addEventListener( 'click', ( e ) => {
		if ( e.target === modal ) closeModal( modal );
	} );
}
