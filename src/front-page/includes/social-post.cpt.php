<?php
// social-post.
/**
 * Register Custom Post Type: Social Post (Instagram-style posts for Home Blog)
 *
 * Used by the Home Blog section to show post-style cards with custom fields:
 * description, date (optional), social network, images, videos.
 * Supports title, editor, thumbnail, and block editor for block-based media extraction.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_register_social_post_cpt() {
	$labels = array(
		'name'               => __( 'Posteos Sociales', 'etheme' ),
		'singular_name'      => __( 'Posteo Social', 'etheme' ),
		'add_new'            => __( 'Añadir posteo', 'etheme' ),
		'add_new_item'       => __( 'Añadir nuevo posteo social', 'etheme' ),
		'edit_item'          => __( 'Editar posteo social', 'etheme' ),
		'new_item'           => __( 'Nuevo posteo social', 'etheme' ),
		'view_item'          => __( 'Ver posteo social', 'etheme' ),
		'search_items'       => __( 'Buscar posteos sociales', 'etheme' ),
		'not_found'          => __( 'No se encontraron posteos sociales', 'etheme' ),
		'not_found_in_trash' => __( 'No hay posteos sociales en la papelera', 'etheme' ),
		'menu_name'          => __( 'Posteos Sociales', 'etheme' ),
	);

	register_post_type( 'social_post', array(
		'labels'       => $labels,
		'public'       => true,
		'show_ui'      => true,
		'show_in_menu' => true,
		'menu_icon'    => 'dashicons-share',
		'supports'     => array( 'title', 'editor', 'thumbnail' ),
		'has_archive'  => true,
		'rewrite'      => array( 'slug' => 'social-posts' ),
		'show_in_rest' => true,
	) );
}
add_action( 'init', 'etheme_register_social_post_cpt' );
