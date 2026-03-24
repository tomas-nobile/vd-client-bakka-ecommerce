/**
 * Product Filter Menu Component Script
 * 
 * Handles:
 * - Auto-apply filters on change (no submit button needed)
 * - Dual range slider for price filtering
 * - Color and size filter interactions
 */

export function initFilterMenu() {
	const filtersForm = document.querySelector( '.filters-form' );
	if ( ! filtersForm ) {
		return;
	}
	
	// Auto-apply filters on change
	if ( isDesktopView() ) {
		initAutoApplyFilters( filtersForm );
	}
	
	// Initialize price range slider
	initPriceRangeSlider();
	
	// Initialize color filter interactions
	initColorFilters();
	
	// Initialize size filter interactions
	initSizeFilters();
}

function isDesktopView() {
	return window.matchMedia( '(min-width: 768px)' ).matches;
}

/**
 * Auto-apply filters when any filter changes
 */
function initAutoApplyFilters( form ) {
	const filterInputs = form.querySelectorAll( 
		'input[type="checkbox"], input[type="range"], .price-range-input'
	);
	
	filterInputs.forEach( function ( input ) {
		input.addEventListener( 'change', function () {
			// Small delay to allow both range inputs to update
			setTimeout( function () {
				form.submit();
			}, 100 );
		} );
	} );
}

/**
 * Initialize dual range slider for price
 */
function initPriceRangeSlider() {
	const minInput = document.querySelector( '.price-range-min' );
	const maxInput = document.querySelector( '.price-range-max' );
	const fill = document.querySelector( '.price-range-fill' );
	const minDisplay = document.querySelector( '.price-min-display' );
	const maxDisplay = document.querySelector( '.price-max-display' );
	
	if ( ! minInput || ! maxInput || ! fill ) {
		return;
	}
	
	const min = parseFloat( minInput.dataset.min || 0 );
	const max = parseFloat( minInput.dataset.max || 1000 );
	
	function updateSlider() {
		const minVal = parseFloat( minInput.value );
		const maxVal = parseFloat( maxInput.value );
		
		// Ensure min doesn't exceed max and vice versa
		if ( minVal > maxVal ) {
			if ( minInput === document.activeElement ) {
				maxInput.value = minVal;
			} else {
				minInput.value = maxVal;
			}
		}
		
		const finalMin = parseFloat( minInput.value );
		const finalMax = parseFloat( maxInput.value );
		
		// Update fill position and width
		const minPercent = ( ( finalMin - min ) / ( max - min ) ) * 100;
		const maxPercent = ( ( finalMax - min ) / ( max - min ) ) * 100;
		
		fill.style.left = minPercent + '%';
		fill.style.width = ( maxPercent - minPercent ) + '%';
		
		// Update display values
		if ( minDisplay ) {
			minDisplay.textContent = '$' + Math.round( finalMin ).toLocaleString();
		}
		if ( maxDisplay ) {
			maxDisplay.textContent = '$' + Math.round( finalMax ).toLocaleString();
		}
	}
	
	// Update on input (for real-time feedback)
	minInput.addEventListener( 'input', updateSlider );
	maxInput.addEventListener( 'input', updateSlider );
	
	// Initial update
	updateSlider();
}

/**
 * Initialize color swatch interactions (Contrive style: border ring via is-selected).
 */
function initColorFilters() {
	const colorCheckboxes = document.querySelectorAll( '.color-filter-checkbox' );

	colorCheckboxes.forEach( function ( checkbox ) {
		const label = checkbox.closest( 'label' );
		if ( ! label ) {
			return;
		}

		label.addEventListener( 'click', function () {
			checkbox.checked = ! checkbox.checked;
			label.classList.toggle( 'is-selected', checkbox.checked );
		} );
	} );
}

/**
 * Initialize attribute pill interactions (Contrive style: is-selected class).
 */
function initSizeFilters() {
	const sizeCheckboxes = document.querySelectorAll( '.size-filter-checkbox' );

	sizeCheckboxes.forEach( function ( checkbox ) {
		const label = checkbox.closest( 'label' );
		if ( ! label ) {
			return;
		}

		label.addEventListener( 'click', function () {
			checkbox.checked = ! checkbox.checked;
			label.classList.toggle( 'is-selected', checkbox.checked );
		} );
	} );
}
