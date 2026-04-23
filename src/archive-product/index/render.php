<?php
/**
 * Product Archive Index - Main orchestrator for product archive
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include helper functions
require_once get_template_directory() . '/src/archive-product/includes/filter-helpers.php';
require_once get_template_directory() . '/src/core/components/sub-banner.php';

// Ensure color-dots helper (defined in front-page) is available
if ( ! function_exists( 'etheme_get_product_color_dots_with_images' ) ) {
	require_once get_template_directory() . '/src/front-page/includes/front-page-index.helpers.php';
}

// Load popular-products card component from front-page (shared, no duplication)
require_once get_template_directory() . '/src/front-page/components/popular-products-card.php';

// Auto-load components
$components_dir = get_template_directory() . '/src/archive-product/components/';
$components = array(
	'header',
	'searchbar',
	'sorting',
	'filter-button',
	'filter-menu',
	'grid',
	'pagination',
	'card',
);

foreach ( $components as $component ) {
	require_once $components_dir . $component . '.php';
}

// Default filters (category first, then color, attribute, price)
$default_filters = array(
	array( 'type' => 'category' ),
	array( 'type' => 'color', 'taxonomy' => 'pa_color', 'termOverrides' => array() ),
	array( 'type' => 'attribute', 'taxonomy' => 'pa_size', 'label' => 'Size' ),
	array( 'type' => 'price', 'rangeColor' => '#fb704f' ),
);

$defaults = array(
	'columns'                 => 4,
	'perPage'                 => 12,
	'defaultOrderBy'          => 'date',
	'defaultOrder'            => 'desc',
	'showSearch'              => true,
	'showSorting'             => true,
	'filterParentCategories'  => true,
	'useParentChildrenOnly'   => true,
	'showParentCategoryBar'   => true,
	'filters'                 => $default_filters,
);
$attributes = wp_parse_args( $attributes, $defaults );

// Normalize filters: ensure array, category first, valid items only
$raw_filters = isset( $attributes['filters'] ) ? $attributes['filters'] : $default_filters;
if ( is_string( $raw_filters ) ) {
	$raw_filters = json_decode( $raw_filters, true );
}
if ( ! is_array( $raw_filters ) ) {
	$raw_filters = $default_filters;
}
$filter_config = array();
$has_category = false;
foreach ( $raw_filters as $item ) {
	if ( ! is_array( $item ) || empty( $item['type'] ) ) {
		continue;
	}
	if ( $item['type'] === 'category' ) {
		if ( ! $has_category ) {
			$filter_config[] = array( 'type' => 'category' );
			$has_category = true;
		}
		continue;
	}
	if ( $item['type'] === 'color' && ! empty( $item['taxonomy'] ) ) {
		$filter_config[] = array(
			'type'          => 'color',
			'taxonomy'      => $item['taxonomy'],
			'termOverrides' => isset( $item['termOverrides'] ) && is_array( $item['termOverrides'] ) ? $item['termOverrides'] : array(),
		);
	} elseif ( $item['type'] === 'attribute' && ! empty( $item['taxonomy'] ) ) {
		$filter_config[] = array(
			'type'     => 'attribute',
			'taxonomy' => $item['taxonomy'],
			'label'    => isset( $item['label'] ) ? $item['label'] : 'Attribute',
		);
	} elseif ( $item['type'] === 'price' ) {
		$filter_config[] = array(
			'type'       => 'price',
			'rangeColor' => isset( $item['rangeColor'] ) ? $item['rangeColor'] : '#fb704f',
		);
	}
}
if ( ! $has_category ) {
	array_unshift( $filter_config, array( 'type' => 'category' ) );
}
if ( empty( $filter_config ) ) {
	$filter_config = $default_filters;
}

// Derive attribute taxonomies for URL and query (color + attribute types)
$attribute_taxonomies = array();
foreach ( $filter_config as $item ) {
	if ( isset( $item['type'] ) && in_array( $item['type'], array( 'color', 'attribute' ), true ) && ! empty( $item['taxonomy'] ) ) {
		$attribute_taxonomies[] = $item['taxonomy'];
	}
}
$attribute_taxonomies = array_unique( $attribute_taxonomies );

etheme_set_archive_attribute_taxonomies( $attribute_taxonomies );

// Get filter parameters and build query
$filter_params = etheme_get_filter_params( $attribute_taxonomies );
$query_args   = etheme_build_query_args( $filter_params, $attributes, $attribute_taxonomies );

// Execute WooCommerce product query
$products_query = wc_get_products( $query_args );

$columns = absint( $attributes['columns'] );
$per_page = absint( $attributes['perPage'] );
$current_sort = ( $filter_params['orderby'] ?: $attributes['defaultOrderBy'] ) . '-' . 
                ( $filter_params['order'] ?: $attributes['defaultOrder'] );
$has_filters = etheme_has_active_filters( $filter_params );
$show_search = $attributes['showSearch'];
$filter_parent_categories = (bool) $attributes['filterParentCategories'];
$use_parent_children_only = (bool) $attributes['useParentChildrenOnly'];
$show_parent_category_bar = (bool) $attributes['showParentCategoryBar'];

// Extract paginated results
$products = $products_query->products;
$total_products = $products_query->total;
$max_num_pages = $products_query->max_num_pages;

// Results counter for top-icons bar
$results_count  = count( $products );
$results_start  = max( 1, ( $filter_params['paged'] - 1 ) * $per_page + 1 );
$results_end    = min( $results_start + $results_count - 1, $total_products );
$search_categories = ! empty( $filter_params['search'] )
	? etheme_get_search_result_categories_from_products( $products )
	: null;
$exclude_category_ids = array();
if ( $show_parent_category_bar ) {
	$parent_cats = etheme_get_parent_product_categories();
	if ( ! empty( $parent_cats ) && ! is_wp_error( $parent_cats ) ) {
		$exclude_category_ids = array_map( function ( $t ) {
			return $t->term_id;
		}, $parent_cats );
	}
}
?>

<div <?php echo get_block_wrapper_attributes(); ?>>

	<?php
	$sorting_data = $attributes['showSorting'] ? array(
		'filter_params' => $filter_params,
		'current_sort'  => $current_sort,
	) : null;

	$is_compact_archive =
		! empty( $filter_params['search'] )
		|| ! empty( get_query_var( 's' ) )
		|| is_product_category()
		|| is_product_tag()
		|| ! empty( get_query_var( 'product_cat' ) )
		|| ! empty( get_query_var( 'product_tag' ) );

	etheme_render_sub_banner( array(
		'title'         => etheme_get_archive_title( $filter_params ),
		'subtitle'      => etheme_get_archive_subtitle( $filter_params ),
		'breadcrumbs'   => etheme_get_archive_breadcrumbs( $filter_params ),
		'compact'       => $is_compact_archive,
		'after_content' => $show_parent_category_bar
			? function () use ( $filter_params, $show_parent_category_bar ) {
				etheme_render_archive_category_bar( $filter_params, $show_parent_category_bar );
			}
			: null,
	) );
	?>

	<section class="bg-white">
		<div class="w-full px-4 ">

			<!-- Filter Button (Mobile Only): full-width bar, extra top spacing -->
			<div class="md:hidden mt-12 sm:mt-16 mx-0 w-full">
				<?php etheme_render_filter_button( $has_filters ); ?>
			</div>

			<!-- Main Content: Filters Sidebar + Products Grid -->
			<div class="flex flex-wrap -mx-4 md:px-[1vw] lg:px-[3vw]">

				<?php
				etheme_render_filter_menu( $filter_params, false, $sorting_data, $filter_parent_categories, $use_parent_children_only, $search_categories, $exclude_category_ids, $filter_config );
				?>

				<!-- Product Grid Component (Main Content) -->
				<div class="pb-8 w-full md:w-2/3 lg:w-3/4 px-4">

					<!-- Top icons bar: results count + sorting (Contrive style) -->
					<div class="shop-top-bar" data-aos="fade-up">
						<span class="shop-top-bar__results">
							<?php if ( $total_products > 0 ) :
								printf(
									/* translators: 1: start, 2: end, 3: total */
									esc_html__( 'Showing %1$s–%2$s of %3$s results', 'etheme' ),
									$results_start,
									$results_end,
									$total_products
								);
							else :
								esc_html_e( 'No results found', 'etheme' );
							endif; ?>
						</span>
						<?php etheme_render_sorting( $filter_params, $current_sort, true, false ); ?>
					</div>

					<?php etheme_render_product_grid( $products, $filter_params, $columns, $per_page, $total_products ); ?>

					<?php etheme_render_pagination( $filter_params, $max_num_pages, $filter_params['paged'] ); ?>
				</div>

			</div>
		</div>
	</section>

</div>
