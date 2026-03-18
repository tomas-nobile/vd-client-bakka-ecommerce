<?php
// core/blog-card-modal.
/**
 * Blog Card Modal Component (core, reusable)
 *
 * Renders the shared modal shell for blog post cards.
 * JS (blog-modal.js) populates content dynamically when a card is clicked.
 *
 * Used by: front-page-index (home), page-posteos-index.
 * Include this once per page; the modal is shared by all cards on the page.
 *
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the blog card modal shell.
 * JS populates #blog-modal-media and #blog-modal-description on card click.
 */
function etheme_render_blog_card_modal() {
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
				<div class="blog-modal__media-inner" id="blog-modal-media-inner">
					<!-- Populated by JS -->
				</div>

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

				<div class="blog-modal__dots" id="blog-modal-dots" aria-hidden="true"></div>
			</div>

			<div class="blog-modal__body">
				<div class="blog-modal__caption-row">
					<div class="blog-modal__description" id="blog-modal-description"><!-- Populated by JS --></div>
				</div>
				<div class="blog-modal__footer">
					<time class="blog-modal__date" id="blog-modal-date" datetime=""><!-- Populated by JS --></time>
					<a
						href="#"
						class="blog-modal__instagram-link"
						id="blog-modal-social-link"
						target="_blank"
						rel="noopener noreferrer"
						aria-label="<?php esc_attr_e( 'Ir al post en la red social', 'etheme' ); ?>"
					>
						<img src="" class="blog-modal__instagram-icon" id="blog-modal-social-icon" width="24" height="24" alt="" aria-hidden="true" />
					</a>
				</div>
			</div>

		</div>
	</div>
	<?php
}
