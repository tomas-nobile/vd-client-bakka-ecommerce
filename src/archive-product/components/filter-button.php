<?php
/**
 * Product Filter Toggle Button Component
 * 
 * Renders a button to toggle the visibility of the filter menu.
 *
 * @param bool $has_active_filters Whether there are active filters applied.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_filter_button( $has_active_filters = false ) {
	?>
	
	<div class="mb-6">
		<button 
			type="button" 
			id="toggle-filters" 
			class="w-full bg-gray-800 text-white p-4 rounded-lg shadow-md font-semibold hover:bg-gray-700 transition-colors flex items-center justify-between"
		>
			<span>🎛️ <?php esc_html_e( 'Filters', 'etheme' ); ?></span>
			<span class="arrow transition-transform text-2xl font-bold">+</span>
		</button>
	</div>
	<?php
}

