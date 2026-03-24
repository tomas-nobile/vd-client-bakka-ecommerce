<?php
/**
 * Product Pagination Component
 *
 * Renders Contrive-style pagination: square 45×45px buttons, border, accent
 * fill on hover/active. Preserves all active filters and sorting in URLs.
 *
 * @param array|null $filter_params Optional filter parameters.
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

	$base_url  = _etheme_pagination_base_url();
	$query_args = _etheme_pagination_query_args( $filter_params );
	$pages      = _etheme_pagination_page_numbers( (int) $current_page, (int) $max_num_pages );

	$current_page = max( 1, absint( $current_page ) );
	$prev_page    = $current_page > 1 ? $current_page - 1 : 0;
	$next_page    = $current_page < $max_num_pages ? $current_page + 1 : 0;

	$build_url = function ( $page_num ) use ( $base_url, $query_args ) {
		$args          = $query_args;
		$args['paged'] = $page_num;
		return $base_url . '?' . http_build_query( $args );
	};
	?>

	<nav class="shop-pagination" aria-label="<?php esc_attr_e( 'Products pagination', 'etheme' ); ?>">

		<?php if ( $prev_page > 0 ) : ?>
			<a href="<?php echo esc_url( $build_url( $prev_page ) ); ?>" class="page-btn" aria-label="<?php esc_attr_e( 'Previous page', 'etheme' ); ?>">
				<?php echo _etheme_pagination_chevron_left(); ?>
			</a>
		<?php else : ?>
			<span class="page-btn page-btn--disabled" aria-disabled="true">
				<?php echo _etheme_pagination_chevron_left(); ?>
			</span>
		<?php endif; ?>

		<?php foreach ( $pages as $page_num ) : ?>
			<?php if ( 'dots' === $page_num ) : ?>
				<span class="page-dots">&hellip;</span>
			<?php elseif ( (int) $page_num === $current_page ) : ?>
				<span class="page-btn page-btn--active" aria-current="page"><?php echo esc_html( $page_num ); ?></span>
			<?php else : ?>
				<a href="<?php echo esc_url( $build_url( $page_num ) ); ?>" class="page-btn" aria-label="<?php printf( esc_attr__( 'Page %d', 'etheme' ), $page_num ); ?>">
					<?php echo esc_html( $page_num ); ?>
				</a>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php if ( $next_page > 0 ) : ?>
			<a href="<?php echo esc_url( $build_url( $next_page ) ); ?>" class="page-btn" aria-label="<?php esc_attr_e( 'Next page', 'etheme' ); ?>">
				<?php echo _etheme_pagination_chevron_right(); ?>
			</a>
		<?php else : ?>
			<span class="page-btn page-btn--disabled" aria-disabled="true">
				<?php echo _etheme_pagination_chevron_right(); ?>
			</span>
		<?php endif; ?>

	</nav>
	<?php
}

/**
 * Build the clean base URL for pagination links.
 */
function _etheme_pagination_base_url() {
	$base_url = home_url( '/' );
	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$shop_id = wc_get_page_id( 'shop' );
		if ( $shop_id ) {
			$base_url = get_permalink( $shop_id );
		}
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$base_url = _etheme_pagination_term_url();
	} elseif ( function_exists( 'is_product_tag' ) && is_product_tag() ) {
		$base_url = _etheme_pagination_term_url();
	}

	$parts    = parse_url( $base_url );
	$clean    = $parts['scheme'] . '://' . $parts['host'];
	if ( isset( $parts['port'] ) ) {
		$clean .= ':' . $parts['port'];
	}
	if ( isset( $parts['path'] ) ) {
		$clean .= $parts['path'];
	}
	return $clean;
}

/** Get the current term permalink, fallback to home. */
function _etheme_pagination_term_url() {
	$term = get_queried_object();
	if ( $term && ! is_wp_error( $term ) ) {
		$url = get_term_link( $term );
		return is_wp_error( $url ) ? home_url( '/' ) : $url;
	}
	return home_url( '/' );
}

/** Build query args preserving filters for pagination links. */
function _etheme_pagination_query_args( $filter_params ) {
	$is_tax   = ( function_exists( 'is_product_category' ) && is_product_category() )
				|| ( function_exists( 'is_product_tag' ) && is_product_tag() );
	$args     = etheme_build_url_query_args( $filter_params, array(
		'post_type'   => ! $is_tax,
		'product_cat' => true,
		'search'      => true,
		'sort'        => true,
		'filters'     => true,
	) );
	if ( ! $is_tax && ( ! isset( $args['post_type'] ) || 'product' !== $args['post_type'] ) ) {
		$args['post_type'] = 'product';
	}
	return $args;
}

/** Calculate page numbers array (with optional dots). */
function _etheme_pagination_page_numbers( $current, $max ) {
	$end_size = 1;
	$mid_size = 2;
	$pages    = array();
	$dots     = false;

	if ( $current > $end_size + $mid_size + 1 ) {
		$pages[] = 1;
		$dots    = true;
	}

	$start = max( 1, $current - $mid_size );
	$end   = min( $max, $current + $mid_size );

	if ( $dots && $start > $end_size + 1 ) {
		$pages[] = 'dots';
		$dots    = false;
	}

	for ( $i = $start; $i <= $end; $i++ ) {
		$pages[] = $i;
	}

	if ( $end < $max - $end_size ) {
		$pages[] = 'dots';
		$dots    = true;
	}

	if ( $current < $max - $mid_size - $end_size ) {
		if ( $dots ) {
			$pages[] = 'dots';
		}
		$pages[] = $max;
	}

	return $pages;
}

/** SVG chevron left icon for prev button. */
function _etheme_pagination_chevron_left() {
	return '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}

/** SVG chevron right icon for next button. */
function _etheme_pagination_chevron_right() {
	return '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true"><path d="M5 3l4 4-4 4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>';
}
