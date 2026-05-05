<?php
/**
 * Product Archive Header Component
 *
 * Helper functions for building the archive page header
 * via the reusable sub-banner component.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build the dynamic title for the archive page.
 *
 * @param array $filter_params Filter parameters from request.
 * @return string
 */
function etheme_get_archive_title( $filter_params ) {
	if ( ! empty( $filter_params['search'] ) ) {
		return $filter_params['search'];
	}
	if ( is_product_category() || is_product_tag() ) {
		return single_term_title( '', false );
	}
	return __( 'TIENDA', 'etheme' );
}

/**
 * Build breadcrumbs array for the archive sub-banner.
 *
 * Pattern: HOME / SHOP on base; HOME / SHOP / TERM on category, tag, or search.
 *
 * @param array $filter_params Filter parameters from request.
 * @return array
 */
function etheme_get_archive_breadcrumbs( $filter_params ) {
	$shop_url    = wc_get_page_permalink( 'shop' );
	$breadcrumbs = array(
		array( 'label' => __( 'Inicio', 'etheme' ), 'url' => home_url( '/' ) ),
	);

	if ( is_product_category() || is_product_tag() ) {
		$breadcrumbs[] = array( 'label' => __( 'Tienda', 'etheme' ), 'url' => $shop_url );
		$breadcrumbs[] = array( 'label' => single_term_title( '', false ) );
		return $breadcrumbs;
	}

	if ( ! empty( $filter_params['search'] ) ) {
		$breadcrumbs[] = array( 'label' => __( 'Tienda', 'etheme' ), 'url' => $shop_url );
		$breadcrumbs[] = array( 'label' => $filter_params['search'] );
		return $breadcrumbs;
	}

	$breadcrumbs[] = array( 'label' => __( 'Tienda', 'etheme' ), 'url' => $shop_url );
	return $breadcrumbs;
}

/**
 * Return subtitle for archive sub-banner.
 *
 * Only shown on the base shop page; empty on category, tag, or search.
 *
 * @param array $filter_params Filter parameters from request.
 * @return string
 */
function etheme_get_archive_subtitle( $filter_params ) {
	if ( ! empty( $filter_params['search'] ) || is_product_category() || is_product_tag() ) {
		return '';
	}
	return __( 'Explorá nuestra colección completa', 'etheme' );
}

/**
 * Render the horizontal parent category chips bar.
 *
 * Shares the same background image as the sub-banner above it.
 *
 * @param array $filter_params            Filter parameters from request.
 * @param bool  $show_parent_category_bar Whether to render the bar.
 */
function etheme_render_archive_category_bar( $filter_params, $show_parent_category_bar = true ) {
	if ( ! $show_parent_category_bar ) {
		return;
	}

	$parent_categories = etheme_get_parent_product_categories();
	if ( empty( $parent_categories ) || is_wp_error( $parent_categories ) ) {
		return;
	}

	$active_parent_id = etheme_get_active_parent_category_id( $filter_params );
	$bg_url           = esc_url( get_template_directory_uri() . '/assets/images/subbanner-backgroundimage.jpg' );
	$scroll_id        = wp_unique_id( 'archive-category-scroll-' );
	?>
	<div
		class="archive-category-bar"
		style="background-image: url('<?php echo $bg_url; ?>');"
		data-aos="fade-up"
	>
		<div class="archive-category-track-wrap" data-archive-category-nav>
			<div
				id="<?php echo esc_attr( $scroll_id ); ?>"
				class="archive-category-scroll flex flex-nowrap gap-2 overflow-x-auto mt-[-40px] pb-4"
				role="region"
				aria-label="<?php echo esc_attr__( 'Categorías de producto', 'etheme' ); ?>"
				tabindex="0"
			>
				<?php foreach ( $parent_categories as $category ) :
					$is_active = $active_parent_id && ( $active_parent_id === absint( $category->term_id ) );
					$term_link = get_term_link( $category );
					if ( is_wp_error( $term_link ) ) {
						continue;
					}
					?>
					<a
						href="<?php echo esc_url( $term_link ); ?>"
						class="archive-cat-chip inline-flex items-center px-4 py-2 rounded-md text-sm font-semibold border transition-colors no-underline <?php echo $is_active ? 'active' : ''; ?>"
					>
						<?php echo esc_html( $category->name ); ?>
					</a>
				<?php endforeach; ?>
			</div>
			<button
				type="button"
				class="archive-category-nav archive-category-nav--prev lg:hidden"
				data-archive-category-prev
				aria-controls="<?php echo esc_attr( $scroll_id ); ?>"
				aria-label="<?php esc_attr_e( 'Desplazar categorías hacia la izquierda', 'etheme' ); ?>"
			>
				<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
				</svg>
			</button>
			<button
				type="button"
				class="archive-category-nav archive-category-nav--next"
				data-archive-category-next
				aria-controls="<?php echo esc_attr( $scroll_id ); ?>"
				aria-label="<?php esc_attr_e( 'Desplazar categorías hacia la derecha', 'etheme' ); ?>"
			>
				<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
				</svg>
			</button>
		</div>
	</div>
	<?php
}

/**
 * @deprecated Replaced by etheme_render_sub_banner() + etheme_render_archive_category_bar().
 *             Kept as no-op stub for backward compatibility.
 */
function etheme_render_archive_header( $filter_params, $total_products, $attributes, $sorting_data = null, $show_parent_category_bar = true ) {
}
