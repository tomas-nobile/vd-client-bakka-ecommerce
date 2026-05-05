// core/blog-modal.js
/**
 * Blog modal: open/close, media rendering, in-modal carousel navigation.
 * Card carousel (prev/next within the card itself) is also handled here.
 *
 * Uses event delegation on document so dynamically added cards (e.g. via
 * AJAX "Mostrar más") automatically work without re-initialization.
 *
 * Card triggers: [data-blog-card] → click or Enter/Space.
 * Close triggers: [data-close-modal] inside the modal, or Escape key.
 *
 * Used by: front-page-index (home blog section), page-trabajos-realizados-index.
 */

/** @type {Array<{type:string,src?:string,srcset?:string,alt?:string,url?:string}>} */
let modalMedia = [];
let modalIndex = 0;

export function initBlogModal() {
	const modal = document.getElementById( 'blog-post-modal' );
	if ( ! modal ) return;

	initCardClickListeners();
	initModalCloseListeners( modal );
	initModalNavListeners();
	initCardCarouselListeners();
}

// ─── Card click (event delegation — works for dynamically added cards) ─────────

function initCardClickListeners() {
	document.addEventListener( 'click', ( e ) => {
		// Ignore clicks inside the social link (go to network, not modal).
		if ( e.target.closest( '[data-social-link]' ) ) return;

		// Ignore carousel nav buttons — handled by initCardCarouselListeners.
		if ( e.target.closest( '[data-carousel-prev], [data-carousel-next]' ) ) return;

		const card = e.target.closest( '[data-blog-card]' );
		if ( ! card ) return;

		e.preventDefault();
		openModal( card );
	} );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.target.closest( '[data-social-link]' ) ) return;
		if ( e.target.closest( '[data-carousel-prev], [data-carousel-next]' ) ) return;

		const card = e.target.closest( '[data-blog-card]' );
		if ( ! card ) return;

		if ( 'Enter' === e.key || ' ' === e.key ) {
			e.preventDefault();
			openModal( card );
		}
	} );
}

// ─── Modal open / close ───────────────────────────────────────────────────────

function openModal( card ) {
	const modal = document.getElementById( 'blog-post-modal' );
	if ( ! modal ) return;

	pauseVideosIn( card );

	modalMedia = parseCardMedia( card );
	const slides     = card.querySelectorAll( '.blog-insta-card__slide' );
	const activeSlide = card.querySelector( '.blog-insta-card__slide.is-active' );
	modalIndex = ( activeSlide && slides.length ) ? Array.from( slides ).indexOf( activeSlide ) : 0;

	populateModalDate( card.dataset.date || '', card.dataset.datetime || '' );
	populateSocialMeta( card );
	renderModalMedia();
	buildDots();
	toggleModalArrows();
	autoPlayModalVideo();

	modal.hidden = false;
	requestAnimationFrame( () => modal.classList.add( 'is-open' ) );
	document.body.style.overflow = 'hidden';
	modal.querySelector( '[data-close-modal]' )?.focus();
}

function closeModal() {
	const modal = document.getElementById( 'blog-post-modal' );
	if ( ! modal || modal.hidden ) return;

	pauseVideosIn( modal.querySelector( '#blog-modal-media' ) );
	modal.classList.remove( 'is-open' );
	modal.addEventListener(
		'transitionend',
		() => {
			modal.hidden = true;
			document.body.style.overflow = '';
		},
		{ once: true }
	);
}

function initModalCloseListeners( modal ) {
	modal.querySelectorAll( '[data-close-modal]' ).forEach( ( el ) => {
		el.addEventListener( 'click', closeModal );
	} );
	document.addEventListener( 'keydown', ( e ) => {
		if ( 'Escape' === e.key ) closeModal();
	} );
}

// ─── In-modal carousel navigation ────────────────────────────────────────────

function initModalNavListeners() {
	document.getElementById( 'blog-modal-prev' )
		?.addEventListener( 'click', () => navigateModal( -1 ) );
	document.getElementById( 'blog-modal-next' )
		?.addEventListener( 'click', () => navigateModal( 1 ) );
}

function navigateModal( dir ) {
	if ( modalMedia.length < 2 ) return;
	pauseVideosIn( document.getElementById( 'blog-modal-media' ) );
	modalIndex = ( modalIndex + dir + modalMedia.length ) % modalMedia.length;
	renderModalMedia();
	updateDots();
	autoPlayModalVideo();
}

// ─── In-card carousel (event delegation for dynamically added cards) ──────────

function initCardCarouselListeners() {
	document.addEventListener( 'click', ( e ) => {
		const prevBtn = e.target.closest( '[data-carousel-prev]' );
		const nextBtn = e.target.closest( '[data-carousel-next]' );

		if ( prevBtn || nextBtn ) {
			e.stopPropagation();
			const card = ( prevBtn || nextBtn ).closest( '[data-blog-card]' );
			if ( card ) navigateCard( card, prevBtn ? -1 : 1 );
		}
	} );
}

function navigateCard( card, dir ) {
	const slides = Array.from( card.querySelectorAll( '.blog-insta-card__slide' ) );
	const dots   = Array.from( card.querySelectorAll( '.blog-insta-card__dot' ) );
	if ( slides.length < 2 ) return;

	const current = slides.findIndex( ( s ) => s.classList.contains( 'is-active' ) );
	const next    = ( current + dir + slides.length ) % slides.length;

	pauseVideosIn( slides[ current ] );
	slides[ current ].classList.remove( 'is-active' );
	slides[ next ].classList.add( 'is-active' );
	if ( dots[ current ] ) dots[ current ].classList.remove( 'is-active' );
	if ( dots[ next ] )    dots[ next ].classList.add( 'is-active' );
}

// ─── Media rendering ──────────────────────────────────────────────────────────

function renderModalMedia() {
	const container = document.getElementById( 'blog-modal-media' );
	const inner     = document.getElementById( 'blog-modal-media-inner' ) || container;
	if ( ! container ) return;

	pauseVideosIn( container );
	const item   = modalMedia[ modalIndex ];
	inner.innerHTML = item ? buildMediaHTML( item ) : '';
	if ( item && 'image' === item.type ) {
		applyModalBackgroundFromImage( container );
	}
}

function autoPlayModalVideo() {
	const container = document.getElementById( 'blog-modal-media' );
	if ( ! container ) return;

	const video = container.querySelector( 'video' );
	if ( ! video ) return;

	const playPromise = video.play();
	if ( playPromise && 'function' === typeof playPromise.then ) {
		playPromise.catch( () => {} );
	}
}

function pauseVideosIn( container ) {
	if ( ! container ) return;
	container.querySelectorAll( 'video' ).forEach( ( v ) => {
		if ( ! v.paused ) v.pause();
	} );
}

function buildMediaHTML( item ) {
	if ( 'image' === item.type ) {
		const srcset = item.srcset ? ` srcset="${ escAttr( item.srcset ) }"` : '';
		return `<img src="${ escAttr( item.src ) }"${ srcset } alt="${ escAttr( item.alt || '' ) }" class="blog-modal__img">`;
	}
	if ( 'video' === item.type ) {
		const poster = item.poster ? ` poster="${ escAttr( item.poster ) }"` : '';
		return `<video class="blog-modal__video" controls preload="none"${ poster }><source src="${ escAttr( item.url ) }"></video>`;
	}
	if ( 'embed' === item.type ) {
		return `<div class="blog-modal__embed"><iframe src="${ escAttr( item.url ) }" frameborder="0" allowfullscreen title="Video embed"></iframe></div>`;
	}
	return '';
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function parseCardMedia( card ) {
	try {
		return JSON.parse( card.dataset.media || '[]' );
	} catch ( e ) {
		return [];
	}
}

function populateModalDate( label, datetime ) {
	const el = document.getElementById( 'blog-modal-date' );
	if ( ! el ) return;
	el.textContent = label;
	if ( datetime ) el.setAttribute( 'datetime', datetime );
}

function populateSocialMeta( card ) {
	const handle = card.dataset.socialHandle || '';
	const url    = card.dataset.socialUrl || '';
	const icon   = card.dataset.socialIcon || '';

	const descEl = document.getElementById( 'blog-modal-description' );
	if ( descEl ) {
		const rawText = card.dataset.description || '';
		descEl.innerHTML = '';
		if ( handle ) {
			const strong = document.createElement( 'strong' );
			strong.className = 'blog-modal__instagram-handle';
			strong.textContent = `@${ handle } `;
			descEl.appendChild( strong );
		}
		descEl.appendChild( document.createTextNode( rawText ) );
	}

	const linkEl = document.getElementById( 'blog-modal-social-link' );
	if ( linkEl ) {
		linkEl.href           = url || '#';
		linkEl.style.display  = url ? '' : 'none';
	}

	const iconEl = document.getElementById( 'blog-modal-social-icon' );
	if ( iconEl ) {
		iconEl.src           = icon || '';
		iconEl.style.display = icon ? '' : 'none';
	}
}

function buildDots() {
	const container = document.getElementById( 'blog-modal-dots' );
	if ( ! container ) return;

	container.innerHTML = '';

	if ( modalMedia.length < 2 ) {
		container.hidden = true;
		return;
	}

	container.hidden = false;

	modalMedia.forEach( ( _item, index ) => {
		const dot = document.createElement( 'button' );
		dot.type      = 'button';
		dot.className = 'blog-modal__dot' + ( index === modalIndex ? ' is-active' : '' );
		dot.setAttribute( 'aria-label', `${ index + 1 } / ${ modalMedia.length }` );
		dot.addEventListener( 'click', () => {
			if ( modalIndex === index ) return;
			pauseVideosIn( document.getElementById( 'blog-modal-media' ) );
			modalIndex = index;
			renderModalMedia();
			updateDots();
			autoPlayModalVideo();
		} );
		container.appendChild( dot );
	} );
}

function updateDots() {
	const container = document.getElementById( 'blog-modal-dots' );
	if ( ! container ) return;
	container.querySelectorAll( '.blog-modal__dot' ).forEach( ( dot, index ) => {
		dot.classList.toggle( 'is-active', index === modalIndex );
	} );
}

function toggleModalArrows() {
	const hasMultiple = modalMedia.length > 1;
	const prev = document.getElementById( 'blog-modal-prev' );
	const next = document.getElementById( 'blog-modal-next' );
	if ( prev ) prev.style.display = hasMultiple ? '' : 'none';
	if ( next ) next.style.display = hasMultiple ? '' : 'none';
}

function escAttr( str ) {
	return String( str )
		.replace( /&/g, '&amp;' )
		.replace( /"/g, '&quot;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' );
}

function applyModalBackgroundFromImage( container ) {
	const img = container.querySelector( '.blog-modal__img' );
	if ( ! img ) return;

	const onLoad = () => {
		const canvas = document.createElement( 'canvas' );
		const ctx    = canvas.getContext( '2d' );
		if ( ! ctx ) return;

		const width  = img.naturalWidth || img.width;
		const height = img.naturalHeight || img.height;
		if ( ! width || ! height ) return;

		canvas.width  = width;
		canvas.height = height;

		try {
			ctx.drawImage( img, 0, 0, width, height );
			const [ r, g, b, a ] = ctx.getImageData( width - 1, 0, 1, 1 ).data;
			if ( a === 0 ) return;
			container.style.backgroundColor = `rgb(${ r }, ${ g }, ${ b })`;
		} catch ( e ) {
			// Cross-origin image — fall back silently.
		}
	};

	if ( img.complete && img.naturalWidth ) {
		onLoad();
	} else {
		img.addEventListener( 'load', onLoad, { once: true } );
	}
}
