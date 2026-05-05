<?php
/**
 * Product Relevance — custom meta field for "Productos Populares" ordering.
 *
 * Stores _etheme_relevance (0–100, default 50) as post meta.
 * Post meta is never displayed in the "Additional Information" tab — only
 * WooCommerce product attributes (pa_* taxonomies) appear there.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_product_relevance_field() {
	global $post;
	$value = get_post_meta( $post->ID, '_etheme_relevance', true );
	if ( '' === $value ) {
		$value = '50';
	}
	woocommerce_wp_text_input( array(
		'id'                => '_etheme_relevance',
		'label'             => __( 'Relevancia (Productos Populares)', 'etheme' ),
		'description'       => __( '0–100. Mayor valor = aparece antes en el home. Por defecto: 50.', 'etheme' ),
		'desc_tip'          => true,
		'type'              => 'number',
		'custom_attributes' => array(
			'min'  => '0',
			'max'  => '100',
			'step' => '1',
		),
		'value'             => $value,
	) );
}
add_action( 'woocommerce_product_options_general_product_data', 'etheme_product_relevance_field' );

function etheme_save_product_relevance( $post_id ) {
	if ( ! isset( $_POST['_etheme_relevance'] ) ) {
		return;
	}
	$value = min( 100, max( 0, absint( $_POST['_etheme_relevance'] ) ) );
	update_post_meta( $post_id, '_etheme_relevance', $value );
}
add_action( 'woocommerce_process_product_meta', 'etheme_save_product_relevance' );

/**
 * Temporary posts_clauses filter: LEFT JOIN + COALESCE so products without
 * the meta are treated as relevance = 50 rather than being excluded.
 */
function etheme_popular_relevance_orderby( $clauses ) {
	global $wpdb;
	$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS _etheme_rel
		ON ( {$wpdb->posts}.ID = _etheme_rel.post_id
		AND _etheme_rel.meta_key = '_etheme_relevance' )";
	$clauses['orderby'] = 'COALESCE(CAST(_etheme_rel.meta_value AS SIGNED), 50) DESC';
	return $clauses;
}
