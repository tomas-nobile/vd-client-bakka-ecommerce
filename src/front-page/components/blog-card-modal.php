<?php
// home-blog-card-modal.
/**
 * Home Blog Card Modal Component
 *
 * Renders the shared modal shell for blog post cards.
 * JS (home-blog-modal.js) populates #blog-modal-media and
 * #blog-modal-description dynamically when a card is clicked.
 *
 * Pattern mirrors src/single-product/components/image-modal.php:
 * backdrop with data-close-modal, centred panel, close button.
 *
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_home_blog_card_modal() {
	?>
	<div
		id="blog-post-modal"
		class="blog-modal"
		role="dialog"
		aria-modal="true"
		aria-label="<?php esc_attr_e( 'Ver post', 'etheme' ); ?>"
		hidden
	>
		<div class="blog-modal__backdrop" data-close-modal></div>

		<div class="blog-modal__panel">

			<button
				type="button"
				class="blog-modal__close"
				data-close-modal
				aria-label="<?php esc_attr_e( 'Cerrar', 'etheme' ); ?>"
			>
				<svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
				</svg>
			</button>

			<div class="blog-modal__media" id="blog-modal-media">
				<!-- Populated by JS -->
			</div>

			<div class="blog-modal__nav" id="blog-modal-nav" hidden>
				<button
					type="button"
					class="blog-modal__arrow blog-modal__arrow--prev"
					id="blog-modal-prev"
					aria-label="<?php esc_attr_e( 'Imagen anterior', 'etheme' ); ?>"
				>
					<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
					</svg>
				</button>

				<span class="blog-modal__counter" id="blog-modal-counter" aria-live="polite"></span>

				<button
					type="button"
					class="blog-modal__arrow blog-modal__arrow--next"
					id="blog-modal-next"
					aria-label="<?php esc_attr_e( 'Imagen siguiente', 'etheme' ); ?>"
				>
					<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
					</svg>
				</button>
			</div>

			<div class="blog-modal__description" id="blog-modal-description">
				<!-- Populated by JS -->
			</div>

		</div>
	</div>
	<?php
}
