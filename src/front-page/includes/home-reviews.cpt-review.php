<?php
// home-reviews.
/**
 * Register Custom Post Type: Review / Testimonial
 *
 * Uses ACF for custom fields (client name, role, rating, avatar).
 * ACF field group should target post_type == etheme_review.
 *
 * Expected ACF fields:
 *   - review_client_name  (text)
 *   - review_client_role  (text)
 *   - review_rating       (number, 1-5)
 *   - review_avatar       (image, returns ID)
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_register_review_cpt() {
	$labels = array(
		'name'               => __( 'Testimonios', 'etheme' ),
		'singular_name'      => __( 'Testimonio', 'etheme' ),
		'add_new'            => __( 'Añadir testimonio', 'etheme' ),
		'add_new_item'       => __( 'Añadir nuevo testimonio', 'etheme' ),
		'edit_item'          => __( 'Editar testimonio', 'etheme' ),
		'new_item'           => __( 'Nuevo testimonio', 'etheme' ),
		'view_item'          => __( 'Ver testimonio', 'etheme' ),
		'search_items'       => __( 'Buscar testimonios', 'etheme' ),
		'not_found'          => __( 'No se encontraron testimonios', 'etheme' ),
		'not_found_in_trash' => __( 'No hay testimonios en la papelera', 'etheme' ),
		'menu_name'          => __( 'Testimonios', 'etheme' ),
	);

	register_post_type( 'etheme_review', array(
		'labels'       => $labels,
		'public'       => false,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-format-quote',
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'  => false,
		'rewrite'      => false,
		'show_in_rest' => true,
	) );
}
add_action( 'init', 'etheme_register_review_cpt' );

/**
 * Register meta fields as fallback when ACF is not active.
 */
function etheme_register_review_meta_fallback() {
	if ( function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$fields = array(
		'review_client_name' => 'string',
		'review_client_role' => 'string',
		'review_rating'      => 'integer',
		'review_avatar'      => 'integer',
	);

	foreach ( $fields as $key => $type ) {
		register_post_meta( 'etheme_review', $key, array(
			'show_in_rest' => true,
			'single'       => true,
			'type'         => $type,
		) );
	}
}
add_action( 'init', 'etheme_register_review_meta_fallback' );
