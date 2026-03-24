/**
 * Scroll-triggered fade-up animation (IntersectionObserver / AOS fade-up).
 * Observes [data-aos="fade-up"] inside the given block selector and adds
 * .fp-aos-visible when the element enters the viewport.
 *
 * @param {string} [blockSelector='.wp-block-etheme-front-page-index']
 *   CSS selector for the block wrapper to scope the observation.
 *   Pass the block's own class when using this in other blocks.
 */
function initFadeUp( blockSelector ) {
	const selector = blockSelector || '.wp-block-etheme-front-page-index';
	const block    = document.querySelector( selector );
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

	elements.forEach( ( el ) => {
		const delay = el.dataset.aosDelay;
		if ( delay ) {
			el.style.transitionDelay = delay + 'ms';
		}
		observer.observe( el );
	} );
}

export { initFadeUp };
