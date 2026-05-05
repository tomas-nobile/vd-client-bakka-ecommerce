<?php
/**
 * Detects which SEO plugin is active so the theme does not duplicate output.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_seo_yoast_active() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$cached = defined( 'WPSEO_VERSION' );
	return $cached;
}

function etheme_seo_yoast_woo_active() {
	static $cached = null;
	if ( null !== $cached ) {
		return $cached;
	}
	$cached = defined( 'WPSEO_WOO_VERSION' );
	return $cached;
}
