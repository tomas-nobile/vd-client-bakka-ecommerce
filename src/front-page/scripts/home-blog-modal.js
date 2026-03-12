// home-blog-modal.js
/**
 * Blog modal: open/close, media rendering, in-modal carousel navigation.
 * Card carousel (prev/next within the card itself) is also handled here.
 *
 * Card triggers: [data-blog-card] → click or Enter/Space.
 * Close triggers: [data-close-modal] inside the modal, or Escape key.
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

// ─── Card click ───────────────────────────────────────────────────────────────

function initCardClickListeners() {
	document.querySelectorAll( '[data-blog-card]' ).forEach( ( card ) => {
		card.addEventListener( 'click', () => openModal( card ) );
		card.addEventListener( 'keydown', ( e ) => {
			if ( 'Enter' === e.key || ' ' === e.key ) {
				e.preventDefault();
				openModal( card );
			}
		} );
	} );
}

// ─── Modal open / close ───────────────────────────────────────────────────────

function openModal( card ) {
	const modal = document.getElementById( 'blog-post-modal' );
	if ( ! modal ) return;

	modalMedia = parseCardMedia( card );
	modalIndex = 0;

	populateDescription( card.dataset.description || '' );
	renderModalMedia();
	syncModalNav();

	modal.hidden = false;
	requestAnimationFrame( () => modal.classList.add( 'is-open' ) );
	document.body.style.overflow = 'hidden';
	modal.querySelector( '[data-close-modal]' )?.focus();
}

function closeModal() {
	const modal = document.getElementById( 'blog-post-modal' );
	if ( ! modal || modal.hidden ) return;

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
	modalIndex = ( modalIndex + dir + modalMedia.length ) % modalMedia.length;
	renderModalMedia();
}

// ─── In-card carousel (arrows on the card itself) ────────────────────────────

function initCardCarouselListeners() {
	document.querySelectorAll( '[data-blog-card]' ).forEach( ( card ) => {
		card.querySelectorAll( '[data-carousel-prev]' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', ( e ) => { e.stopPropagation(); navigateCard( card, -1 ); } );
		} );
		card.querySelectorAll( '[data-carousel-next]' ).forEach( ( btn ) => {
			btn.addEventListener( 'click', ( e ) => { e.stopPropagation(); navigateCard( card, 1 ); } );
		} );
	} );
}

function navigateCard( card, dir ) {
	const slides = Array.from( card.querySelectorAll( '.blog-insta-card__slide' ) );
	const dots   = Array.from( card.querySelectorAll( '.blog-insta-card__dot' ) );
	if ( slides.length < 2 ) return;

	const current = slides.findIndex( ( s ) => s.classList.contains( 'is-active' ) );
	const next    = ( current + dir + slides.length ) % slides.length;

	slides[ current ].classList.remove( 'is-active' );
	slides[ next ].classList.add( 'is-active' );
	if ( dots[ current ] ) dots[ current ].classList.remove( 'is-active' );
	if ( dots[ next ] )    dots[ next ].classList.add( 'is-active' );
}

// ─── Media rendering ──────────────────────────────────────────────────────────

function renderModalMedia() {
	const container = document.getElementById( 'blog-modal-media' );
	if ( ! container ) return;

	const item = modalMedia[ modalIndex ];
	container.innerHTML = item ? buildMediaHTML( item ) : '';
	updateCounter();
}

function buildMediaHTML( item ) {
	if ( 'image' === item.type ) {
		const srcset = item.srcset ? ` srcset="${ escAttr( item.srcset ) }"` : '';
		return `<img src="${ escAttr( item.src ) }"${ srcset } alt="${ escAttr( item.alt || '' ) }" class="blog-modal__img">`;
	}
	if ( 'video' === item.type ) {
		return `<video class="blog-modal__video" controls preload="metadata"><source src="${ escAttr( item.url ) }"></video>`;
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

function populateDescription( text ) {
	const el = document.getElementById( 'blog-modal-description' );
	if ( el ) el.textContent = text;
}

function syncModalNav() {
	const nav = document.getElementById( 'blog-modal-nav' );
	if ( nav ) nav.hidden = modalMedia.length < 2;
	updateCounter();
}

function updateCounter() {
	const el = document.getElementById( 'blog-modal-counter' );
	if ( el ) el.textContent = `${ modalIndex + 1 } / ${ modalMedia.length }`;
}

function escAttr( str ) {
	return String( str )
		.replace( /&/g, '&amp;' )
		.replace( /"/g, '&quot;' )
		.replace( /</g, '&lt;' )
		.replace( />/g, '&gt;' );
}
