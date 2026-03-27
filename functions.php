<?php
function myblocksinit() {
    register_block_type( __DIR__ . '/build/0_block' );
    register_block_type( __DIR__ . '/build/taxonomy-product_cat/index' );
    register_block_type( __DIR__ . '/build/single-product/index' );
    register_block_type( __DIR__ . '/build/front-page/index' );
    register_block_type( __DIR__ . '/build/core/navbar' );
    register_block_type( __DIR__ . '/build/core/footer' );
    register_block_type( __DIR__ . '/build/core/header' );
    register_block_type( __DIR__ . '/build/archive-product/index' );
    register_block_type( __DIR__ . '/build/page-cart/index' );
    register_block_type( __DIR__ . '/build/page-checkout/index' );
    register_block_type( __DIR__ . '/build/page/index' );
    register_block_type( __DIR__ . '/build/page-posteos/index' );
}
add_action( 'init', 'myblocksinit' );

// Include Cart AJAX handlers
require_once __DIR__ . '/src/page-cart/includes/ajax-handlers.php';

// Include Front Page CPT and AJAX handlers
require_once __DIR__ . '/src/front-page/includes/home-reviews.cpt-review.php';
require_once __DIR__ . '/src/front-page/includes/social-post.cpt.php';
require_once __DIR__ . '/src/front-page/includes/social-post.metabox.php';
require_once __DIR__ . '/src/front-page/includes/home-newsletter.ajax-handlers.php';

// Include Posteos AJAX handlers (load-more for /posteos page)
require_once __DIR__ . '/src/page-posteos/includes/ajax-handlers.php';

function etheme_enqueue_front_page_styles() {
	// Enqueue front-page CSS en la home y también en la página /posteos.
	$posteos_page = get_page_by_path( 'posteos' );
	$is_posteos   = ( $posteos_page instanceof WP_Post ) ? is_page( $posteos_page->ID ) : false;
	if ( ! is_front_page() && ! $is_posteos ) {
		return;
	}
	$path = get_template_directory() . '/build/front-page/index/style-index.css';
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_style(
		'etheme-front-page-index',
		get_theme_file_uri( '/build/front-page/index/style-index.css' ),
		array(),
		filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_front_page_styles' );

function test_theme_load_assets() {
    $version = filemtime(get_template_directory() . '/build/index.css');
    wp_enqueue_script('test-theme-main-js', get_theme_file_uri('/build/index.js'), array('wp-element'), $version, true);
    wp_enqueue_style('test-theme-main-css', get_theme_file_uri('/build/index.css'), array(), $version);
	wp_enqueue_style('etheme-style', get_stylesheet_uri(), array(), filemtime(get_template_directory() . '/style.css'));


    // Load WooCommerce styles if WooCommerce is active
    if ( class_exists( 'woocommerce' ) ) {
        $woo_css_path = get_template_directory() . '/assets/css/woocommerce.css';
        if ( file_exists( $woo_css_path ) ) {
            $woo_version = filemtime( $woo_css_path );
            wp_enqueue_style( 'etheme-woocommerce-css', get_theme_file_uri( '/assets/css/woocommerce.css' ), array(), $woo_version );
        }
    }
    // wp_enqueue_style('wp-block-library'); 
}
add_action('wp_enqueue_scripts', 'test_theme_load_assets');

function test_theme_load_editor_assets() {
    $version = filemtime(get_template_directory() . '/build/index.css');
    wp_enqueue_style('test-theme-editor-css', get_theme_file_uri('/build/index.css'), array(), $version);
}
add_action('enqueue_block_editor_assets', 'test_theme_load_editor_assets');

function test_theme_add_support() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
}
add_action('after_setup_theme', 'test_theme_add_support');

add_action( 'after_setup_theme', function() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
});

/**
 * Do not auto-login after registration. User must log in with the password
 * they set via the email link (WooCommerce "Send password setup link").
 */
add_filter( 'woocommerce_registration_auth_new_customer', '__return_false' );

/**
 * Expand search terms for product searches.
 */
add_filter( 'posts_search', 'etheme_expand_product_search_terms', 10, 2 );
function etheme_expand_product_search_terms( $search, $wp_query ) {
	if ( is_admin() || ! $wp_query->is_search() ) {
		return $search;
	}
	
	$post_type = $wp_query->get( 'post_type' );
	if ( is_array( $post_type ) && ! in_array( 'product', $post_type, true ) ) {
		return $search;
	}
	if ( is_string( $post_type ) && 'product' !== $post_type ) {
		return $search;
	}
	
	$term = sanitize_text_field( $wp_query->get( 's' ) );
	if ( '' === $term ) {
		return $search;
	}
	
	$variants = etheme_get_search_variants( $term );
	if ( empty( $variants ) ) {
		return $search;
	}
	
	global $wpdb;
	$extra_sql = etheme_build_search_variant_sql( $variants, $wpdb->posts );
	if ( '' === $extra_sql ) {
		return $search;
	}
	
	return preg_replace( '/\)\s*\)$/', ' OR ' . $extra_sql . '))', $search, 1 );
}

/**
 * Build search variants for common separators and typos.
 */
function etheme_get_search_variants( $term ) {
	$variants = array();
	$normalized = preg_replace( '/[_\-\s]+/', ' ', $term );
	$compact = str_replace( ' ', '', $normalized );
	$hyphenated = str_replace( ' ', '-', $normalized );
	$spaced = $normalized;
	$underscored = str_replace( ' ', '_', $normalized );
	
	foreach ( array( $compact, $hyphenated, $spaced, $underscored ) as $candidate ) {
		if ( $candidate && $candidate !== $term ) {
			$variants[] = $candidate;
		}
	}
	
	$length = strlen( $term );
	if ( $length >= 4 && $length <= 8 && false === strpbrk( $term, '-_ ' ) ) {
		$variants[] = substr( $term, 0, 1 ) . '-' . substr( $term, 1 );
		$variants[] = substr( $term, 0, 1 ) . ' ' . substr( $term, 1 );
	}
	
	return array_values( array_unique( $variants ) );
}

/**
 * Build SQL fragment for search variants.
 */
function etheme_build_search_variant_sql( $variants, $posts_table ) {
	if ( empty( $variants ) ) {
		return '';
	}
	
	global $wpdb;
	$like_sql = array();
	foreach ( $variants as $variant ) {
		$like = $wpdb->prepare( '%s', '%' . $variant . '%' );
		$like_sql[] = "{$posts_table}.post_title LIKE {$like}";
	}
	
	$soundex = $wpdb->prepare( '%s', reset( $variants ) );
	$like_sql[] = "SOUNDEX({$posts_table}.post_title) = SOUNDEX({$soundex})";
	
	return implode( ' OR ', $like_sql );
}

/**
 * Handle price range filtering for WooCommerce product queries
 * This hook allows wc_get_products() to filter by price range correctly
 */
/**
 * Prevent 404 on paginated product taxonomy URLs (e.g. ?product_cat=clothing&paged=2).
 * The theme uses wc_get_products() for the product list, not the main query, so WordPress
 * would otherwise 404 when the main query has no posts on that page number.
 */
add_filter( 'pre_handle_404', 'etheme_prevent_404_product_taxonomy_paged', 10, 2 );
function etheme_prevent_404_product_taxonomy_paged( $preempt, $wp_query ) {
	if ( ! $wp_query->is_main_query() ) {
		return $preempt;
	}
	if ( ! function_exists( 'is_product_category' ) || ! function_exists( 'is_product_tag' ) ) {
		return $preempt;
	}
	if ( ( is_product_category() || is_product_tag() ) && $wp_query->is_paged() ) {
		return true;
	}
	return $preempt;
}

add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'etheme_handle_price_range_query_var', 10, 2 );
function etheme_handle_price_range_query_var( $query, $query_vars ) {
	if ( ! empty( $query_vars['price_range'] ) && is_array( $query_vars['price_range'] ) && count( $query_vars['price_range'] ) === 2 ) {
		$min_price = floatval( $query_vars['price_range'][0] );
		$max_price = floatval( $query_vars['price_range'][1] );
		
		if ( $min_price > 0 || $max_price < PHP_INT_MAX ) {
			$meta_query = array();
			
			if ( $min_price > 0 ) {
				$meta_query[] = array(
					'key'     => '_price',
					'value'   => $min_price,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				);
			}
			
			if ( $max_price < PHP_INT_MAX ) {
				$meta_query[] = array(
					'key'     => '_price',
					'value'   => $max_price,
					'compare' => '<=',
					'type'    => 'NUMERIC',
				);
			}
			
			if ( ! empty( $meta_query ) ) {
				if ( count( $meta_query ) > 1 ) {
					$meta_query['relation'] = 'AND';
				}
				$query['meta_query'] = isset( $query['meta_query'] ) ? array_merge( $query['meta_query'], $meta_query ) : $meta_query;
			}
		}
	}
	return $query;
}

/**
 * Redirect to a specific page after "Comprar ahora".
 *
 * The buy-now button submits the current single-product form but with a query
 * param `bakka_buy_now=1` (via `formaction`). WooCommerce triggers the redirect
 * via `woocommerce_add_to_cart_redirect` after the item is added to the cart.
 */
add_filter(
	'woocommerce_add_to_cart_redirect',
	function ( $url, $adding_to_cart ) {
		$bakka_buy_now = isset( $_GET['bakka_buy_now'] ) ? sanitize_text_field( wp_unslash( $_GET['bakka_buy_now'] ) ) : '';
		if ( '1' === $bakka_buy_now ) {
			return home_url( '/?page_id=57' );
		}

		return $url;
	},
	10,
	2
);
?>
