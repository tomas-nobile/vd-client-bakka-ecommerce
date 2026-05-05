<?php
/**
 * Emits FAQPage JSON-LD on the front page from config homeFaqs items.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_seo_schema_faq_register() {
	add_action( 'wp_head', 'etheme_seo_emit_schema_faq', 20 );
}

function etheme_seo_emit_schema_faq() {
	if ( ! is_front_page() ) {
		return;
	}
	if ( ! apply_filters( 'etheme_seo_emit_faq_schema', true ) ) {
		return;
	}
	if ( ! function_exists( 'etheme_get_core_config' ) ) {
		return;
	}

	$config = etheme_get_core_config();
	$items  = isset( $config['homeFaqs']['items'] ) && is_array( $config['homeFaqs']['items'] ) ? $config['homeFaqs']['items'] : array();
	if ( empty( $items ) ) {
		return;
	}

	$entities = array();
	foreach ( $items as $item ) {
		if ( empty( $item['question'] ) || empty( $item['answer'] ) ) {
			continue;
		}
		$entities[] = array(
			'@type'          => 'Question',
			'name'           => wp_strip_all_tags( $item['question'] ),
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => wp_strip_all_tags( $item['answer'] ),
			),
		);
	}
	if ( empty( $entities ) ) {
		return;
	}

	etheme_seo_emit_jsonld( array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => $entities,
	) );
}
