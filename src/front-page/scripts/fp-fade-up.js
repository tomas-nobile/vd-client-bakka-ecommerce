/**
 * Front Page: scroll-triggered fade-up animation (same as reference feature-con / AOS fade-up).
 * Observes [data-aos="fade-up"] inside the front-page block and adds .fp-aos-visible when in viewport.
 */
function initFadeUp() {
	const block = document.querySelector( '.wp-block-etheme-front-page-index' );
	if ( ! block ) {
		return;
	}

	const elements = block.querySelectorAll( '[data-aos="fade-up"]' );
	if ( ! elements.length ) {
		return;
	}

	const observer = new IntersectionObserver(
		( entries ) => {
			entries.forEach( ( entry ) => {
				if ( entry.isIntersecting ) {
					entry.target.classList.add( 'fp-aos-visible' );
				}
			} );
		},
		{
			rootMargin: '0px 0px -40px 0px',
			threshold: 0.1,
		}
	);

	elements.forEach( ( el ) => observer.observe( el ) );
}

export { initFadeUp };
