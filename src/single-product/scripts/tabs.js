/**
 * Product Tabs Script
 *
 * Handles tab switching for description/info/reviews.
 */

export function initTabs() {
	const tabsContainer = document.getElementById( 'product-tabs' );

	if ( ! tabsContainer ) {
		return;
	}

	const items = tabsContainer.querySelectorAll( '[data-accordion-item]' );

	if ( ! items.length ) {
		return;
	}

	items.forEach( ( item ) => {
		const trigger = item.querySelector( '[data-accordion-trigger]' );
		const content = item.querySelector( '[data-accordion-content]' );

		if ( ! trigger || ! content ) {
			return;
		}

		trigger.addEventListener( 'click', () => {
			toggleItem( item, content, items );
		} );
	} );
}

function toggleItem( item, content, items ) {
	const isOpen = item.classList.contains( 'is-open' );

	items.forEach( ( otherItem ) => {
		if ( otherItem !== item ) {
			closeItem( otherItem );
		}
	} );

	if ( isOpen ) {
		closeItem( item );
	} else {
		openItem( item, content );
	}
}

function openItem( item, content ) {
	const icon = item.querySelector( '[data-accordion-icon]' );
	const height = content.scrollHeight;

	item.classList.add( 'is-open' );
	content.classList.remove( 'h-0' );
	content.style.height = `${ height }px`;

	if ( icon ) {
		icon.classList.add( 'rotate-180' );
	}

	window.setTimeout( () => {
		if ( item.classList.contains( 'is-open' ) ) {
			content.style.height = 'auto';
		}
	}, 500 );
}

function closeItem( item ) {
	const content = item.querySelector( '[data-accordion-content]' );
	const icon = item.querySelector( '[data-accordion-icon]' );

	if ( ! content ) {
		return;
	}

	content.style.height = `${ content.scrollHeight }px`;
	window.requestAnimationFrame( () => {
		item.classList.remove( 'is-open' );
		content.style.height = '0px';
		content.classList.add( 'h-0' );
	} );

	if ( icon ) {
		icon.classList.remove( 'rotate-180' );
	}
}
