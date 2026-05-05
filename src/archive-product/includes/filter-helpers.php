<?php
/**
 * Shared helper functions for product archive filters
 *
 * @package etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Archive attribute taxonomies set by the block (used for dynamic filter params and URL building).
 *
 * @var array
 */
$GLOBALS['etheme_archive_attribute_taxonomies'] = array();

/**
 * Set the list of attribute taxonomies used by the current archive block (e.g. pa_color, pa_size).
 *
 * @param array $taxonomies List of taxonomy slugs.
 * @return void
 */
function etheme_set_archive_attribute_taxonomies( $taxonomies ) {
	$GLOBALS['etheme_archive_attribute_taxonomies'] = is_array( $taxonomies ) ? $taxonomies : array();
}

/**
 * Get the list of attribute taxonomies set for the current archive block.
 *
 * @return array
 */
function etheme_get_archive_attribute_taxonomies() {
	return isset( $GLOBALS['etheme_archive_attribute_taxonomies'] ) && is_array( $GLOBALS['etheme_archive_attribute_taxonomies'] )
		? $GLOBALS['etheme_archive_attribute_taxonomies']
		: array();
}

/**
 * Get and sanitize query parameters from URL.
 *
 * @param array $attribute_taxonomies Optional. List of attribute taxonomy slugs (e.g. pa_color, pa_size). When provided, reads filter_{taxonomy}[] from GET and fills $params['attributes']. When empty, uses legacy filter_colors / filter_sizes.
 * @return array
 */
function etheme_get_filter_params( $attribute_taxonomies = array() ) {
	$params = array();

	// Sorting
	$orderby = '';
	$order = '';
	if ( isset( $_GET['orderby'] ) && strpos( (string) $_GET['orderby'], '-' ) !== false ) {
		$parts = explode( '-', sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) );
		if ( count( $parts ) === 2 ) {
			$orderby = $parts[0];
			$order = $parts[1];
		}
	}

	$valid_orderby = array( 'price', 'popularity', 'date' );
	$valid_order   = array( 'asc', 'desc' );

	$params['orderby'] = in_array( $orderby, $valid_orderby, true ) ? $orderby : '';
	$params['order']   = in_array( $order, $valid_order, true ) ? $order : '';
	$paged             = get_query_var( 'paged' );
	if ( ! $paged && isset( $_GET['paged'] ) ) {
		$paged = absint( $_GET['paged'] );
	}
	$params['paged'] = $paged ? max( 1, $paged ) : 1;

	// Search
	$params['search'] = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

	// Categories
	$params['categories'] = isset( $_GET['filter_categories'] ) && is_array( $_GET['filter_categories'] )
		? array_map( 'absint', wp_unslash( $_GET['filter_categories'] ) )
		: array();

	// Price range
	$params['min_price'] = isset( $_GET['min_price'] ) && $_GET['min_price'] !== '' ? floatval( $_GET['min_price'] ) : 0;
	$params['max_price'] = isset( $_GET['max_price'] ) && $_GET['max_price'] !== '' ? floatval( $_GET['max_price'] ) : 0;

	$params['attributes'] = array();

	if ( ! empty( $attribute_taxonomies ) ) {
		foreach ( $attribute_taxonomies as $tax ) {
			$key = 'filter_' . sanitize_key( $tax );
			if ( isset( $_GET[ $key ] ) && is_array( $_GET[ $key ] ) ) {
				$params['attributes'][ $tax ] = array_map( 'sanitize_text_field', wp_unslash( $_GET[ $key ] ) );
			} else {
				$params['attributes'][ $tax ] = array();
			}
		}
		$params['colors'] = array();
		$params['sizes']  = array();
	} else {
		$params['colors'] = isset( $_GET['filter_colors'] ) && is_array( $_GET['filter_colors'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_GET['filter_colors'] ) )
			: array();
		$params['sizes']  = isset( $_GET['filter_sizes'] ) && is_array( $_GET['filter_sizes'] )
			? array_map( 'sanitize_text_field', wp_unslash( $_GET['filter_sizes'] ) )
			: array();
		$params['attributes']['pa_color'] = $params['colors'];
		$params['attributes']['pa_size']  = $params['sizes'];
	}

	// On sale
	$params['on_sale'] = isset( $_GET['on_sale'] ) && $_GET['on_sale'] === '1';

	return $params;
}

/**
 * Get current product category term ID from archive context.
 *
 * @return int
 */
function etheme_get_current_product_category_id() {
	if ( ! is_product_category() ) {
		return 0;
	}
	
	$term = get_queried_object();
	if ( ! $term || ! isset( $term->term_id ) ) {
		return 0;
	}
	
	return absint( $term->term_id );
}

/**
 * Build WC_Product_Query arguments with filters
 *
 * @param array $params                Filter parameters from URL.
 * @param array $attributes            Block attributes.
 * @param array $attribute_taxonomies Optional. List of attribute taxonomy slugs. When provided, tax_query is built from $params['attributes']; otherwise legacy colors/sizes are used.
 * @return array Query arguments for WC_Product_Query.
 */
function etheme_build_query_args( $params, $attributes, $attribute_taxonomies = array() ) {
	$orderby = $params['orderby'] ?: $attributes['defaultOrderBy'];
	$order   = $params['order'] ?: $attributes['defaultOrder'];
	$current_category_id = etheme_get_current_product_category_id();
	if ( empty( $params['categories'] ) && $current_category_id ) {
		$params['categories'] = array( $current_category_id );
	}

	$args = array(
		'status'     => 'publish',
		'limit'      => $attributes['perPage'],
		'page'       => $params['paged'],
		'paginate'   => true,
		'visibility' => 'catalog',
	);

	// Search
	if ( ! empty( $params['search'] ) ) {
		$args['s'] = $params['search'];
	}

	// Categories filter - optimized batch validation
	if ( ! empty( $params['categories'] ) ) {
		$category_ids     = array_map( 'absint', $params['categories'] );
		$valid_categories = get_terms( array(
			'taxonomy'   => 'product_cat',
			'include'    => $category_ids,
			'fields'     => 'ids',
			'hide_empty' => false,
		) );

		if ( ! empty( $valid_categories ) && ! is_wp_error( $valid_categories ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'term_id',
					'terms'    => $valid_categories,
					'operator' => 'IN',
				),
			);
		}
	}

	// Price range filter
	if ( $params['min_price'] > 0 || $params['max_price'] > 0 ) {
		$args['price_range'] = array(
			$params['min_price'] > 0 ? $params['min_price'] : 0,
			$params['max_price'] > 0 ? $params['max_price'] : PHP_INT_MAX,
		);
	}

	// Attribute filters (dynamic or legacy)
	$tax_list = ! empty( $attribute_taxonomies ) ? $attribute_taxonomies : etheme_get_archive_attribute_taxonomies();
	if ( ! empty( $tax_list ) && ! empty( $params['attributes'] ) && is_array( $params['attributes'] ) ) {
		foreach ( $tax_list as $tax ) {
			if ( ! empty( $params['attributes'][ $tax ] ) && is_array( $params['attributes'][ $tax ] ) ) {
				if ( ! isset( $args['tax_query'] ) ) {
					$args['tax_query'] = array();
				}
				// For pa_color: also match pa_color2 so bicolor products surface under either color.
				if ( 'pa_color' === $tax && taxonomy_exists( 'pa_color2' ) ) {
					$args['tax_query'][] = array(
						'relation' => 'OR',
						array(
							'taxonomy' => 'pa_color',
							'field'    => 'slug',
							'terms'    => $params['attributes'][ $tax ],
							'operator' => 'IN',
						),
						array(
							'taxonomy' => 'pa_color2',
							'field'    => 'slug',
							'terms'    => $params['attributes'][ $tax ],
							'operator' => 'IN',
						),
					);
				} else {
					$args['tax_query'][] = array(
						'taxonomy' => $tax,
						'field'    => 'slug',
						'terms'    => $params['attributes'][ $tax ],
						'operator' => 'IN',
					);
				}
			}
		}
	} else {
		// Legacy: colors and sizes
		if ( ! empty( $params['colors'] ) ) {
			if ( ! isset( $args['tax_query'] ) ) {
				$args['tax_query'] = array();
			}
			$args['tax_query'][] = array(
				'taxonomy' => 'pa_color',
				'field'    => 'slug',
				'terms'    => $params['colors'],
				'operator' => 'IN',
			);
		}
		if ( ! empty( $params['sizes'] ) ) {
			if ( ! isset( $args['tax_query'] ) ) {
				$args['tax_query'] = array();
			}
			$args['tax_query'][] = array(
				'taxonomy' => 'pa_size',
				'field'    => 'slug',
				'terms'    => $params['sizes'],
				'operator' => 'IN',
			);
		}
	}

	// Set tax_query relation if multiple tax queries
	if ( isset( $args['tax_query'] ) && count( $args['tax_query'] ) > 1 ) {
		$args['tax_query']['relation'] = 'AND';
	}
	
	// On sale filter
	if ( $params['on_sale'] ) {
		$sale_ids = wc_get_product_ids_on_sale();
		if ( ! empty( $sale_ids ) ) {
			$args['include'] = $sale_ids;
		} else {
			// No products on sale, return empty result
			$args['include'] = array( 0 );
		}
	}
	
	// Sorting
	switch ( $orderby ) {
		case 'price':
			$args['orderby'] = 'meta_value_num';
			$args['meta_key'] = '_price';
			$args['order'] = $order;
			break;
		case 'popularity':
			$args['orderby'] = 'popularity';
			$args['order'] = $order;
			break;
		case 'date':
		default:
			$args['orderby'] = 'date';
			$args['order'] = $order;
			break;
	}
	
	return $args;
}

/**
 * Get sorting options
 */
function etheme_get_sorting_options() {
	return array(
		array( 'value' => 'date-desc', 'label' => __( 'Más nuevos', 'etheme' ) ),
		array( 'value' => 'date-asc', 'label' => __( 'Más antiguos', 'etheme' ) ),
		array( 'value' => 'price-asc', 'label' => __( 'Precio: menor a mayor', 'etheme' ) ),
		array( 'value' => 'price-desc', 'label' => __( 'Precio: mayor a menor', 'etheme' ) ),
		array( 'value' => 'popularity-desc', 'label' => __( 'Más populares', 'etheme' ) ),
		array( 'value' => 'popularity-asc', 'label' => __( 'Menos populares', 'etheme' ) ),
	);
}

/**
 * Get product categories
 */
function etheme_get_product_categories() {
	static $cached_categories = null;
	
	if ( null !== $cached_categories ) {
		return $cached_categories;
	}
	
	$cached_categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );
	
	return $cached_categories;
}

/**
 * Get term depth based on ancestors count.
 */
function etheme_get_term_depth( $term_id, $taxonomy ) {
	$ancestors = get_ancestors( $term_id, $taxonomy );
	return is_array( $ancestors ) ? count( $ancestors ) : 0;
}

/**
 * Get deepest term ids from a term list.
 */
function etheme_get_deepest_term_ids( $terms, $taxonomy ) {
	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return array();
	}
	
	$deepest_ids = array();
	$max_depth = -1;
	foreach ( $terms as $term ) {
		$depth = etheme_get_term_depth( $term->term_id, $taxonomy );
		if ( $depth > $max_depth ) {
			$max_depth = $depth;
			$deepest_ids = array( $term->term_id );
		} elseif ( $depth === $max_depth ) {
			$deepest_ids[] = $term->term_id;
		}
	}
	
	return array_values( array_unique( $deepest_ids ) );
}

/**
 * Get deepest product category ids for a product.
 */
function etheme_get_product_deepest_category_ids( $product_id ) {
	$terms = get_the_terms( $product_id, 'product_cat' );
	return etheme_get_deepest_term_ids( $terms, 'product_cat' );
}

/**
 * Get closest product categories from a product list.
 */
function etheme_get_search_result_categories_from_products( $products ) {
	if ( empty( $products ) ) {
		return array();
	}
	
	$category_ids = array();
	foreach ( $products as $product ) {
		if ( $product instanceof WC_Product ) {
			$category_ids = array_merge(
				$category_ids,
				etheme_get_product_deepest_category_ids( $product->get_id() )
			);
		}
	}
	
	$category_ids = array_values( array_unique( array_map( 'absint', $category_ids ) ) );
	if ( empty( $category_ids ) ) {
		return array();
	}
	
	return get_terms( array(
		'taxonomy'   => 'product_cat',
		'include'    => $category_ids,
		'hide_empty' => false,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );
}

/**
 * Get top-level product categories (parent = 0)
 */
function etheme_get_parent_product_categories() {
	$categories = etheme_get_product_categories();
	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return $categories;
	}
	
	$parents = array();
	foreach ( $categories as $category ) {
		if ( 0 === absint( $category->parent ) ) {
			$parents[] = $category;
		}
	}
	
	return $parents;
}

/**
 * Get child product categories (parent != 0)
 */
function etheme_get_child_product_categories() {
	$categories = etheme_get_product_categories();
	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return $categories;
	}
	
	$children = array();
	foreach ( $categories as $category ) {
		if ( 0 !== absint( $category->parent ) ) {
			$children[] = $category;
		}
	}
	
	return $children;
}

/**
 * Get child categories for a specific parent term id.
 */
function etheme_get_child_categories_by_parent( $parent_id ) {
	static $cached_children = array();
	$parent_id = absint( $parent_id );
	if ( $parent_id <= 0 ) {
		return array();
	}
	
	if ( isset( $cached_children[ $parent_id ] ) ) {
		return $cached_children[ $parent_id ];
	}
	
	$cached_children[ $parent_id ] = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => $parent_id,
		'orderby'    => 'name',
		'order'      => 'ASC',
	) );
	
	return $cached_children[ $parent_id ];
}

/**
 * Resolve active top-level category id from current context or filters.
 */
function etheme_get_active_parent_category_id( $filter_params ) {
	if ( is_product_category() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->term_id ) ) {
			return $term->parent ? absint( $term->parent ) : absint( $term->term_id );
		}
	}
	
	if ( ! empty( $filter_params['categories'] ) ) {
		$first_category_id = absint( $filter_params['categories'][0] );
		if ( $first_category_id ) {
			$term = get_term( $first_category_id, 'product_cat' );
			if ( $term && ! is_wp_error( $term ) ) {
				return $term->parent ? absint( $term->parent ) : absint( $term->term_id );
			}
		}
	}
	
	return 0;
}

/**
 * Resolve active category id from current context or filters.
 */
function etheme_get_active_category_id( $filter_params ) {
	if ( is_product_category() ) {
		$term = get_queried_object();
		if ( $term && isset( $term->term_id ) ) {
			return absint( $term->term_id );
		}
	}
	
	if ( ! empty( $filter_params['categories'] ) ) {
		return absint( $filter_params['categories'][0] );
	}
	
	return 0;
}

/**
 * Check if any filters are active
 *
 * @param array $params Filter parameters.
 * @return bool
 */
function etheme_has_active_filters( $params ) {
	$has_attribute_filters = ! empty( $params['attributes'] ) && is_array( $params['attributes'] )
		&& array_filter( $params['attributes'] ) !== array();

	return ! empty( $params['categories'] )
		|| $params['min_price'] > 0
		|| $params['max_price'] > 0
		|| ! empty( $params['colors'] )
		|| ! empty( $params['sizes'] )
		|| $has_attribute_filters
		|| $params['on_sale'];
}

/**
 * Get price range (min and max) scoped to the given product IDs when provided.
 *
 * @param int[] $object_ids Optional. When non-empty, restricts to these product IDs.
 * @return array { min: float, max: float }
 */
function etheme_get_price_range( $object_ids = array() ) {
	static $cache = array();

	$key = empty( $object_ids ) ? '__all__' : md5( implode( ',', $object_ids ) );
	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	global $wpdb;

	if ( ! empty( $object_ids ) ) {
		$ids_in    = implode( ',', array_map( 'absint', $object_ids ) );
		$min_price = $wpdb->get_var( "
			SELECT MIN(meta_value + 0)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_price'
			AND meta_value != ''
			AND post_id IN ({$ids_in})
		" );
		$max_price = $wpdb->get_var( "
			SELECT MAX(meta_value + 0)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_price'
			AND meta_value != ''
			AND post_id IN ({$ids_in})
		" );
	} else {
		$min_price = $wpdb->get_var( "
			SELECT MIN(meta_value + 0)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_price'
			AND meta_value != ''
		" );
		$max_price = $wpdb->get_var( "
			SELECT MAX(meta_value + 0)
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_price'
			AND meta_value != ''
		" );
	}

	$cache[ $key ] = array(
		'min' => floatval( $min_price ?: 0 ),
		'max' => floatval( $max_price ?: 1000 ),
	);

	return $cache[ $key ];
}

/**
 * Get product colors from attributes (legacy). Uses default-colors.json when term has no color; names translatable.
 */
function etheme_get_product_colors() {
	static $cached_colors = null;

	if ( null !== $cached_colors ) {
		return $cached_colors;
	}

	$colors = array();
	if ( taxonomy_exists( 'pa_color' ) ) {
		$terms      = get_terms( array(
			'taxonomy'   => 'pa_color',
			'hide_empty' => true,
		) );
		$color_map  = etheme_get_default_color_map();

		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$hex = get_term_meta( $term->term_id, 'product_attribute_color', true );
				if ( ! $hex ) {
					$hex = isset( $color_map[ strtolower( $term->slug ) ] ) ? $color_map[ strtolower( $term->slug ) ] : '#CCCCCC';
				}
				$colors[] = array(
					'slug' => $term->slug,
					'name' => $term->name,
					'hex'  => $hex,
				);
			}
		}
	}

	$cached_colors = $colors;
	return $cached_colors;
}

/**
 * Get product sizes from attributes
 */
function etheme_get_product_sizes() {
	static $cached_sizes = null;
	
	if ( null !== $cached_sizes ) {
		return $cached_sizes;
	}
	
	$sizes = array();
	
	// Check if size attribute exists
	if ( taxonomy_exists( 'pa_size' ) ) {
		$terms = get_terms( array(
			'taxonomy'   => 'pa_size',
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		) );
		
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$sizes[] = array(
					'slug' => $term->slug,
					'name' => $term->name,
				);
			}
		}
	}
	
	// If no sizes found, return default sizes
	if ( empty( $sizes ) ) {
		$sizes = array(
			array( 'slug' => '30', 'name' => '30' ),
			array( 'slug' => '40', 'name' => '40' ),
			array( 'slug' => '41', 'name' => '41' ),
			array( 'slug' => '42', 'name' => '42' ),
			array( 'slug' => '43', 'name' => '43' ),
			array( 'slug' => '44', 'name' => '44' ),
			array( 'slug' => '45', 'name' => '45' ),
			array( 'slug' => '46', 'name' => '46' ),
		);
	}
	
	$cached_sizes = $sizes;
	return $cached_sizes;
}

/**
 * Get product attribute terms for a given taxonomy (slug and name).
 *
 * @param string $taxonomy   Taxonomy slug (e.g. pa_size).
 * @param int[]  $object_ids Optional. When non-empty, restricts to products with these IDs.
 * @return array Array of arrays with 'slug' and 'name'.
 */
function etheme_get_product_attribute_terms( $taxonomy, $object_ids = array() ) {
	if ( ! taxonomy_exists( $taxonomy ) ) {
		return array();
	}
	$args = array(
		'taxonomy'   => $taxonomy,
		'hide_empty' => true,
		'orderby'    => 'name',
		'order'      => 'ASC',
	);
	if ( ! empty( $object_ids ) ) {
		$args['object_ids'] = $object_ids;
	}
	$terms = get_terms( $args );
	if ( is_wp_error( $terms ) || empty( $terms ) ) {
		return array();
	}
	$out = array();
	foreach ( $terms as $term ) {
		$out[] = array(
			'slug' => $term->slug,
			'name' => $term->name,
		);
	}
	return $out;
}

/**
 * Load default colors from JSON (slug, hex, name). Names are translation-ready (use __() for Spanish/English).
 *
 * @return array Array of arrays with 'slug', 'hex', 'name' (name translated via __()).
 */
function etheme_get_default_colors() {
	static $cached = null;
	if ( $cached !== null ) {
		return $cached;
	}
	$path = get_template_directory() . '/src/archive-product/includes/default-colors.json';
	if ( ! file_exists( $path ) || ! is_readable( $path ) ) {
		$cached = array(
			array( 'slug' => 'white', 'name' => __( 'White', 'etheme' ), 'hex' => '#FFFFFF' ),
			array( 'slug' => 'black', 'name' => __( 'Black', 'etheme' ), 'hex' => '#000000' ),
			array( 'slug' => 'red', 'name' => __( 'Red', 'etheme' ), 'hex' => '#EF4444' ),
			array( 'slug' => 'blue', 'name' => __( 'Blue', 'etheme' ), 'hex' => '#3B82F6' ),
			array( 'slug' => 'green', 'name' => __( 'Green', 'etheme' ), 'hex' => '#22C55E' ),
			array( 'slug' => 'gray', 'name' => __( 'Gray', 'etheme' ), 'hex' => '#6B7280' ),
		);
		return $cached;
	}
	$json = file_get_contents( $path );
	$list = json_decode( $json, true );
	if ( ! is_array( $list ) ) {
		$cached = array(
			array( 'slug' => 'white', 'name' => __( 'White', 'etheme' ), 'hex' => '#FFFFFF' ),
			array( 'slug' => 'black', 'name' => __( 'Black', 'etheme' ), 'hex' => '#000000' ),
		);
		return $cached;
	}
	$out = array();
	foreach ( $list as $item ) {
		if ( empty( $item['slug'] ) || empty( $item['hex'] ) ) {
			continue;
		}
		$name = isset( $item['name'] ) ? $item['name'] : $item['slug'];
		$out[] = array(
			'slug' => $item['slug'],
			'hex'  => $item['hex'],
			'name' => __( $name, 'etheme' ),
		);
	}
	if ( empty( $out ) ) {
		$out = array(
			array( 'slug' => 'white', 'name' => __( 'White', 'etheme' ), 'hex' => '#FFFFFF' ),
			array( 'slug' => 'black', 'name' => __( 'Black', 'etheme' ), 'hex' => '#000000' ),
		);
	}
	$cached = $out;
	return $cached;
}

/**
 * Get slug => hex map from default colors (for term meta fallback when user has not set color picker).
 *
 * @return array
 */
function etheme_get_default_color_map() {
	$list = etheme_get_default_colors();
	$map  = array();
	foreach ( $list as $item ) {
		$map[ strtolower( $item['slug'] ) ] = $item['hex'];
	}
	return $map;
}

/**
 * Get product colors (or any color-style attribute) for a taxonomy with optional term overrides (hex, name for hover).
 * Uses default-colors.json when term has no color picker value; names are translatable (Spanish/English via __()).
 *
 * @param string $taxonomy       Taxonomy slug (e.g. pa_color).
 * @param array  $term_overrides Optional. Keyed by term slug, values are array( 'hex' => '', 'name' => '' ). Overrides term meta / defaults.
 * @param array  $object_ids     Optional. When non-empty, only returns terms assigned to these product IDs.
 * @return array Array of arrays with 'slug', 'name', 'hex'.
 */
function etheme_get_product_colors_for_taxonomy( $taxonomy, $term_overrides = array(), $object_ids = array() ) {
	$term_overrides = is_array( $term_overrides ) ? $term_overrides : array();
	$color_map     = etheme_get_default_color_map();
	$colors        = array();
	if ( taxonomy_exists( $taxonomy ) ) {
		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
		);
		if ( ! empty( $object_ids ) ) {
			$args['object_ids'] = $object_ids;
		}
		$terms = get_terms( $args );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			foreach ( $terms as $term ) {
				$override = isset( $term_overrides[ $term->slug ] ) && is_array( $term_overrides[ $term->slug ] )
					? $term_overrides[ $term->slug ]
					: array();
				$hex = isset( $override['hex'] ) && $override['hex']
					? $override['hex']
					: get_term_meta( $term->term_id, 'product_attribute_color', true );
				if ( ! $hex ) {
					$hex = isset( $color_map[ strtolower( $term->slug ) ] ) ? $color_map[ strtolower( $term->slug ) ] : '#CCCCCC';
				}
				$name = isset( $override['name'] ) && $override['name'] !== ''
					? $override['name']
					: $term->name;
				$colors[] = array(
					'slug' => $term->slug,
					'name' => $name,
					'hex'  => $hex,
				);
			}
		}
	}
	return $colors;
}

/**
 * Get product IDs scoped to the active category + search context.
 * Returns empty array when neither category nor search is active (= no restriction).
 *
 * @param array $filter_params
 * @return int[]
 */
function etheme_get_product_ids_in_context( $filter_params = array() ) {
	$category_ids = array();
	$current_id   = etheme_get_current_product_category_id();
	if ( $current_id ) {
		$category_ids[] = $current_id;
	}
	if ( ! empty( $filter_params['categories'] ) ) {
		$category_ids = array_values( array_unique( array_merge( $category_ids, array_map( 'absint', $filter_params['categories'] ) ) ) );
	}
	$search = ! empty( $filter_params['search'] ) ? $filter_params['search'] : '';

	if ( empty( $category_ids ) && '' === $search ) {
		return array();
	}

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	);
	if ( ! empty( $category_ids ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $category_ids,
				'operator' => 'IN',
			),
		);
	}
	if ( '' !== $search ) {
		$args['s'] = $search;
	}

	$query = new WP_Query( $args );
	return $query->posts ?: array();
}

/**
 * Get colors from pa_color and pa_color2 merged and deduplicated by slug.
 * Scoped to the active category context when filter_params are provided.
 *
 * @param array $term_overrides Optional overrides keyed by slug.
 * @param array $filter_params  Optional filter params to scope by active category.
 * @return array
 */
function etheme_get_product_colors_combined( $term_overrides = array(), $filter_params = array() ) {
	$object_ids = etheme_get_product_ids_in_context( $filter_params );
	$colors     = etheme_get_product_colors_for_taxonomy( 'pa_color', $term_overrides, $object_ids );
	$existing   = array_column( $colors, 'slug' );
	if ( taxonomy_exists( 'pa_color2' ) ) {
		foreach ( etheme_get_product_colors_for_taxonomy( 'pa_color2', $term_overrides, $object_ids ) as $c ) {
			if ( ! in_array( $c['slug'], $existing, true ) ) {
				$colors[]   = $c;
				$existing[] = $c['slug'];
			}
		}
	}
	return $colors;
}

/**
 * Get current page URL
 */
function etheme_get_current_url() {
	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}

/**
 * Build clear filters URL
 */
function etheme_get_clear_filters_url( $keep_search = false, $keep_sort = false, $params = array() ) {
	$query_args = etheme_build_url_query_args( $params, array(
		'search'  => $keep_search,
		'sort'    => $keep_sort,
		'filters' => false,
	) );
	
	$base_url = etheme_get_current_url();
	return ! empty( $query_args ) ? add_query_arg( $query_args, $base_url ) : $base_url;
}

/**
 * Build query args array from filter params for URLs
 *
 * @param array $filter_params Filter parameters array.
 * @param array $include       Array of flags indicating what to include:
 *                            - 'post_type' (default: true)
 *                            - 'product_cat' (default: true)
 *                            - 'search' (default: true)
 *                            - 'sort' (default: true)
 *                            - 'filters' (default: true)
 * @return array Query arguments array.
 */
function etheme_build_url_query_args( $filter_params, $include = array() ) {
	$defaults = array(
		'post_type' => true,
		'product_cat' => true,
		'search'    => true,
		'sort'      => true,
		'filters'   => true,
	);
	$include = wp_parse_args( $include, $defaults );
	
	$query_args = array();
	
	if ( $include['post_type'] && isset( $_GET['post_type'] ) ) {
		$query_args['post_type'] = sanitize_text_field( $_GET['post_type'] );
	}
	if ( $include['product_cat'] && isset( $_GET['product_cat'] ) ) {
		$query_args['product_cat'] = sanitize_text_field( $_GET['product_cat'] );
	}
	if ( $include['search'] && ! empty( $filter_params['search'] ) ) {
		$query_args['s'] = $filter_params['search'];
	}
	if ( $include['sort'] && $filter_params['orderby'] && $filter_params['order'] ) {
		$query_args['orderby'] = $filter_params['orderby'] . '-' . $filter_params['order'];
	}
	if ( $include['filters'] ) {
		if ( ! empty( $filter_params['categories'] ) ) {
			$query_args['filter_categories'] = $filter_params['categories'];
		}
		if ( $filter_params['min_price'] > 0 ) {
			$query_args['min_price'] = $filter_params['min_price'];
		}
		if ( $filter_params['max_price'] > 0 ) {
			$query_args['max_price'] = $filter_params['max_price'];
		}
		$attr_taxonomies = etheme_get_archive_attribute_taxonomies();
		if ( ! empty( $attr_taxonomies ) && ! empty( $filter_params['attributes'] ) && is_array( $filter_params['attributes'] ) ) {
			foreach ( $attr_taxonomies as $tax ) {
				if ( ! empty( $filter_params['attributes'][ $tax ] ) ) {
					$query_args[ 'filter_' . $tax ] = $filter_params['attributes'][ $tax ];
				}
			}
		} else {
			if ( ! empty( $filter_params['colors'] ) ) {
				$query_args['filter_colors'] = $filter_params['colors'];
			}
			if ( ! empty( $filter_params['sizes'] ) ) {
				$query_args['filter_sizes'] = $filter_params['sizes'];
			}
		}
		if ( $filter_params['on_sale'] ) {
			$query_args['on_sale'] = '1';
		}
	}

	return $query_args;
}

/**
 * Render hidden inputs to preserve filter parameters
 *
 * @param array $filter_params Filter parameters array.
 * @param array $preserve      Array of flags indicating what to preserve:
 *                            - 'post_type' (default: true)
 *                            - 'product_cat' (default: true)
 *                            - 'search' (default: false)
 *                            - 'sort' (default: false)
 *                            - 'filters' (default: false)
 * @return void
 */
function etheme_render_preserved_params( $filter_params, $preserve = array() ) {
	$defaults = array(
		'post_type' => true,
		'product_cat' => true,
		'search'    => false,
		'sort'      => false,
		'filters'   => false,
	);
	$preserve = wp_parse_args( $preserve, $defaults );
	
	if ( $preserve['post_type'] && isset( $_GET['post_type'] ) ) {
		echo '<input type="hidden" name="post_type" value="' . esc_attr( sanitize_text_field( $_GET['post_type'] ) ) . '">';
	}
	if ( $preserve['product_cat'] && isset( $_GET['product_cat'] ) ) {
		echo '<input type="hidden" name="product_cat" value="' . esc_attr( sanitize_text_field( $_GET['product_cat'] ) ) . '">';
	}
	
	if ( $preserve['search'] && ! empty( $filter_params['search'] ) ) {
		echo '<input type="hidden" name="s" value="' . esc_attr( $filter_params['search'] ) . '">';
	}
	
	if ( $preserve['sort'] && ! empty( $filter_params['orderby'] ) && ! empty( $filter_params['order'] ) ) {
		echo '<input type="hidden" name="orderby" value="' . esc_attr( $filter_params['orderby'] . '-' . $filter_params['order'] ) . '">';
	}
	
	if ( $preserve['filters'] ) {
		if ( ! empty( $filter_params['categories'] ) ) {
			foreach ( $filter_params['categories'] as $cat_id ) {
				echo '<input type="hidden" name="filter_categories[]" value="' . esc_attr( $cat_id ) . '">';
			}
		}
		if ( $filter_params['min_price'] > 0 ) {
			echo '<input type="hidden" name="min_price" value="' . esc_attr( $filter_params['min_price'] ) . '">';
		}
		if ( $filter_params['max_price'] > 0 ) {
			echo '<input type="hidden" name="max_price" value="' . esc_attr( $filter_params['max_price'] ) . '">';
		}
		$attr_taxonomies = etheme_get_archive_attribute_taxonomies();
		if ( ! empty( $attr_taxonomies ) && ! empty( $filter_params['attributes'] ) && is_array( $filter_params['attributes'] ) ) {
			foreach ( $attr_taxonomies as $tax ) {
				if ( ! empty( $filter_params['attributes'][ $tax ] ) ) {
					foreach ( $filter_params['attributes'][ $tax ] as $slug ) {
						echo '<input type="hidden" name="filter_' . esc_attr( $tax ) . '[]" value="' . esc_attr( $slug ) . '">';
					}
				}
			}
		} else {
			if ( ! empty( $filter_params['colors'] ) ) {
				foreach ( $filter_params['colors'] as $color ) {
					echo '<input type="hidden" name="filter_colors[]" value="' . esc_attr( $color ) . '">';
				}
			}
			if ( ! empty( $filter_params['sizes'] ) ) {
				foreach ( $filter_params['sizes'] as $size ) {
					echo '<input type="hidden" name="filter_sizes[]" value="' . esc_attr( $size ) . '">';
				}
			}
		}
		if ( $filter_params['on_sale'] ) {
			echo '<input type="hidden" name="on_sale" value="1">';
		}
	}
}

