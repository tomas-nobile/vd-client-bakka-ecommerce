<?php
// front-page-index.
/**
 * Front Page Helper Functions
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/core/includes/social-posts.helpers.php';

/**
 * Get popular products for a given category, ordered by total_sales.
 *
 * Popularity is based on WooCommerce meta key `total_sales`.
 * To extend with other criteria, add cases to the switch below
 * and corresponding options in the block's InspectorControls.
 *
 * @param int    $category_id Term ID of the product category (0 for all).
 * @param int    $limit       Number of products to return.
 * @param string $order_by    Popularity criterion: 'total_sales' (default).
 * @return array Array of WC_Product objects.
 */
function etheme_get_popular_products( $category_id = 0, $limit = 6, $order_by = 'total_sales' ) {
	$args = array(
		'status' => 'publish',
		'limit'  => $limit,
		'return' => 'objects',
	);

	if ( $category_id > 0 ) {
		$args['category'] = array( get_term( $category_id )->slug );
	}

	switch ( $order_by ) {
		case 'total_sales':
		default:
			$args['orderby']  = 'meta_value_num';
			$args['meta_key'] = 'total_sales';
			$args['order']    = 'DESC';
			break;
	}

	return wc_get_products( $args );
}

/**
 * Get product categories for the home page.
 *
 * @param string $mode    'all', 'include', or 'exclude'.
 * @param array  $ids     Category IDs to include or exclude.
 * @return array Array of WP_Term objects.
 */
function etheme_get_home_categories( $mode = 'all', $ids = array() ) {
	$args = array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
		'orderby'    => 'count',
		'order'      => 'DESC',
	);

	if ( 'include' === $mode && ! empty( $ids ) ) {
		$args['include'] = array_map( 'absint', $ids );
		unset( $args['parent'] );
	} elseif ( 'exclude' === $mode && ! empty( $ids ) ) {
		$args['exclude'] = array_map( 'absint', $ids );
	}

	$categories = get_terms( $args );

	return is_wp_error( $categories ) ? array() : $categories;
}

/**
 * Get reviews (CPT etheme_review).
 *
 * @param int    $count    Number of reviews.
 * @param string $order_by 'date' or 'rand'.
 * @return WP_Post[] Array of review posts.
 */
function etheme_get_home_reviews( $count = 6, $order_by = 'date' ) {
	return get_posts( array(
		'post_type'      => 'etheme_review',
		'posts_per_page' => $count,
		'orderby'        => $order_by,
		'order'          => 'DESC',
		'post_status'    => 'publish',
	) );
}

/**
 * Get a review's ACF field with fallback to post meta.
 *
 * @param string $field   Field name (e.g. 'review_client_name').
 * @param int    $post_id Post ID.
 * @return mixed
 */
function etheme_get_review_field( $field, $post_id ) {
	if ( function_exists( 'get_field' ) ) {
		return get_field( $field, $post_id );
	}
	return get_post_meta( $post_id, $field, true );
}

/**
 * Get recent blog posts.
 *
 * @param int   $count      Number of posts.
 * @param array $categories Category IDs to filter by (empty = all).
 * @return WP_Post[] Array of posts.
 */
function etheme_get_home_blog_posts( $count = 3, $categories = array() ) {
	$args = array(
		'post_type'      => 'post',
		'posts_per_page' => $count,
		'orderby'        => 'date',
		'order'          => 'DESC',
		'post_status'    => 'publish',
	);

	if ( ! empty( $categories ) ) {
		$args['category__in'] = array_map( 'absint', $categories );
	}

	return get_posts( $args );
}

// ─── Color dots helpers ───────────────────────────────────────────────────────

/**
 * Return up to 4 CSS color strings for a product's color attribute/variations.
 *
 * Checks the `pa_color` / `pa_colour` taxonomy first; falls back to variation
 * attribute values for variable products. Returns empty array when no colors
 * are found (dots won't be rendered).
 *
 * @param WC_Product $product WooCommerce product object.
 * @return string[] Array of CSS color strings (hex or named colors).
 */
function etheme_get_product_color_dots( $product ) {
	$attr_names = array( 'pa_color', 'pa_colour' );

	foreach ( $attr_names as $attr ) {
		$terms = wc_get_product_terms( $product->get_id(), $attr, array( 'fields' => 'all' ) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			return array_slice( etheme_map_terms_to_colors( $terms ), 0, 4 );
		}
	}

	return array_slice( etheme_get_variation_colors( $product ), 0, 4 );
}

/**
 * Map WP_Term objects to CSS color strings, skipping unresolvable terms.
 *
 * @param WP_Term[] $terms Attribute terms.
 * @return string[] CSS color strings.
 */
function etheme_map_terms_to_colors( $terms ) {
	$colors = array();
	foreach ( $terms as $term ) {
		$color = etheme_resolve_term_color( $term );
		if ( $color ) {
			$colors[] = $color;
		}
	}
	return $colors;
}

/**
 * Resolve a WP_Term to a CSS color string.
 *
 * Accepts hex codes (slug/name) and common English/Spanish color names.
 *
 * @param WP_Term $term Attribute term.
 * @return string|null CSS color string or null if not resolvable.
 */
function etheme_resolve_term_color( $term ) {
	$candidates = array( strtolower( $term->slug ), strtolower( $term->name ) );

	$css_names = array(
		'red', 'blue', 'green', 'black', 'white', 'yellow', 'orange', 'purple',
		'pink', 'brown', 'gray', 'grey', 'beige', 'navy', 'teal', 'coral',
		'cream', 'gold', 'silver', 'maroon', 'olive', 'cyan', 'magenta',
		'rojo', 'azul', 'verde', 'negro', 'blanco', 'amarillo', 'naranja',
		'morado', 'rosa', 'marron', 'gris', 'dorado', 'plateado',
	);

	$css_map = array(
		'rojo' => 'red', 'azul' => 'blue', 'verde' => 'green', 'negro' => 'black',
		'blanco' => 'white', 'amarillo' => 'yellow', 'naranja' => 'orange',
		'morado' => 'purple', 'rosa' => 'pink', 'marron' => '#8B4513',
		'gris' => 'gray', 'dorado' => 'gold', 'plateado' => 'silver',
	);

	foreach ( $candidates as $value ) {
		if ( preg_match( '/^#[0-9a-f]{3,6}$/i', $value ) ) {
			return $value;
		}
		if ( isset( $css_map[ $value ] ) ) {
			return $css_map[ $value ];
		}
		if ( in_array( $value, $css_names, true ) ) {
			return $value;
		}
	}

	return null;
}

/**
 * Extract color values from variation attributes of a variable product.
 *
 * @param WC_Product $product WooCommerce product object.
 * @return string[] Unique CSS color strings from variation attributes.
 */
function etheme_get_variation_colors( $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return array();
	}

	$colors = array();

	foreach ( array_slice( $product->get_available_variations(), 0, 8 ) as $variation ) {
		foreach ( $variation['attributes'] as $key => $value ) {
			if ( false !== strpos( $key, 'color' ) || false !== strpos( $key, 'colour' ) ) {
				$resolved = etheme_resolve_term_color( (object) array( 'slug' => $value, 'name' => $value ) );
				if ( $resolved ) {
					$colors[] = $resolved;
				}
			}
		}
	}

	return array_values( array_unique( $colors ) );
}

/**
 * Return color dots with optional variation image URL/srcset for each color.
 *
 * Used so clicking a color dot can switch the card image to that variation's image.
 * Each item: [ 'color' => css_color, 'image_url' => url|null, 'image_srcset' => srcset|null ].
 * Max 4 items.
 *
 * @param WC_Product $product WooCommerce product object.
 * @return array[] List of { color, image_url, image_srcset }.
 */
function etheme_get_product_color_dots_with_images( $product ) {
	$simple_colors = etheme_get_product_color_dots( $product );
	if ( empty( $simple_colors ) ) {
		return array();
	}

	if ( ! $product->is_type( 'variable' ) ) {
		return array_map( function ( $c ) {
			return array( 'color' => $c, 'image_url' => null, 'image_srcset' => null );
		}, array_slice( $simple_colors, 0, 4 ) );
	}

	$color_attr_key = null;
	foreach ( array( 'pa_color', 'pa_colour' ) as $attr ) {
		if ( taxonomy_exists( $attr ) ) {
			$color_attr_key = 'attribute_' . $attr;
			break;
		}
	}
	if ( ! $color_attr_key ) {
		return array_map( function ( $c ) {
			return array( 'color' => $c, 'image_url' => null, 'image_srcset' => null );
		}, array_slice( $simple_colors, 0, 4 ) );
	}

	$main_image_id = $product->get_image_id();
	$main_url      = $main_image_id ? wp_get_attachment_image_url( $main_image_id, 'woocommerce_thumbnail' ) : '';
	$main_srcset   = $main_image_id ? wp_get_attachment_image_srcset( $main_image_id, 'woocommerce_thumbnail' ) : '';

	$by_color = array();
	foreach ( $product->get_available_variations() as $variation ) {
		$val = isset( $variation['attributes'][ $color_attr_key ] ) ? $variation['attributes'][ $color_attr_key ] : '';
		$css = etheme_resolve_term_color( (object) array( 'slug' => $val, 'name' => $val ) );
		if ( ! $css || isset( $by_color[ $css ] ) ) {
			continue;
		}
		$img = isset( $variation['image'] ) && ! empty( $variation['image']['url'] )
			? $variation['image']
			: null;
		$by_color[ $css ] = array(
			'image_url'   => $img ? $variation['image']['url'] : $main_url,
			'image_srcset' => $img && ! empty( $variation['image']['srcset'] ) ? $variation['image']['srcset'] : $main_srcset,
		);
	}

	$out = array();
	foreach ( array_slice( $simple_colors, 0, 4 ) as $c ) {
		$out[] = array(
			'color'        => $c,
			'image_url'   => isset( $by_color[ $c ] ) ? $by_color[ $c ]['image_url'] : null,
			'image_srcset' => isset( $by_color[ $c ] ) ? $by_color[ $c ]['image_srcset'] : null,
		);
	}
	return $out;
}

// ─── Multimedia + Social Post helpers → moved to src/core/includes/social-posts.helpers.php
// (loaded via require_once at the top of this file; functions remain accessible here)

/**
 * Render star rating as SVG icons.
 *
 * @param int $rating Rating value (1-5).
 * @return string HTML of star icons.
 */
function etheme_render_stars( $rating ) {
	$rating = max( 1, min( 5, absint( $rating ) ) );
	$html   = '';

	for ( $i = 1; $i <= 5; $i++ ) {
		$filled = $i <= $rating ? 'text-yellow-400' : 'text-gray-300';
		$html  .= '<svg class="w-4 h-4 ' . esc_attr( $filled ) . ' inline-block" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>';
	}

	return $html;
}

// ─── Social Post (CPT) helpers → moved to src/core/includes/social-posts.helpers.php ──────────
// All social_post and blog-card related helpers now live in the core helpers file.
