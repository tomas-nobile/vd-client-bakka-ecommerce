<?php
/**
 * Páginas del tema: slugs por defecto, creación automática y helpers de URL.
 *
 * Para cambiar un slug:
 * 1. Editá el array en etheme_get_theme_auto_pages() (o usá el filtro etheme_theme_auto_pages).
 * 2. Renombrá la plantilla en templates/ a page-{slug}.html para que la jerarquía FSE siga coincidiendo.
 *    (Si no renombrás la plantilla, asigná la plantilla a mano en el editor de la página.)
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Incrementar cuando se agreguen páginas nuevas al listado (vuelve a ejecutar la siembra). */
const ETHEME_AUTO_PAGES_VERSION = 7;

/**
 * Definición de páginas que el tema puede crear si no existen.
 *
 * Clave interna (key) → título + slug (post_name). El slug debe coincidir con page-{slug}.html.
 *
 * @return array<string, array{title: string, slug: string}>
 */
function etheme_get_theme_auto_pages() {
	$pages = array(
		'privacy_policy'      => array(
			'title' => __( 'Política de Privacidad', 'etheme' ),
			'slug'  => 'politica-de-privacidad',
		),
		'terms'               => array(
			'title' => __( 'Términos y Condiciones', 'etheme' ),
			'slug'  => 'terminos-y-condiciones',
		),
		'commerce_conditions' => array(
			'title' => __( 'Condiciones de Compra, Envíos y Devoluciones', 'etheme' ),
			'slug'  => 'condiciones-de-compra',
		),
		'contacto'            => array(
			'title' => __( 'Contacto', 'etheme' ),
			'slug'  => 'contacto',
		),
		'posteos'             => array(
			'title' => __( 'Trabajos Realizados', 'etheme' ),
			'slug'  => 'trabajos-realizados',
		),
	);

	return apply_filters( 'etheme_theme_auto_pages', $pages );
}

/**
 * Slug de una página del tema por clave (p. ej. 'posteos', 'contacto').
 *
 * @param string $key Key from etheme_get_theme_auto_pages().
 * @return string
 */
function etheme_get_theme_page_slug( $key ) {
	$pages = etheme_get_theme_auto_pages();
	if ( empty( $pages[ $key ] ) || ! is_array( $pages[ $key ] ) ) {
		return '';
	}
	return (string) $pages[ $key ]['slug'];
}

/**
 * URL de una página del tema, o fallback a home/{slug}/ si aún no existe el post.
 *
 * @param string $key Key from etheme_get_theme_auto_pages().
 * @return string
 */
function etheme_get_theme_page_url( $key ) {
	$slug = etheme_get_theme_page_slug( $key );
	if ( '' === $slug ) {
		return home_url( '/' );
	}
	$page = get_page_by_path( $slug );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	return home_url( '/' . $slug . '/' );
}

/**
 * Migra slugs en inglés de las páginas legales a español (una vez al subir a v3).
 *
 * @return void
 */
function etheme_migrate_legal_page_slugs_to_spanish() {
	$map = array(
		'privacy-policy'       => 'politica-de-privacidad',
		'terms-and-conditions' => 'terminos-y-condiciones',
		'buy-conditions'       => 'condiciones-de-compra',
	);

	foreach ( $map as $old_slug => $new_slug ) {
		$old_page = get_page_by_path( $old_slug );
		if ( ! $old_page instanceof WP_Post ) {
			continue;
		}
		if ( get_page_by_path( $new_slug ) instanceof WP_Post ) {
			continue;
		}

		wp_update_post(
			array(
				'ID'        => $old_page->ID,
				'post_name' => $new_slug,
			)
		);
	}
}

/**
 * Crear páginas faltantes según etheme_get_theme_auto_pages().
 * Se controla con la opción etheme_auto_pages_version para no repetir lógica innecesaria;
 * al subir ETHEME_AUTO_PAGES_VERSION se vuelve a comprobar qué falta.
 *
 * @return void
 */
function etheme_maybe_create_theme_pages() {
	$stored = (int) get_option( 'etheme_auto_pages_version', 0 );
	if ( $stored >= ETHEME_AUTO_PAGES_VERSION ) {
		return;
	}

	if ( $stored < 3 ) {
		etheme_migrate_legal_page_slugs_to_spanish();
	}

	if ( $stored < 6 ) {
		$old_page = get_page_by_path( 'posteos' );
		if ( $old_page instanceof WP_Post && ! get_page_by_path( 'trabajos-realizados' ) ) {
			wp_update_post( array(
				'ID'         => $old_page->ID,
				'post_name'  => 'trabajos-realizados',
				'post_title' => __( 'Trabajos Realizados', 'etheme' ),
			) );
		}
	}

	foreach ( etheme_get_theme_auto_pages() as $page_data ) {
		if ( empty( $page_data['slug'] ) || empty( $page_data['title'] ) ) {
			continue;
		}
		$slug      = $page_data['slug'];
		$existing  = get_page_by_path( $slug );
		if ( $existing instanceof WP_Post ) {
			if ( 'publish' !== $existing->post_status ) {
				wp_update_post( array( 'ID' => $existing->ID, 'post_status' => 'publish' ) );
			}
			continue;
		}

		wp_insert_post(
			array(
				'post_title'  => $page_data['title'],
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);
	}

	update_option( 'etheme_auto_pages_version', ETHEME_AUTO_PAGES_VERSION );

	// Opción antigua (solo legales): ya no se usa.
	delete_option( 'etheme_legal_pages_created' );
}
