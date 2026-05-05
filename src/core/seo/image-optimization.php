<?php
/**
 * Adds decoding="async" globally and an alt fallback when an image lacks one.
 *
 * Loading attribute and fetchpriority are decided per-call (see gallery.php) and
 * are not forced here.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_seo_image_optimization_register() {
	add_filter( 'wp_get_attachment_image_attributes', 'etheme_seo_image_attrs', 10, 3 );
}

function etheme_seo_image_attrs( $attr, $attachment, $size ) {
	if ( empty( $attr['decoding'] ) ) {
		$attr['decoding'] = 'async';
	}
	if ( ! isset( $attr['alt'] ) || '' === trim( (string) $attr['alt'] ) ) {
		$alt = $attachment instanceof WP_Post ? get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) : '';
		if ( '' === trim( (string) $alt ) && $attachment instanceof WP_Post ) {
			$alt = $attachment->post_title;
		}
		$attr['alt'] = (string) $alt;
	}
	return $attr;
}
