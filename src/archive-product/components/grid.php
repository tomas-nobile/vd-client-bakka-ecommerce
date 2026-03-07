<?php
/**
 * Product Grid Component
 * 
 * Renders a responsive grid of products with results counter and empty state.
 *
 * @param array $products       Array of WC_Product objects.
 * @param array $filter_params  Filter parameters for pagination/URLs.
 * @param int   $columns        Number of columns (1-6).
 * @param int   $per_page       Products per page.
 * @param int   $total_products Total number of products found.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_product_grid( $products, $filter_params, $columns = 4, $per_page = 12, $total_products = 0 ) {
	// Calculate grid classes (static for performance)
	static $col_classes = array(
		1 => 'grid-cols-1',
		2 => 'grid-cols-1 sm:grid-cols-2',
		3 => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3',
		4 => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4',
		5 => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5',
		6 => 'grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6',
	);
	$grid_class = isset( $col_classes[ $columns ] ) ? $col_classes[ $columns ] : $col_classes[4];
	
	// Calculate results counter
	$product_count = count( $products );
	$start = ( $filter_params['paged'] - 1 ) * $per_page + 1;
	$end = min( $start + $product_count - 1, $total_products );
	$has_filters = etheme_has_active_filters( $filter_params );
	?>
	
	<?php if ( ! empty( $products ) ) : ?>
	<!-- Product Grid -->
	<div class="flex flex-wrap -mx-4 min-w-0">
		<?php 
		foreach ( $products as $product ) :
			// Get product ID (works with both WC_Product objects and IDs)
			$product_id = is_object( $product ) ? $product->get_id() : $product;
		?>
		<div class="w-full sm:w-1/2 lg:w-1/3 px-2 md:px-4 min-w-0 box-border">
			<?php
			// Render the Product Card component
			etheme_render_product_card( $product_id );
			?>
		</div>
		<?php endforeach; ?>
	</div>

	<?php else : ?>
	<!-- No Products Found -->
	<div class="text-center py-12 bg-gray-50 rounded-lg">
		<div class="text-6xl mb-4">🔍</div>
		<p class="text-gray-600 text-lg mb-4">
			<?php esc_html_e( 'No products found matching your criteria.', 'etheme' ); ?>
		</p>
		<?php if ( $has_filters ) : ?>
		<a 
			href="<?php echo esc_url( etheme_get_clear_filters_url( true, true, $filter_params ) ); ?>" 
			class="inline-block px-6 py-3 bg-blue-600 text-white rounded-lg font-semibold hover:bg-blue-700 transition-colors"
		>
			<?php esc_html_e( 'Clear filters and show all products', 'etheme' ); ?>
		</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<?php
}

