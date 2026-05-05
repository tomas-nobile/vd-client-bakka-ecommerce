<?php
/**
 * Price range filtering for wc_get_products() queries.
 *
 * Allows the archive block to pass a `price_range` query var and have it
 * translated into the correct meta_query for WooCommerce's data store.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_product_data_store_cpt_get_products_query', 'etheme_handle_price_range_query_var', 10, 2 );
function etheme_handle_price_range_query_var( $query, $query_vars ) {
	if ( empty( $query_vars['price_range'] ) || ! is_array( $query_vars['price_range'] ) || count( $query_vars['price_range'] ) !== 2 ) {
		return $query;
	}

	$min_price = floatval( $query_vars['price_range'][0] );
	$max_price = floatval( $query_vars['price_range'][1] );

	if ( $min_price <= 0 && $max_price >= PHP_INT_MAX ) {
		return $query;
	}

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
		$query['meta_query'] = isset( $query['meta_query'] )
			? array_merge( $query['meta_query'], $meta_query )
			: $meta_query;
	}

	return $query;
}
