/**
 * Gallery Script
 *
 * Main gallery script that handles thumbnails, modal, and all gallery interactions.
 */

// Gallery modal variables
let currentImageIndex = 0;
let galleryImages = [];
let currentZoom = 1;
let isPanning = false;
let startX = 0;
let startY = 0;
let translateX = 0;
let translateY = 0;
let syncMainImage = true;

const MIN_ZOOM = 1;
const MAX_ZOOM = 4;
const ZOOM_STEP = 0.5;

export function initGallery() {
	const gallery = document.querySelector( '.product-gallery' );

	if ( ! gallery ) {
		return;
	}

	// Initialize thumbnails
	initGalleryThumbnails();

	// Initialize modal
	initGalleryModal();

	// Add loading states for images
	const images = gallery.querySelectorAll( 'img' );
	
	images.forEach( ( img ) => {
		if ( ! img.complete ) {
			img.addEventListener( 'load', function () {
				this.style.opacity = '1';
			} );
			img.style.opacity = '0.5';
		}
	} );

	// Add hover effects to main image
	const mainImage = document.getElementById( 'main-gallery-image' );
	if ( mainImage ) {
		mainImage.addEventListener( 'mouseenter', function () {
			this.style.transform = 'scale(1.02)';
		} );

		mainImage.addEventListener( 'mouseleave', function () {
			this.style.transform = 'scale(1)';
		} );
	}
}

/**
 * Initialize gallery thumbnails functionality
 */
function initGalleryThumbnails() {
	const thumbnailsContainer = document.getElementById( 'gallery-thumbnails' );
	const mainImage = document.getElementById( 'main-gallery-image' );

	if ( ! thumbnailsContainer || ! mainImage ) {
		return;
	}

	const thumbnails = thumbnailsContainer.querySelectorAll( '[data-thumbnail]' );

	thumbnails.forEach( ( thumbnail ) => {
		thumbnail.addEventListener( 'click', function () {
			updateMainImageFromThumbnail( this );
		} );
	} );
}

/**
 * Initialize gallery modal functionality
 */
function initGalleryModal() {
	const modal = document.getElementById( 'image-modal' );
	const mainImageContainer = document.getElementById( 'product-main-image' );
	const modalImage = document.getElementById( 'modal-image' );
	const galleryDataEl = document.getElementById( 'modal-gallery-data' );

	if ( ! modal || ! mainImageContainer || ! modalImage ) {
		return;
	}

	// Parse gallery data
	if ( galleryDataEl ) {
		try {
			galleryImages = JSON.parse( galleryDataEl.textContent );
		} catch ( e ) {
			console.error( 'Failed to parse gallery data:', e );
			return;
		}
	}

	// Open modal on main image click
	mainImageContainer.addEventListener( 'click', openModalFromMain );

	// Close modal handlers
	modal.querySelectorAll( '[data-close-modal]' ).forEach( ( el ) => {
		el.addEventListener( 'click', closeModal );
	} );

	// Escape key to close
	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' && ! modal.classList.contains( 'hidden' ) ) {
			closeModal();
		}
		// Arrow keys for navigation
		if ( ! modal.classList.contains( 'hidden' ) ) {
			if ( e.key === 'ArrowLeft' ) {
				navigatePrev();
			} else if ( e.key === 'ArrowRight' ) {
				navigateNext();
			}
		}
	} );

	// Navigation buttons
	const prevBtn = document.getElementById( 'modal-prev' );
	const nextBtn = document.getElementById( 'modal-next' );

	if ( prevBtn ) {
		prevBtn.addEventListener( 'click', navigatePrev );
	}
	if ( nextBtn ) {
		nextBtn.addEventListener( 'click', navigateNext );
	}

	// Zoom controls
	const zoomInBtn = document.getElementById( 'modal-zoom-in' );
	const zoomOutBtn = document.getElementById( 'modal-zoom-out' );
	const zoomResetBtn = document.getElementById( 'modal-zoom-reset' );

	if ( zoomInBtn ) {
		zoomInBtn.addEventListener( 'click', zoomIn );
	}
	if ( zoomOutBtn ) {
		zoomOutBtn.addEventListener( 'click', zoomOut );
	}
	if ( zoomResetBtn ) {
		zoomResetBtn.addEventListener( 'click', resetZoom );
	}

	// Double click/tap to zoom
	modalImage.addEventListener( 'dblclick', toggleZoom );

	// Pan functionality
	modalImage.addEventListener( 'mousedown', startPan );
	document.addEventListener( 'mousemove', doPan );
	document.addEventListener( 'mouseup', endPan );

	// Touch support for pan
	modalImage.addEventListener( 'touchstart', handleTouchStart, { passive: false } );
	modalImage.addEventListener( 'touchmove', handleTouchMove, { passive: false } );
	modalImage.addEventListener( 'touchend', handleTouchEnd );

	// Prevent context menu on image
	modalImage.addEventListener( 'contextmenu', ( e ) => e.preventDefault() );

	// Additional modal enhancements
	initModalEnhancements();
}

/**
 * Initialize additional modal enhancements
 */
function initModalEnhancements() {
	const modal = document.getElementById( 'image-modal' );
	
	if ( ! modal ) {
		return;
	}

	// Prevent modal from closing when clicking on the image itself
	const modalImage = document.getElementById( 'modal-image' );
	if ( modalImage ) {
		modalImage.addEventListener( 'click', function ( e ) {
			e.stopPropagation();
		} );
	}

	// Add keyboard navigation hints
	const modalContent = modal.querySelector( '.modal-content' );
	if ( modalContent ) {
		// Show keyboard hints on first open
		let hasShownHints = false;
		
		const observer = new MutationObserver( function ( mutations ) {
			mutations.forEach( function ( mutation ) {
				if ( mutation.type === 'attributes' && mutation.attributeName === 'class' ) {
					if ( ! modal.classList.contains( 'hidden' ) && ! hasShownHints ) {
						showKeyboardHints();
						hasShownHints = true;
					}
				}
			} );
		} );

		observer.observe( modal, { attributes: true } );
	}
}

function showKeyboardHints() {
	// Create a temporary hint overlay
	const hints = document.createElement( 'div' );
	hints.className = 'absolute top-16 left-1/2 transform -translate-x-1/2 bg-black/70 text-white px-4 py-2 rounded-lg text-sm z-20';
	hints.innerHTML = 'Use arrow keys to navigate • ESC to close • Double-click to zoom';
	
	const modal = document.getElementById( 'image-modal' );
	if ( modal ) {
		modal.appendChild( hints );
		
		// Remove hints after 3 seconds
		setTimeout( () => {
			if ( hints.parentNode ) {
				hints.parentNode.removeChild( hints );
			}
		}, 3000 );
	}
}

/**
 * Update active thumbnail by image ID
 */
function setActiveThumbnail( imageId ) {
	const thumbnailsContainer = document.getElementById( 'gallery-thumbnails' );
	if ( ! thumbnailsContainer ) {
		return;
	}

	const thumbnails = thumbnailsContainer.querySelectorAll( '[data-thumbnail]' );
	thumbnails.forEach( ( thumb ) => {
		if ( thumb.dataset.imageId === String( imageId ) ) {
			thumb.classList.remove( 'border-transparent' );
			thumb.classList.add( 'border-gray-700' );
		} else {
			thumb.classList.remove( 'border-gray-700' );
			thumb.classList.add( 'border-transparent' );
		}
	} );
}

/**
 * Update main image from thumbnail without opening modal
 */
function updateMainImageFromThumbnail( thumbnail ) {
	const mainImage = document.getElementById( 'main-gallery-image' );
	if ( ! mainImage || ! thumbnail ) {
		return;
	}

	const largeSrc = thumbnail.dataset.largeSrc;
	const fullSrc = thumbnail.dataset.fullSrc;
	const imageId = thumbnail.dataset.imageId;

	if ( largeSrc ) {
		mainImage.src = largeSrc;
	}
	if ( imageId ) {
		mainImage.dataset.imageId = imageId;
	}
	if ( fullSrc ) {
		mainImage.dataset.fullSrc = fullSrc;
	}

	setActiveThumbnail( imageId );
}

// Modal functions
function openModalFromMain() {
	syncMainImage = true;
	openModalAtIndex( getCurrentImageIndex() );
}

function openModalFromThumbnail( index ) {
	syncMainImage = false;
	openModalAtIndex( index );
}

function getCurrentImageIndex() {
	const mainImage = document.getElementById( 'main-gallery-image' );

	if ( ! mainImage ) {
		return 0;
	}

	const currentImageId = mainImage.dataset.imageId;
	const index = galleryImages.findIndex(
		( img ) => String( img.id ) === String( currentImageId )
	);

	return index === -1 ? 0 : index;
}

function openModalAtIndex( index ) {
	const modal = document.getElementById( 'image-modal' );
	refreshGalleryData();

	if ( ! modal || galleryImages.length === 0 ) {
		return;
	}

	currentImageIndex = Math.max( 0, Math.min( index, galleryImages.length - 1 ) );

	// Load image and show modal
	loadModalImage( currentImageIndex );
	modal.classList.remove( 'hidden' );
	document.body.style.overflow = 'hidden';

	// Reset zoom
	resetZoom();
}

function refreshGalleryData() {
	const galleryDataEl = document.getElementById( 'modal-gallery-data' );

	if ( ! galleryDataEl ) {
		return;
	}

	try {
		galleryImages = JSON.parse( galleryDataEl.textContent );
	} catch ( e ) {
		console.error( 'Failed to parse gallery data:', e );
	}
}

function closeModal() {
	const modal = document.getElementById( 'image-modal' );
	if ( modal ) {
		modal.classList.add( 'hidden' );
		document.body.style.overflow = '';
	}
}

function loadModalImage( index ) {
	const modalImage = document.getElementById( 'modal-image' );
	const counter = document.getElementById( 'modal-image-counter' );

	if ( ! modalImage || ! galleryImages[ index ] ) {
		return;
	}

	const imageData = galleryImages[ index ];
	modalImage.src = imageData.full;
	modalImage.alt = imageData.alt || '';

	if ( counter ) {
		counter.textContent = index + 1;
	}

	// Update thumbnail selection
	setActiveThumbnail( imageData.id );

	// Update main gallery image
	const mainImage = document.getElementById( 'main-gallery-image' );
	if ( syncMainImage && mainImage && imageData.large ) {
		mainImage.src = imageData.large;
		mainImage.dataset.imageId = imageData.id;
		mainImage.dataset.fullSrc = imageData.full;
	}

	// Reset zoom when changing images
	resetZoom();
}

function navigatePrev() {
	if ( galleryImages.length <= 1 ) {
		return;
	}
	currentImageIndex =
		currentImageIndex > 0 ? currentImageIndex - 1 : galleryImages.length - 1;
	loadModalImage( currentImageIndex );
}

function navigateNext() {
	if ( galleryImages.length <= 1 ) {
		return;
	}
	currentImageIndex =
		currentImageIndex < galleryImages.length - 1 ? currentImageIndex + 1 : 0;
	loadModalImage( currentImageIndex );
}

function zoomIn() {
	if ( currentZoom < MAX_ZOOM ) {
		currentZoom = Math.min( currentZoom + ZOOM_STEP, MAX_ZOOM );
		applyTransform();
	}
}

function zoomOut() {
	if ( currentZoom > MIN_ZOOM ) {
		currentZoom = Math.max( currentZoom - ZOOM_STEP, MIN_ZOOM );
		// Reset pan if zooming out to 1x
		if ( currentZoom === MIN_ZOOM ) {
			translateX = 0;
			translateY = 0;
		}
		applyTransform();
	}
}

function resetZoom() {
	currentZoom = MIN_ZOOM;
	translateX = 0;
	translateY = 0;
	applyTransform();
}

function toggleZoom() {
	if ( currentZoom > MIN_ZOOM ) {
		resetZoom();
	} else {
		currentZoom = 2;
		applyTransform();
	}
}

function applyTransform() {
	const modalImage = document.getElementById( 'modal-image' );
	if ( modalImage ) {
		modalImage.style.transform = `translate(${ translateX }px, ${ translateY }px) scale(${ currentZoom })`;
	}
}

function startPan( e ) {
	if ( currentZoom <= MIN_ZOOM ) {
		return;
	}
	isPanning = true;
	startX = e.clientX - translateX;
	startY = e.clientY - translateY;

	const modalImage = document.getElementById( 'modal-image' );
	if ( modalImage ) {
		modalImage.style.cursor = 'grabbing';
	}
}

function doPan( e ) {
	if ( ! isPanning ) {
		return;
	}
	e.preventDefault();

	translateX = e.clientX - startX;
	translateY = e.clientY - startY;

	// Limit pan boundaries
	const maxPan = ( currentZoom - 1 ) * 200;
	translateX = Math.max( -maxPan, Math.min( maxPan, translateX ) );
	translateY = Math.max( -maxPan, Math.min( maxPan, translateY ) );

	applyTransform();
}

function endPan() {
	isPanning = false;
	const modalImage = document.getElementById( 'modal-image' );
	if ( modalImage ) {
		modalImage.style.cursor = currentZoom > MIN_ZOOM ? 'grab' : 'zoom-in';
	}
}

// Touch handlers
let touchStartX = 0;
let touchStartY = 0;

function handleTouchStart( e ) {
	if ( currentZoom <= MIN_ZOOM ) {
		return;
	}
	if ( e.touches.length === 1 ) {
		isPanning = true;
		touchStartX = e.touches[ 0 ].clientX - translateX;
		touchStartY = e.touches[ 0 ].clientY - translateY;
	}
}

function handleTouchMove( e ) {
	if ( ! isPanning || e.touches.length !== 1 ) {
		return;
	}
	e.preventDefault();

	translateX = e.touches[ 0 ].clientX - touchStartX;
	translateY = e.touches[ 0 ].clientY - touchStartY;

	const maxPan = ( currentZoom - 1 ) * 200;
	translateX = Math.max( -maxPan, Math.min( maxPan, translateX ) );
	translateY = Math.max( -maxPan, Math.min( maxPan, translateY ) );

	applyTransform();
}

function handleTouchEnd() {
	isPanning = false;
}