export function initFaqsAccordion() {
	const list = document.querySelector( '.faqs-content__list' );
	if ( ! list ) return;

	list.addEventListener( 'click', ( e ) => {
		const btn = e.target.closest( '.faqs-content__toggle' );
		if ( ! btn ) return;

		const item   = btn.closest( '.faqs-content__item' );
		const answer = item.querySelector( '.faqs-content__answer' );
		const open   = btn.getAttribute( 'aria-expanded' ) === 'true';

		// Close all other open items
		list.querySelectorAll( '.faqs-content__item' ).forEach( ( other ) => {
			if ( other === item ) return;
			other.querySelector( '.faqs-content__toggle' ).setAttribute( 'aria-expanded', 'false' );
			other.querySelector( '.faqs-content__answer' ).hidden = true;
			other.classList.remove( 'faqs-content__item--open' );
		} );

		btn.setAttribute( 'aria-expanded', String( ! open ) );
		answer.hidden = open;
		item.classList.toggle( 'faqs-content__item--open', ! open );
	} );
}
