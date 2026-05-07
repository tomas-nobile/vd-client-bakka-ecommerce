<?php
/**
 * Organization and WebSite JSON-LD schemas.
 *
 * Organization is emitted on every page. WebSite (with SearchAction) only on the front page.
 * Both are skipped when Yoast SEO is active.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_seo_schema_organization_register() {
	if ( etheme_seo_yoast_active() ) {
		return;
	}
	add_action( 'wp_head', 'etheme_seo_emit_schema_organization', 15 );
}

function etheme_seo_emit_schema_organization() {
	$config    = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
	$site_name = isset( $config['site']['name'] ) ? $config['site']['name'] : get_bloginfo( 'name' );
	$site_url  = isset( $config['site']['url'] ) ? $config['site']['url'] : home_url( '/' );
	$email     = isset( $config['email']['fromAddress'] ) ? $config['email']['fromAddress'] : '';
	$address   = isset( $config['site']['address'] ) ? $config['site']['address'] : '';

	$social_urls = array();
	$social      = isset( $config['social'] ) && is_array( $config['social'] ) ? $config['social'] : array();
	foreach ( $social as $data ) {
		if ( isset( $data['url'] ) && '#' !== $data['url'] && '' !== $data['url'] ) {
			$social_urls[] = $data['url'];
		}
	}

	$logo_url = '';
	$logo_id  = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$logo_url = wp_get_attachment_image_url( $logo_id, 'full' );
	}
	if ( ! $logo_url ) {
		$logo_path = get_template_directory() . '/assets/images/logo-big.webp';
		if ( file_exists( $logo_path ) ) {
			$logo_url = get_template_directory_uri() . '/assets/images/logo-big.webp';
		}
	}

	$org = array(
		'@context' => 'https://schema.org',
		'@type'    => 'Organization',
		'name'     => $site_name,
		'url'      => $site_url,
	);

	if ( $email ) {
		$org['email'] = $email;
	}

	if ( $address ) {
		$org['address'] = array(
			'@type'           => 'PostalAddress',
			'addressLocality' => $address,
			'addressCountry'  => 'AR',
		);
	}

	if ( $logo_url ) {
		$org['logo'] = array(
			'@type' => 'ImageObject',
			'url'   => $logo_url,
		);
	}

	if ( ! empty( $social_urls ) ) {
		$org['sameAs'] = array_values( $social_urls );
	}

	etheme_seo_emit_jsonld( $org );

	if ( is_front_page() ) {
		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/tienda/' );
		etheme_seo_emit_jsonld( array(
			'@context'        => 'https://schema.org',
			'@type'           => 'WebSite',
			'name'            => $site_name,
			'url'             => $site_url,
			'potentialAction' => array(
				'@type'       => 'SearchAction',
				'target'      => array(
					'@type'       => 'EntryPoint',
					'urlTemplate' => esc_url( $shop_url ) . '?s={search_term_string}',
				),
				'query-input' => 'required name=search_term_string',
			),
		) );
	}
}
