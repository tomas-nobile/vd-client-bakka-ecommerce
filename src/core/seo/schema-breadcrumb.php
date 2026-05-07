<?php
/**
 * BreadcrumbList JSON-LD schema.
 *
 * Emitted on single-product, shop archive, and product_cat taxonomy pages.
 * Skipped when Yoast WooCommerce SEO is active (it provides its own breadcrumb schema).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_seo_schema_breadcrumb_register() {
	add_action( 'wp_head', 'etheme_seo_emit_schema_breadcrumb', 20 );
}

function etheme_seo_emit_schema_breadcrumb() {
	if ( etheme_seo_yoast_woo_active() ) {
		return;
	}
	if ( ! apply_filters( 'etheme_seo_emit_breadcrumb_schema', true ) ) {
		return;
	}

	$items = etheme_seo_build_breadcrumb_items();
	if ( count( $items ) < 2 ) {
		return;
	}

	$list_items = array();
	foreach ( $items as $position => $item ) {
		$list_item = array(
			'@type'    => 'ListItem',
			'position' => $position + 1,
			'name'     => $item['name'],
		);
		if ( ! empty( $item['url'] ) ) {
			$list_item['item'] = $item['url'];
		}
		$list_items[] = $list_item;
	}

	etheme_seo_emit_jsonld( array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $list_items,
	) );
}

function etheme_seo_build_breadcrumb_items() {
	$items = array();

	$items[] = array(
		'name' => get_bloginfo( 'name' ),
		'url'  => home_url( '/' ),
	);

	$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';

	if ( function_exists( 'is_product' ) && is_product() ) {
		if ( $shop_url ) {
			$items[] = array( 'name' => __( 'Tienda', 'etheme' ), 'url' => $shop_url );
		}

		$terms = get_the_terms( get_the_ID(), 'product_cat' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$cat = $terms[0];
			// Prefer a non-uncategorized term.
			foreach ( $terms as $t ) {
				if ( 'uncategorized' !== $t->slug ) {
					$cat = $t;
					break;
				}
			}
			if ( 'uncategorized' !== $cat->slug ) {
				$items[] = array( 'name' => $cat->name, 'url' => get_term_link( $cat ) );
			}
		}

		$items[] = array( 'name' => get_the_title(), 'url' => '' );

	} elseif ( function_exists( 'is_shop' ) && is_shop() ) {
		$items[] = array( 'name' => __( 'Tienda', 'etheme' ), 'url' => '' );

	} elseif ( is_tax( 'product_cat' ) ) {
		if ( $shop_url ) {
			$items[] = array( 'name' => __( 'Tienda', 'etheme' ), 'url' => $shop_url );
		}
		$term = get_queried_object();
		if ( $term ) {
			$items[] = array( 'name' => $term->name, 'url' => '' );
		}
	} else {
		return array();
	}

	return $items;
}
