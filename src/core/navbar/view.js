document.addEventListener('DOMContentLoaded', () => {
	const navbar = document.querySelector('.etheme-navbar');
	if (!navbar) return;

	const mobileToggle = navbar.querySelector('.etheme-navbar__mobile-toggle');
	const mobileMenu = navbar.querySelector('.etheme-navbar__mobile-menu');
	const mobileClose = navbar.querySelector('.etheme-navbar__mobile-close');
	const mobileOverlay = navbar.querySelector('.etheme-navbar__mobile-overlay');

	const openMobileMenu = () => {
		if (mobileMenu) {
			mobileMenu.classList.add('active');
			document.body.style.overflow = 'hidden';
		}
	};

	const closeMobileMenu = () => {
		if (mobileMenu) {
			mobileMenu.classList.remove('active');
			document.body.style.overflow = '';
		}
	};

	if (mobileToggle) {
		mobileToggle.addEventListener('click', openMobileMenu);
	}

	if (mobileClose) {
		mobileClose.addEventListener('click', closeMobileMenu);
	}

	if (mobileOverlay) {
		mobileOverlay.addEventListener('click', closeMobileMenu);
	}

	initNavbarSearch(navbar);

	// Cart functionality
	initCartUpdates();
});

/**
 * Initialize navbar search toggle.
 */
function initNavbarSearch(navbar) {
	const search = navbar.querySelector('.etheme-navbar__search');
	if (!search) {
		return;
	}

	const toggle = search.querySelector('.etheme-navbar__search-toggle');
	const input = search.querySelector('.dgwt-wcas-search-input, .etheme-navbar__search-input');
	if (!toggle) {
		return;
	}

	const closeSearch = () => {
		search.classList.remove('is-open');
		toggle.setAttribute('aria-expanded', 'false');
	};

	const openSearch = () => {
		search.classList.add('is-open');
		toggle.setAttribute('aria-expanded', 'true');
		if (input) {
			input.focus();
			input.select();
		}
	};

	const handleToggle = (event) => {
		event.preventDefault();
		if (search.classList.contains('is-open')) {
			closeSearch();
		} else {
			openSearch();
		}
	};

	toggle.addEventListener('click', handleToggle);

	document.addEventListener('click', (event) => {
		if (!search.contains(event.target) && !event.target.closest('.dgwt-wcas-suggestions-wrapp')) {
			closeSearch();
		}
	});

	document.addEventListener('keydown', (event) => {
		if (event.key === 'Escape') {
			closeSearch();
		}
	});
}

/**
 * Initialize cart count updates via WooCommerce fragments
 */
function initCartUpdates() {
	const cartButtons = document.querySelectorAll('.etheme-navbar__cart-btn');
	if (cartButtons.length === 0) {
		return;
	}

	const updateCartCount = (count) => {
		cartButtons.forEach((btn) => {
			btn.setAttribute('data-cart-count', count);
			let badge = btn.querySelector('.etheme-navbar__cart-badge');
			
			if (count > 0) {
				if (!badge) {
					badge = document.createElement('span');
					badge.className = 'etheme-navbar__cart-badge';
					badge.setAttribute('aria-hidden', 'true');
					btn.appendChild(badge);
				}
				badge.textContent = count;
			} else if (badge) {
				badge.remove();
			}
		});
	};

	// Listen for WooCommerce added_to_cart event
	document.body.addEventListener('added_to_cart', (event) => {
		if (event.detail && event.detail.cart_count !== undefined) {
			updateCartCount(event.detail.cart_count);
		}
	});

	// Listen for WooCommerce cart fragments update via jQuery (WooCommerce standard)
	if (typeof jQuery !== 'undefined') {
		jQuery(document.body).on('wc_fragment_refresh updated_wc_div', () => {
			if (typeof wc_cart_fragments_params !== 'undefined' && wc_cart_fragments_params.fragments) {
				const fragments = wc_cart_fragments_params.fragments;
				// Try to get cart count from fragments
				for (const key in fragments) {
					if (fragments.hasOwnProperty(key)) {
						const parser = new DOMParser();
						const doc = parser.parseFromString(fragments[key], 'text/html');
						const cartCountElement = doc.querySelector('.cart-contents-count, .count');
						if (cartCountElement) {
							const count = parseInt(cartCountElement.textContent.trim(), 10) || 0;
							updateCartCount(count);
							return;
						}
					}
				}
			}
		});

		// Also listen for cart updates when items are removed
		jQuery(document.body).on('removed_from_cart', () => {
			// Trigger fragment refresh to get updated count
			if (typeof wc_cart_fragments_params !== 'undefined') {
				jQuery(document.body).trigger('wc_fragment_refresh');
			}
		});
	}
}
