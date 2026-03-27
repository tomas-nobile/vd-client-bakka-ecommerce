<?php
/**
 * Product Filter Toggle Button Component
 *
 * Renders a Contrive-style button that opens the mobile filter drawer.
 *
 * @param bool $has_active_filters Whether there are active filters applied.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_filter_button( $has_active_filters = false ) {
	?>
	<div class="mb-5 w-full max-w-none" data-aos="fade-up">
		<button
			type="button"
			id="toggle-filters"
			class="shop-filter-btn"
			aria-expanded="false"
			aria-controls="filters-content"
		>
			<svg xmlns="http://www.w3.org/2000/svg" width="15" height="12" viewBox="0 0 15 12" fill="none" aria-hidden="true">
				<path d="M1 1h13M3 6h9M5 11h5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
			</svg>
			<?php esc_html_e( 'Filters', 'etheme' ); ?>
		</button>
	</div>
	<?php
}
