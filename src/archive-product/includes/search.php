<?php
/**
 * Product search expansion — adds separator/typo variants to the SQL query.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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

function etheme_get_search_variants( $term ) {
	$variants   = array();
	$normalized = preg_replace( '/[_\-\s]+/', ' ', $term );
	$compact    = str_replace( ' ', '', $normalized );
	$hyphenated = str_replace( ' ', '-', $normalized );
	$spaced     = $normalized;
	$underscored = str_replace( ' ', '_', $normalized );

	foreach ( array( $compact, $hyphenated, $spaced, $underscored ) as $candidate ) {
		if ( $candidate && $candidate !== $term ) {
			$variants[] = $candidate;
		}
	}

	return array_values( array_unique( $variants ) );
}

function etheme_build_search_variant_sql( $variants, $posts_table ) {
	if ( empty( $variants ) ) {
		return '';
	}

	global $wpdb;
	$like_sql = array();
	foreach ( $variants as $variant ) {
		$like       = $wpdb->prepare( '%s', '%' . $variant . '%' );
		$like_sql[] = "{$posts_table}.post_title LIKE {$like}";
	}

	return implode( ' OR ', $like_sql );
}
