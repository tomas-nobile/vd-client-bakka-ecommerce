/**
 * Parallax por scroll para la sección Custom Work.
 *
 * Mueve el fondo de `.custom-work-con` en sentido opuesto al scroll
 * usando requestAnimationFrame para evitar paint innecesario.
 * Respeta prefers-reduced-motion: si está activado, no aplica efecto.
 *
 * @param {string} [selector='.custom-work-con']
 */
function initCustomWorkParallax( selector ) {
	const target = selector || '.custom-work-con';
	const section = document.querySelector( target );
	if ( ! section ) {
		return;
	}

	const prefersReduced = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
	if ( prefersReduced ) {
		return;
	}

	let ticking   = false;

	function getParallaxConfig() {
		if ( window.innerWidth <= 767 ) {
			return { factor: 0.34, maxOffset: 90 };
		}
		return { factor: 0.8, maxOffset: 260 };
	}

	function applyParallax() {
		const config = getParallaxConfig();
		const rect = section.getBoundingClientRect();
		const triggerOffset = window.innerHeight * 0.35;
		const rawOffset = -( rect.top - triggerOffset ) * config.factor;
		const dynamicLimit = Math.min( config.maxOffset, section.offsetHeight * 0.28 );
		const offset = Math.max( -dynamicLimit, Math.min( dynamicLimit, rawOffset ) );
		section.style.backgroundPosition = `center calc(50% + ${ offset }px)`;
		ticking = false;
	}

	function onScroll() {
		if ( ! ticking ) {
			requestAnimationFrame( applyParallax );
			ticking = true;
		}
	}

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll, { passive: true } );
	onScroll();
}

export { initCustomWorkParallax };
