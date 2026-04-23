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
    register_block_type( __DIR__ . '/build/contact/index' );
    register_block_type( __DIR__ . '/build/information-page/index' );
    register_block_type( __DIR__ . '/build/order-received/index' );
}
add_action( 'init', 'myblocksinit' );

// Theme page slugs + auto-create (contacto, posteos, legales).
require_once __DIR__ . '/src/core/includes/theme-pages.php';

// Include Navbar helpers (registers product_cat cache-invalidation hooks early).
require_once __DIR__ . '/src/core/navbar/includes/navbar-helpers.php';

// Include Cart AJAX handlers
require_once __DIR__ . '/src/page-cart/includes/ajax-handlers.php';

// Include Front Page CPT and AJAX handlers
require_once __DIR__ . '/src/front-page/includes/home-reviews.cpt-review.php';
require_once __DIR__ . '/src/front-page/includes/social-post.cpt.php';
require_once __DIR__ . '/src/front-page/includes/social-post.metabox.php';
require_once __DIR__ . '/src/front-page/includes/social-post-category.taxonomy.php';
require_once __DIR__ . '/src/front-page/includes/home-newsletter.ajax-handlers.php';

// Include Posteos AJAX handlers (load-more for /posteos page)
require_once __DIR__ . '/src/page-posteos/includes/ajax-handlers.php';

// Login form hardening: honeypot + IP-based rate limiting.
require_once __DIR__ . '/src/page/includes/login-security.php';

// Transactional emails (spec 22): WC_Email class + filters for from/subject/heading.
require_once __DIR__ . '/src/core/emails/includes/email-hooks.php';

/**
 * Read shared theme config from src/core/config/config.json.
 *
 * @return array
 */
function etheme_get_core_config() {
	static $config = null;
	if ( null !== $config ) {
		return $config;
	}

	$config_path = get_template_directory() . '/src/core/config/config.json';
	if ( ! file_exists( $config_path ) ) {
		$config = array();
		return $config;
	}

	$raw = file_get_contents( $config_path );
	if ( false === $raw ) {
		$config = array();
		return $config;
	}

	$decoded = json_decode( $raw, true );
	$config  = is_array( $decoded ) ? $decoded : array();
	return $config;
}

function etheme_enqueue_front_page_styles() {
	// Enqueue front-page CSS en la home y también en la página de posteos (slug configurable en theme-pages.php).
	$posteos_slug = etheme_get_theme_page_slug( 'posteos' );
	$posteos_page = $posteos_slug ? get_page_by_path( $posteos_slug ) : null;
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

/**
 * Enqueue a block's style-index.css if the file exists.
 *
 * @param string $handle      Style handle.
 * @param string $build_rel   Path relative to theme root, e.g. `/build/page-cart/index/style-index.css`.
 * @param array  $deps        Dependency handles.
 * @return void
 */
function etheme_enqueue_block_style_index( $handle, $build_rel, $deps ) {
	$path = get_template_directory() . $build_rel;
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_style(
		$handle,
		get_theme_file_uri( $build_rel ),
		$deps,
		filemtime( $path )
	);
}

/**
 * Enqueue cart/checkout block CSS on WC pages (FSE template blocks — see README).
 *
 * @return void
 */
function etheme_enqueue_wc_page_template_block_styles() {
	$deps = array( 'test-theme-main-css' );
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		etheme_enqueue_block_style_index( 'etheme-page-cart-index', '/build/page-cart/index/style-index.css', $deps );
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		etheme_enqueue_block_style_index( 'etheme-page-checkout-index', '/build/page-checkout/index/style-index.css', $deps );
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
		etheme_enqueue_block_style_index( 'etheme-order-received-index', '/build/order-received/index/style-index.css', $deps );
	}
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_wc_page_template_block_styles', 20 );

function test_theme_load_assets() {
    $version = filemtime(get_template_directory() . '/build/index.css');
    wp_enqueue_style( 'etheme-google-fonts', 'https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100..900;1,100..900&family=Jost:ital,wght@0,100..900;1,100..900&display=swap', array(), null );
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

/**
 * Register navbar theme location so the client can assign a menu via
 * Appearance → Menus (or the FSE Navigation editor).
 */
add_action( 'after_setup_theme', function() {
	register_nav_menus(
		array(
			'etheme-primary' => __( 'Navegación principal', 'etheme' ),
		)
	);
} );

/**
 * Enqueue navbar block stylesheet on every frontend page.
 * Needed because the block lives in a template part (parts/header.html)
 * and FSE may not auto-enqueue it — see README Frontend CSS Loading Checklist.
 */
function etheme_enqueue_navbar_styles() {
	$build_rel = '/build/core/navbar/style-index.css';
	$path      = get_template_directory() . $build_rel;

	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_style(
		'etheme-navbar',
		get_theme_file_uri( $build_rel ),
		array( 'test-theme-main-css' ),
		filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_navbar_styles', 15 );

/**
 * Enqueue footer block stylesheet on every frontend page.
 * Needed because the block lives in FSE templates and may not be auto-enqueued.
 */
function etheme_enqueue_footer_styles() {
	$build_rel = '/build/core/footer/style-index.css';
	$path      = get_template_directory() . $build_rel;

	if ( ! file_exists( $path ) ) {
		return;
	}

	wp_enqueue_style(
		'etheme-footer',
		get_theme_file_uri( $build_rel ),
		array( 'test-theme-main-css' ),
		filemtime( $path )
	);
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_footer_styles', 15 );

/**
 * Send no-cache headers on WooCommerce pages with user-specific data.
 * Prevents edge/page caches from leaking cart/checkout/account data across sessions.
 */
function etheme_nocache_sensitive_pages() {
	if ( ! function_exists( 'is_cart' ) ) {
		return;
	}
	if ( is_cart() || is_checkout() || is_account_page() ) {
		nocache_headers();
	}
}
add_action( 'template_redirect', 'etheme_nocache_sensitive_pages', 1 );

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
/* =========================================================================
   CHECKOUT — región geográfica y teléfono dividido
   ========================================================================= */

/**
 * Provincias permitidas para avanzar al pago.
 * BA_GBA = Gran Buenos Aires; C = Capital Federal / CABA.
 *
 * @return string[]
 */
function etheme_checkout_get_allowed_provinces() {
	return array( 'C', 'BA_GBA' );
}

/**
 * Validación server-side: bloquea el checkout si la provincia no es permitida.
 * Corre en woocommerce_checkout_process (antes de que WC remapee los datos),
 * por lo que $shipping_state todavía tiene el valor crudo del POST.
 */
add_action( 'woocommerce_checkout_process', 'etheme_checkout_validate_region', 5 );
function etheme_checkout_validate_region() {
	// Prefer the visible selector value (custom codes). Hidden shipping_state may be remapped to WC (e.g. BA_GBA → B) for rates.
	$state = '';
	if ( isset( $_POST['checkout_province_display'] ) ) {
		$state = sanitize_text_field( wp_unslash( $_POST['checkout_province_display'] ) );
	}
	if ( '' === $state && isset( $_POST['shipping_state'] ) ) {
		$state = sanitize_text_field( wp_unslash( $_POST['shipping_state'] ) );
	}
	if ( '' === $state && isset( $_POST['billing_state'] ) ) {
		$state = sanitize_text_field( wp_unslash( $_POST['billing_state'] ) );
	}
	$allowed = etheme_checkout_get_allowed_provinces();
	if ( '' === $state || in_array( $state, $allowed, true ) ) {
		return;
	}
	wc_add_notice(
		__( 'Lo sentimos, por el momento solo realizamos envíos a Capital Federal y Gran Buenos Aires. Escribinos para coordinar tu compra.', 'etheme' ),
		'error'
	);
}

/**
 * Remapea códigos de provincia custom a códigos estándar WooCommerce,
 * y compone billing_phone desde los inputs divididos.
 * Corre sobre los datos ya leídos (antes de la validación de WC).
 *
 * @param array $data Datos del checkout.
 * @return array
 */
add_filter( 'woocommerce_checkout_posted_data', 'etheme_checkout_remap_posted_data', 1 );
function etheme_checkout_remap_posted_data( $data ) {
	$data = etheme_checkout_remap_province( $data );
	$data = etheme_checkout_compose_billing_phone( $data );
	return $data;
}

/**
 * Remap BA_GBA / BA_INTERIOR → código WC estándar 'B' (Provincia de Buenos Aires).
 * BA_INTERIOR ya fue bloqueado en woocommerce_checkout_process; este remap es
 * un fallback para evitar errores de validación de estado si logra pasar.
 *
 * @param array $data Datos del checkout.
 * @return array
 */
function etheme_checkout_remap_province( $data ) {
	$remap = array( 'BA_GBA' => 'B', 'BA_INTERIOR' => 'B' );
	foreach ( array( 'shipping_state', 'billing_state' ) as $key ) {
		if ( isset( $data[ $key ] ) && isset( $remap[ $data[ $key ] ] ) ) {
			$data[ $key ] = $remap[ $data[ $key ] ];
		}
	}
	return $data;
}

/**
 * Compone billing_phone desde checkout_phone_area + checkout_phone_number si están presentes.
 *
 * @param array $data Datos del checkout.
 * @return array
 */
function etheme_checkout_compose_billing_phone( $data ) {
	$area = isset( $_POST['checkout_phone_area'] )
		? preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_POST['checkout_phone_area'] ) ) )
		: '';
	$num  = isset( $_POST['checkout_phone_number'] )
		? preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_POST['checkout_phone_number'] ) ) )
		: '';
	if ( $area && $num ) {
		$data['billing_phone'] = $area . $num;
	}
	return $data;
}

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

add_action( 'init', 'etheme_maybe_create_theme_pages' );
