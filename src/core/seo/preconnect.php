<?php
/**
 * Resource hints for Google Fonts (not handled by Yoast).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_seo_preconnect_register() {
	add_action( 'wp_head', 'etheme_seo_emit_preconnect', 1 );
}

function etheme_seo_emit_preconnect() {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}
