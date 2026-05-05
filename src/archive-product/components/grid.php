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
		$index = 0;
		foreach ( $products as $product_item ) :
			$product = is_object( $product_item ) ? $product_item : wc_get_product( $product_item );
			if ( ! $product ) {
				continue;
			}
			$aos_delay = ( $index % 3 ) * 80;
		?>
		<div class="w-full sm:w-1/2 lg:w-1/3 px-2 md:px-4 min-w-0 box-border"
			data-aos="fade-up"
			data-aos-delay="<?php echo esc_attr( $aos_delay ); ?>"
		>
			<?php etheme_render_home_popular_product_card( $product, true ); ?>
		</div>
		<?php
			$index++;
		endforeach; ?>
	</div>

	<?php else : ?>
	<!-- No Products Found -->
	<div class="text-center py-12 px-6 md:px-10 border border-[#d9e3e2] bg-[#f5f8f8]">
		<div class="inline-flex items-center justify-center w-12 h-12 mb-5 rounded-full border border-[#2b5756]/25 text-[#2b5756]">
			<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-4.35-4.35m1.35-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
			</svg>
		</div>
		<p class="text-[#2b5756] text-xl md:text-2xl font-semibold mb-2">
			<?php esc_html_e( 'No se encontraron productos que coincidan con tu búsqueda.', 'etheme' ); ?>
		</p>
		<p class="text-[#5f7675] text-sm md:text-base mb-6">
			<?php esc_html_e( 'Probá ajustar los filtros para encontrar más productos.', 'etheme' ); ?>
		</p>
		<?php if ( $has_filters ) : ?>
		<a 
			href="<?php echo esc_url( etheme_get_clear_filters_url( true, true, $filter_params ) ); ?>" 
			class="primary_btn inline-flex items-center justify-center"
		>
			<?php esc_html_e( 'Clear filters and show all products', 'etheme' ); ?>
		</a>
		<?php endif; ?>
	</div>
	<?php endif; ?>
	<?php
}

