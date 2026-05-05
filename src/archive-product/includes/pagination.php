<?php
/**
 * Prevent 404 on paginated product taxonomy URLs.
 *
 * The archive block uses wc_get_products() for the product grid, bypassing the
 * main WordPress query. Without this filter, WordPress would 404 on page 2+
 * because the main query finds no posts for that page number.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
