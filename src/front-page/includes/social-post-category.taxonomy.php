<?php
// social-post-category.taxonomy.
/**
 * Register taxonomy: posteo_category for the social_post CPT.
 *
 * Terms are created manually in WP Admin under Posteos Sociales → Categorías de Posteos.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_register_posteo_category_taxonomy(): void {
	register_taxonomy(
		'posteo_category',
		'social_post',
		array(
			'labels'            => array(
				'name'          => __( 'Categorías de Posteos', 'etheme' ),
				'singular_name' => __( 'Categoría de Posteo', 'etheme' ),
				'all_items'     => __( 'Todas las categorías', 'etheme' ),
				'edit_item'     => __( 'Editar categoría', 'etheme' ),
				'add_new_item'  => __( 'Añadir categoría', 'etheme' ),
			),
			'hierarchical'      => true,
			'public'            => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'posteo-categoria' ),
			'show_admin_column' => true,
		)
	);
}
add_action( 'init', 'etheme_register_posteo_category_taxonomy' );
