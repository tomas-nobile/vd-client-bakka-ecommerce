/**
 * Add to Cart Script
 *
 * Handles add to cart form submission, quantity controls, and visual feedback.
 */

export function initAddToCart() {
	const button = document.getElementById( 'add-to-cart-button' );
	const form = document.getElementById( 'add-to-cart-form' ) || ( button ? button.form : null );

	if ( ! button ) {
		return;
	}

	// Initialize quantity controls
	initQuantity();

	// Handle form submission (skip variable product form)
	if ( form && ! form.classList.contains( 'variations_form' ) ) {
		form.addEventListener( 'submit', function () {
		// Show loading state
		const buttonText = button.querySelector( '.button-text' );
		const addingText = button.dataset.addingText || 'Adding...';
		const addedText = button.dataset.addedText || 'Added!';
		const addText = button.dataset.addText || 'Add to Cart';

		if ( buttonText ) {
			buttonText.textContent = addingText;
		}
		button.disabled = true;

		// Simulate success feedback (in real implementation, this would be handled by WooCommerce)
		setTimeout( () => {
			if ( buttonText ) {
				buttonText.textContent = addedText;
			}
			button.classList.add( 'bg-green-600' );
			button.classList.remove( 'bg-purple-500' );

			// Reset after 2 seconds
			setTimeout( () => {
				if ( buttonText ) {
					buttonText.textContent = addText;
				}
				button.disabled = false;
				button.classList.remove( 'bg-green-600' );
				button.classList.add( 'bg-purple-500' );
			}, 2000 );
		}, 500 );
		} );
	}
}

/**
 * Initialize quantity input controls
 */
function initQuantity() {
	const quantityInput = document.getElementById( 'quantity' );
	const decrementBtn = document.getElementById( 'qty-decrement' );
	const incrementBtn = document.getElementById( 'qty-increment' );

	if ( ! quantityInput ) {
		return;
	}

	const min = parseInt( quantityInput.dataset.min, 10 ) || 1;
	const max = quantityInput.dataset.max ? parseInt( quantityInput.dataset.max, 10 ) : Infinity;
	const step = parseInt( quantityInput.step, 10 ) || 1;

	// Decrement button
	if ( decrementBtn ) {
		decrementBtn.addEventListener( 'click', () => {
			let currentValue = parseInt( quantityInput.value, 10 ) || min;
			const newValue = Math.max( min, currentValue - step );
			quantityInput.value = newValue;
			updateButtonStates();
			triggerChangeEvent();
		} );
	}

	// Increment button
	if ( incrementBtn ) {
		incrementBtn.addEventListener( 'click', () => {
			let currentValue = parseInt( quantityInput.value, 10 ) || min;
			const newValue = max !== Infinity ? Math.min( max, currentValue + step ) : currentValue + step;
			quantityInput.value = newValue;
			updateButtonStates();
			triggerChangeEvent();
		} );
	}

	// Direct input validation
	quantityInput.addEventListener( 'change', () => {
		let value = parseInt( quantityInput.value, 10 );

		if ( isNaN( value ) || value < min ) {
			value = min;
		} else if ( max !== Infinity && value > max ) {
			value = max;
		}

		quantityInput.value = value;
		updateButtonStates();
	} );

	// Prevent non-numeric input
	quantityInput.addEventListener( 'keydown', ( e ) => {
		// Allow: backspace, delete, tab, escape, enter, decimal point
		if (
			[ 46, 8, 9, 27, 13, 110, 190 ].indexOf( e.keyCode ) !== -1 ||
			// Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
			( e.keyCode === 65 && e.ctrlKey === true ) ||
			( e.keyCode === 67 && e.ctrlKey === true ) ||
			( e.keyCode === 86 && e.ctrlKey === true ) ||
			( e.keyCode === 88 && e.ctrlKey === true ) ||
			// Allow: home, end, left, right
			( e.keyCode >= 35 && e.keyCode <= 39 )
		) {
			return;
		}
		// Ensure that it is a number and stop the keypress
		if (
			( e.shiftKey || e.keyCode < 48 || e.keyCode > 57 ) &&
			( e.keyCode < 96 || e.keyCode > 105 )
		) {
			e.preventDefault();
		}
	} );

	// Update button states on load
	updateButtonStates();

	function updateButtonStates() {
		const currentValue = parseInt( quantityInput.value, 10 ) || min;

		if ( decrementBtn ) {
			decrementBtn.disabled = currentValue <= min;
			decrementBtn.classList.toggle( 'opacity-50', currentValue <= min );
			decrementBtn.classList.toggle( 'cursor-not-allowed', currentValue <= min );
		}

		if ( incrementBtn && max !== Infinity ) {
			incrementBtn.disabled = currentValue >= max;
			incrementBtn.classList.toggle( 'opacity-50', currentValue >= max );
			incrementBtn.classList.toggle( 'cursor-not-allowed', currentValue >= max );
		}
	}

	function triggerChangeEvent() {
		const event = new Event( 'change', { bubbles: true } );
		quantityInput.dispatchEvent( event );
	}
}