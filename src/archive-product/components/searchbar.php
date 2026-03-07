<?php
/**
 * Product Search Bar Component
 * 
 * Renders a search form for filtering products by search term.
 * Preserves all active filters when performing a search.
 *
 * @param array|null $filter_params Optional filter parameters. If null, fetches from request.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_searchbar( $filter_params = null ) {
	if ( ! $filter_params ) {
		$filter_params = etheme_get_filter_params();
	}
	
	$placeholder = __( '🔍 Search products...', 'etheme' );
	?>
	
	<div class="bg-white p-4 rounded-lg shadow-md mb-4">
		<form method="GET" action="" class="search-form">
			<?php
			// Preserve filters and sort when searching (not search itself)
			etheme_render_preserved_params( $filter_params, array(
				'search'  => false,
				'sort'    => true,
				'filters' => true,
			) );
			?>
			<div class="flex gap-2">
				<input 
					type="text" 
					name="s" 
					value="<?php echo esc_attr( $filter_params['search'] ); ?>"
					placeholder="<?php echo esc_attr( $placeholder ); ?>"
					class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
				>
				<button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors">
					<?php esc_html_e( 'Search', 'etheme' ); ?>
				</button>
			</div>
		</form>
	</div>
	<?php
}

