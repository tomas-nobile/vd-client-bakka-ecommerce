<?php
/**
 * Information page helper functions.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'etheme_get_about_data' ) ) :
/**
 * Retrieve about page data from config.json.
 *
 * @return array
 */
function etheme_get_about_data() {
	$config   = etheme_get_core_config();
	$defaults = array(
		'title'           => __( 'Nosotros', 'etheme' ),
		'breadcrumbLabel' => __( 'Nosotros', 'etheme' ),
		'subtitle'        => '',
		'description'     => '',
		'values'          => array(),
	);
	$data = isset( $config['about'] ) && is_array( $config['about'] ) ? $config['about'] : array();
	return array_merge( $defaults, $data );
}
endif;

if ( ! function_exists( 'etheme_get_faqs_data' ) ) :
/**
 * Retrieve FAQ page data from config.json.
 *
 * @return array
 */
function etheme_get_faqs_data() {
	$config   = etheme_get_core_config();
	$defaults = array(
		'title'           => __( 'Preguntas Frecuentes', 'etheme' ),
		'breadcrumbLabel' => __( 'Preguntas Frecuentes', 'etheme' ),
		'subtitle'        => '',
		'categories'      => array(),
	);
	$data = isset( $config['faqs'] ) && is_array( $config['faqs'] ) ? $config['faqs'] : array();
	return array_merge( $defaults, $data );
}
endif;

if ( ! function_exists( 'etheme_get_legal_page_data' ) ) :
/**
 * Retrieve legal page data from config.json by key.
 *
 * @param string $key One of: privacy, terms, commerceConditions.
 * @return array
 */
function etheme_get_legal_page_data( $key ) {
	$config = etheme_get_core_config();

	$allowed = array( 'privacy', 'terms', 'commerceConditions' );
	if ( ! in_array( $key, $allowed, true ) ) {
		$key = 'privacy';
	}

	$defaults = array(
		'title'           => __( 'Información', 'etheme' ),
		'breadcrumbLabel' => __( 'Información', 'etheme' ),
		'subtitle'        => '',
		'intro'           => '',
		'sections'        => array(),
	);

	$data = isset( $config[ $key ] ) && is_array( $config[ $key ] ) ? $config[ $key ] : array();

	return array_merge( $defaults, $data );
}
endif;

if ( ! function_exists( 'etheme_render_legal_content_html' ) ) :
/**
 * Render the legal content HTML (sections list).
 * Used both by the full-page block and by checkout modals.
 *
 * @param string $key One of: privacy, terms, commerceConditions.
 * @return string Safe HTML.
 */
function etheme_render_legal_content_html( $key ) {
	$data     = etheme_get_legal_page_data( $key );
	$sections = is_array( $data['sections'] ) ? $data['sections'] : array();
	$output   = '';

	if ( '' !== $data['intro'] ) {
		$output .= '<p class="info-page-intro">' . esc_html( $data['intro'] ) . '</p>';
	}

	foreach ( $sections as $section ) {
		$heading = isset( $section['heading'] ) ? $section['heading'] : '';
		$body    = isset( $section['body'] )    ? $section['body']    : '';

		if ( '' === $heading && '' === $body ) {
			continue;
		}

		if ( '' !== $heading ) {
			$output .= '<h5>' . esc_html( $heading ) . '</h5>';
		}

		if ( '' !== $body ) {
			$output .= '<p>' . esc_html( $body ) . '</p>';
		}
	}

	return $output;
}
endif;
