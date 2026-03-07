<?php
/**
 * Product Pagination Component
 * 
 * Renders pagination links for navigating through product pages.
 * Preserves all active filters and sorting in pagination URLs.
 * Nike-style design: circular buttons, minimal, modern.
 *
 * @param array|null $filter_params Optional filter parameters. If null, fetches from request.
 * @param int        $max_num_pages Maximum number of pages.
 * @param int        $current_page  Current page number.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_pagination( $filter_params = null, $max_num_pages = 1, $current_page = 1 ) {
	if ( $max_num_pages <= 1 ) {
		return;
	}
	
	if ( ! $filter_params ) {
		$filter_params = etheme_get_filter_params();
	}
	
	// Get clean base URL without query parameters
	$base_url = home_url( '/' );
	
	// If we're on a shop page, use that URL
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$shop_page_id = wc_get_page_id( 'shop' );
		if ( $shop_page_id ) {
			$base_url = get_permalink( $shop_page_id );
		}
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			$base_url = get_term_link( $term );
			if ( is_wp_error( $base_url ) ) {
				$base_url = home_url( '/' );
			}
		}
	} elseif ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
		$term = get_queried_object();
		if ( $term && ! is_wp_error( $term ) ) {
			$base_url = get_term_link( $term );
			if ( is_wp_error( $base_url ) ) {
				$base_url = home_url( '/' );
			}
		}
	}
	
	// Remove all query parameters from base URL to start clean
	$base_url_parts = parse_url( $base_url );
	$base_url = $base_url_parts['scheme'] . '://' . $base_url_parts['host'];
	if ( isset( $base_url_parts['port'] ) ) {
		$base_url .= ':' . $base_url_parts['port'];
	}
	if ( isset( $base_url_parts['path'] ) ) {
		$base_url .= $base_url_parts['path'];
	}
	
	// Build query args with context-specific rules
	$is_product_taxonomy = ( function_exists( 'is_product_category' ) && is_product_category() )
		|| ( function_exists( 'is_product_tag' ) && is_product_tag() );
	$query_args = etheme_build_url_query_args( $filter_params, array(
		'post_type'  => ! $is_product_taxonomy,
		'product_cat' => true,
		'search'     => true,
		'sort'       => true,
		'filters'    => true,
	) );
	if ( ! $is_product_taxonomy && ( ! isset( $query_args['post_type'] ) || $query_args['post_type'] !== 'product' ) ) {
		$query_args['post_type'] = 'product';
	}
	
	// Build pagination links manually for better control
	$current_page = max( 1, absint( $current_page ) );
	$prev_page = $current_page > 1 ? $current_page - 1 : 0;
	$next_page = $current_page < $max_num_pages ? $current_page + 1 : 0;
	
	// Calculate page numbers to show
	$end_size = 1;
	$mid_size = 2;
	$dots = false;
	
	$pages = array();
	
	// Always show first page
	if ( $current_page > $end_size + $mid_size + 1 ) {
		$pages[] = 1;
		$dots = true;
	}
	
	// Pages before current
	$start = max( 1, $current_page - $mid_size );
	$end = min( $max_num_pages, $current_page + $mid_size );
	
	if ( $dots && $start > $end_size + 1 ) {
		$pages[] = 'dots';
		$dots = false;
	}
	
	for ( $i = $start; $i <= $end; $i++ ) {
		$pages[] = $i;
	}
	
	// Pages after current
	if ( $end < $max_num_pages - $end_size ) {
		$pages[] = 'dots';
		$dots = true;
	}
	
	// Always show last page
	if ( $current_page < $max_num_pages - $mid_size - $end_size ) {
		if ( $dots ) {
			$pages[] = 'dots';
		}
		$pages[] = $max_num_pages;
	}
	
	// Function to build page URL - always include paged parameter
	$build_page_url = function( $page_num ) use ( $base_url, $query_args ) {
		// Always include paged parameter
		$query_args['paged'] = $page_num;
		
		// Build URL with query string
		$query_string = http_build_query( $query_args );
		return $base_url . '?' . $query_string;
	};
	?>
	
	<nav class="product-pagination mt-12 mb-8 flex items-center justify-center" aria-label="<?php esc_attr_e( 'Products pagination', 'etheme' ); ?>">
		<div class="flex items-center gap-2">
			<?php if ( $prev_page > 0 ) : ?>
				<a 
					href="<?php echo esc_url( $build_page_url( $prev_page ) ); ?>" 
					class="pagination-link pagination-prev rounded-full w-10 h-10 flex items-center justify-center bg-white border border-coolGray-200 text-coolGray-700 hover:bg-coolGray-50 hover:border-coolGray-300 transition-all duration-200 text-sm font-medium"
					aria-label="<?php esc_attr_e( 'Previous page', 'etheme' ); ?>"
				>
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
						<path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			<?php else : ?>
				<span class="pagination-link pagination-prev rounded-full w-10 h-10 flex items-center justify-center bg-coolGray-100 border border-coolGray-200 text-coolGray-400 cursor-not-allowed" aria-disabled="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
						<path d="M10 12L6 8L10 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			<?php endif; ?>
			
			<?php foreach ( $pages as $page_num ) : ?>
				<?php if ( $page_num === 'dots' ) : ?>
					<span class="pagination-dots px-2 text-coolGray-400">...</span>
				<?php elseif ( $page_num == $current_page ) : ?>
					<span class="pagination-current rounded-full w-10 h-10 flex items-center justify-center bg-black text-white font-semibold text-sm" aria-current="page">
						<?php echo esc_html( $page_num ); ?>
					</span>
				<?php else : ?>
					<a 
						href="<?php echo esc_url( $build_page_url( $page_num ) ); ?>" 
						class="pagination-link rounded-full w-10 h-10 flex items-center justify-center bg-white border border-coolGray-200 text-coolGray-700 hover:bg-coolGray-50 hover:border-coolGray-300 transition-all duration-200 text-sm font-medium"
						aria-label="<?php printf( esc_attr__( 'Page %d', 'etheme' ), $page_num ); ?>"
					>
						<?php echo esc_html( $page_num ); ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
			
			<?php if ( $next_page > 0 ) : ?>
				<a 
					href="<?php echo esc_url( $build_page_url( $next_page ) ); ?>" 
					class="pagination-link pagination-next rounded-full w-10 h-10 flex items-center justify-center bg-white border border-coolGray-200 text-coolGray-700 hover:bg-coolGray-50 hover:border-coolGray-300 transition-all duration-200 text-sm font-medium"
					aria-label="<?php esc_attr_e( 'Next page', 'etheme' ); ?>"
				>
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
						<path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</a>
			<?php else : ?>
				<span class="pagination-link pagination-next rounded-full w-10 h-10 flex items-center justify-center bg-coolGray-100 border border-coolGray-200 text-coolGray-400 cursor-not-allowed" aria-disabled="true">
					<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
						<path d="M6 4L10 8L6 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
					</svg>
				</span>
			<?php endif; ?>
		</div>
	</nav>
	<?php
}
