<?php
/**
 * SEO module utilities.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_seo_get_locale() {
	return 'es_AR';
}

function etheme_seo_jsonld_encode( $data ) {
	$flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
		| JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
	return wp_json_encode( $data, $flags );
}

function etheme_seo_emit_jsonld( $data ) {
	if ( empty( $data ) ) {
		return;
	}
	$json = etheme_seo_jsonld_encode( $data );
	if ( false === $json ) {
		return;
	}
	echo "\n<script type=\"application/ld+json\">" . $json . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
}
