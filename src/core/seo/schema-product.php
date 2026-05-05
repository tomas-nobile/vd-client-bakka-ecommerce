<?php
/**
 * Emits Product JSON-LD on single-product pages.
 *
 * Skipped when Yoast WooCommerce SEO is active to avoid duplicate schema.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_seo_schema_product_register() {
	add_action( 'wp_head', 'etheme_seo_emit_schema_product', 20 );
}

function etheme_seo_emit_schema_product() {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	if ( etheme_seo_yoast_woo_active() ) {
		return;
	}
	if ( ! apply_filters( 'etheme_seo_emit_product_schema', true ) ) {
		return;
	}

	global $product;
	if ( ! ( $product instanceof WC_Product ) ) {
		$product = wc_get_product( get_the_ID() );
	}
	if ( ! ( $product instanceof WC_Product ) ) {
		return;
	}

	$data = etheme_seo_build_product_schema( $product );
	if ( ! empty( $data ) ) {
		etheme_seo_emit_jsonld( $data );
	}
}

function etheme_seo_build_product_schema( WC_Product $product ) {
	$config    = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
	$site_name = isset( $config['site']['name'] ) ? $config['site']['name'] : get_bloginfo( 'name' );

	$description = wp_strip_all_tags( $product->get_short_description() );
	if ( '' === trim( $description ) ) {
		$description = wp_strip_all_tags( $product->get_description() );
	}
	$description = wp_html_excerpt( $description, 5000, '' );

	$images = etheme_seo_get_product_image_urls( $product );

	$data = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Product',
		'name'     => $product->get_name(),
		'url'      => get_permalink( $product->get_id() ),
		'brand'    => array(
			'@type' => 'Brand',
			'name'  => $site_name,
		),
	);

	if ( '' !== $description ) {
		$data['description'] = $description;
	}
	if ( ! empty( $images ) ) {
		$data['image'] = $images;
	}
	$sku = $product->get_sku();
	if ( '' !== $sku ) {
		$data['sku'] = $sku;
	}

	$offers = etheme_seo_build_product_offers( $product );
	if ( ! empty( $offers ) ) {
		$data['offers'] = $offers;
	}

	return $data;
}

function etheme_seo_get_product_image_urls( WC_Product $product ) {
	$urls = array();
	$ids  = array();

	$main_id = $product->get_image_id();
	if ( $main_id ) {
		$ids[] = (int) $main_id;
	}
	$gallery_ids = $product->get_gallery_image_ids();
	if ( is_array( $gallery_ids ) ) {
		foreach ( $gallery_ids as $gid ) {
			$ids[] = (int) $gid;
		}
	}
	$ids = array_values( array_unique( array_filter( $ids ) ) );

	foreach ( $ids as $id ) {
		$src = wp_get_attachment_image_url( $id, 'large' );
		if ( $src ) {
			$urls[] = $src;
		}
	}
	return $urls;
}

function etheme_seo_build_product_offers( WC_Product $product ) {
	$permalink    = get_permalink( $product->get_id() );
	$availability = 'https://schema.org/' . ( $product->is_in_stock() ? 'InStock' : 'OutOfStock' );
	$valid_until  = gmdate( 'Y-m-d', strtotime( '+1 year' ) );

	if ( $product->is_type( 'variable' ) && method_exists( $product, 'get_variation_prices' ) ) {
		$prices = $product->get_variation_prices( true );
		if ( ! empty( $prices['price'] ) ) {
			$low_price  = wc_format_decimal( min( $prices['price'] ), 2 );
			$high_price = wc_format_decimal( max( $prices['price'] ), 2 );
			return array(
				'@type'           => 'AggregateOffer',
				'priceCurrency'   => 'ARS',
				'lowPrice'        => $low_price,
				'highPrice'       => $high_price,
				'offerCount'      => count( $prices['price'] ),
				'availability'    => $availability,
				'itemCondition'   => 'https://schema.org/NewCondition',
				'priceValidUntil' => $valid_until,
				'url'             => $permalink,
			);
		}
	}

	$price = $product->get_price();
	if ( '' === $price || null === $price ) {
		return array();
	}

	return array(
		'@type'           => 'Offer',
		'price'           => wc_format_decimal( $price, 2 ),
		'priceCurrency'   => 'ARS',
		'availability'    => $availability,
		'itemCondition'   => 'https://schema.org/NewCondition',
		'priceValidUntil' => $valid_until,
		'url'             => $permalink,
	);
}
