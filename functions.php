<?php
/**
 * Bakka Theme — functions.php
 *
 * Responsibilities: block registration, asset enqueuing, theme setup, requires.
 * Business logic lives in src/{block}/includes/ — not here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ==========================================================================
   Block registration
   ========================================================================== */

function etheme_register_blocks() {
	$blocks = array(
		'build/0_block',
		'build/taxonomy-product_cat/index',
		'build/single-product/index',
		'build/front-page/index',
		'build/core/navbar',
		'build/core/footer',
		'build/core/header',
		'build/archive-product/index',
		'build/page-cart/index',
		'build/page-checkout/index',
		'build/page/index',
		'build/page-trabajos-realizados/index',
		'build/contact/index',
		'build/information-page/index',
		'build/page-404/index',
		'build/order-received/index',
	);

	foreach ( $blocks as $block ) {
		register_block_type( __DIR__ . '/' . $block );
	}
}
add_action( 'init', 'etheme_register_blocks' );

/**
 * Replace the static block.json version with filemtime for all etheme block
 * styles so every deploy automatically busts the browser/server cache.
 */
function etheme_bust_block_style_cache() {
	global $wp_styles;
	if ( empty( $wp_styles->registered ) ) {
		return;
	}
	$theme_uri = get_template_directory_uri();
	$theme_dir = get_template_directory();
	foreach ( $wp_styles->registered as $handle => $style ) {
		if ( strpos( $handle, 'wp-block-etheme-' ) !== 0 ) {
			continue;
		}
		if ( strpos( $style->src, $theme_uri ) !== 0 ) {
			continue;
		}
		$path = $theme_dir . substr( $style->src, strlen( $theme_uri ) );
		if ( file_exists( $path ) ) {
			$wp_styles->registered[ $handle ]->ver = filemtime( $path );
		}
	}
}
add_action( 'wp_enqueue_scripts', 'etheme_bust_block_style_cache', 5 );
add_action( 'admin_enqueue_scripts', 'etheme_bust_block_style_cache', 5 );

/* ==========================================================================
   Theme setup (supports, menus)
   ========================================================================== */

function etheme_setup_theme_support() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	add_image_size( 'etheme-og', 1200, 630, true );

	register_nav_menus( array(
		'etheme-primary' => __( 'Navegación principal', 'etheme' ),
	) );
}
add_action( 'after_setup_theme', 'etheme_setup_theme_support' );

/* ==========================================================================
   Asset enqueuing
   ========================================================================== */

/**
 * Enqueue a block's compiled style-index.css if the file exists.
 *
 * @param string $handle    Style handle.
 * @param string $build_rel Path relative to theme root, e.g. `/build/page-cart/index/style-index.css`.
 * @param array  $deps      Dependency handles.
 */
function etheme_enqueue_block_style_index( $handle, $build_rel, $deps = array() ) {
	$path = get_template_directory() . $build_rel;
	if ( ! file_exists( $path ) ) {
		return;
	}
	wp_enqueue_style( $handle, get_theme_file_uri( $build_rel ), $deps, filemtime( $path ) );
}

function etheme_enqueue_assets() {
	$version = filemtime( get_template_directory() . '/build/index.css' );

	wp_enqueue_style( 'etheme-google-fonts', 'https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100..900;1,100..900&family=Jost:ital,wght@0,100..900;1,100..900&display=swap', array(), null );
	wp_enqueue_style( 'test-theme-main-css', get_theme_file_uri( '/build/index.css' ), array(), $version );
	wp_enqueue_style( 'etheme-style', get_stylesheet_uri(), array(), filemtime( get_template_directory() . '/style.css' ) );
	wp_enqueue_script( 'test-theme-main-js', get_theme_file_uri( '/build/index.js' ), array( 'wp-element' ), $version, true );

	if ( class_exists( 'WooCommerce' ) ) {
		etheme_enqueue_block_style_index( 'etheme-woocommerce-css', '/assets/css/woocommerce.css' );
	}
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_assets' );

function etheme_enqueue_editor_assets() {
	$version = filemtime( get_template_directory() . '/build/index.css' );
	wp_enqueue_style( 'test-theme-editor-css', get_theme_file_uri( '/build/index.css' ), array(), $version );
}
add_action( 'enqueue_block_editor_assets', 'etheme_enqueue_editor_assets' );

function etheme_enqueue_front_page_styles() {
	$posteos_slug = etheme_get_theme_page_slug( 'posteos' );
	$posteos_page = $posteos_slug ? get_page_by_path( $posteos_slug ) : null;
	$is_posteos   = ( $posteos_page instanceof WP_Post ) && is_page( $posteos_page->ID );

	if ( ! is_front_page() && ! $is_posteos ) {
		return;
	}

	etheme_enqueue_block_style_index( 'etheme-front-page-index', '/build/front-page/index/style-index.css' );
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_front_page_styles' );

function etheme_enqueue_navbar_styles() {
	etheme_enqueue_block_style_index( 'etheme-navbar', '/build/core/navbar/style-index.css', array( 'test-theme-main-css' ) );
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_navbar_styles', 15 );

function etheme_enqueue_footer_styles() {
	etheme_enqueue_block_style_index( 'etheme-footer', '/build/core/footer/style-index.css', array( 'test-theme-main-css' ) );
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_footer_styles', 15 );

function etheme_enqueue_404_styles() {
	if ( ! is_404() ) {
		return;
	}
	etheme_enqueue_block_style_index( 'etheme-page-404-index', '/build/page-404/index/style-index.css', array( 'test-theme-main-css' ) );
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_404_styles' );

function etheme_enqueue_wc_page_template_block_styles() {
	$deps = array( 'test-theme-main-css' );

	if ( function_exists( 'is_cart' ) && is_cart() ) {
		etheme_enqueue_block_style_index( 'etheme-page-cart-index', '/build/page-cart/index/style-index.css', $deps );
	}
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		etheme_enqueue_block_style_index( 'etheme-page-checkout-index', '/build/page-checkout/index/style-index.css', $deps );
		$arrow_url = esc_url( get_template_directory_uri() . '/assets/images/dropdown-arrow.png' );
		wp_add_inline_style( 'etheme-page-checkout-index', ".page-checkout-block{--co-dropdown-arrow-url:url('{$arrow_url}');}" );
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'order-received' ) ) {
		etheme_enqueue_block_style_index( 'etheme-order-received-index', '/build/order-received/index/style-index.css', $deps );
	}
}
add_action( 'wp_enqueue_scripts', 'etheme_enqueue_wc_page_template_block_styles', 20 );

/* ==========================================================================
   Core utilities
   ========================================================================== */

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

/**
 * Do not auto-login after registration — user must log in via the password setup email.
 */
add_filter( 'woocommerce_registration_auth_new_customer', '__return_false' );

/**
 * Suppress only the "added to cart" success notice.
 * Coupon-applied and other success notices are preserved.
 *
 * woocommerce_add_to_cart_message_html makes WC store an empty string in the session.
 * etheme_purge_empty_cart_notices (priority 21, after WC's add_to_cart_action at 20)
 * removes those empty entries before they can be rendered.
 */
add_filter( 'woocommerce_add_to_cart_message_html', '__return_empty_string' );

add_action( 'wp_loaded', 'etheme_purge_empty_cart_notices', 21 );
function etheme_purge_empty_cart_notices() {
	if ( ! WC()->session ) {
		return;
	}
	$notices = WC()->session->get( 'wc_notices', array() );
	if ( empty( $notices['success'] ) ) {
		return;
	}
	$notices['success'] = array_values( array_filter( $notices['success'], function ( $n ) {
		return ! empty( $n['notice'] );
	} ) );
	if ( empty( $notices['success'] ) ) {
		unset( $notices['success'] );
	}
	WC()->session->set( 'wc_notices', $notices );
}

/* ==========================================================================
   Module requires
   ========================================================================== */

// Core
require_once __DIR__ . '/src/core/seo/seo-bootstrap.php';
require_once __DIR__ . '/src/core/includes/theme-pages.php';
require_once __DIR__ . '/src/core/includes/user-enumeration-protection.php';
require_once __DIR__ . '/src/core/navbar/includes/navbar-helpers.php';
require_once __DIR__ . '/src/core/emails/includes/email-hooks.php';

// Front page: CPTs and AJAX handlers
require_once __DIR__ . '/src/front-page/includes/product-relevance.php';
require_once __DIR__ . '/src/front-page/includes/home-reviews.cpt-review.php';
require_once __DIR__ . '/src/front-page/includes/social-post.cpt.php';
require_once __DIR__ . '/src/front-page/includes/social-post.metabox.php';
require_once __DIR__ . '/src/front-page/includes/social-post-category.taxonomy.php';
require_once __DIR__ . '/src/front-page/includes/social-post-import.php';
require_once __DIR__ . '/src/front-page/includes/home-newsletter.ajax-handlers.php';

// WooCommerce attribute seed
require_once __DIR__ . '/src/core/includes/wc-attributes-seed.php';

// Archive product
require_once __DIR__ . '/src/archive-product/includes/search.php';
require_once __DIR__ . '/src/archive-product/includes/price-filter.php';
require_once __DIR__ . '/src/archive-product/includes/pagination.php';

// Single product
require_once __DIR__ . '/src/single-product/includes/buy-now.php';

// Cart
require_once __DIR__ . '/src/page-cart/includes/ajax-handlers.php';

// Checkout
require_once __DIR__ . '/src/page-checkout/includes/region-validation.php';

// Page (account)
require_once __DIR__ . '/src/page/includes/login-security.php';

// Posteos
require_once __DIR__ . '/src/page-trabajos-realizados/includes/ajax-handlers.php';

add_action( 'init', 'etheme_maybe_create_theme_pages' );
