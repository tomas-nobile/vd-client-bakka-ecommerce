<?php
/**
 * Meta tags fallback — description, OpenGraph, Twitter Cards.
 *
 * Only emitted when Yoast SEO is not active, to avoid duplicate tags.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_seo_meta_tags_register() {
	if ( etheme_seo_yoast_active() ) {
		return;
	}
	add_action( 'wp_head', 'etheme_seo_emit_meta_tags', 5 );
}

function etheme_seo_get_meta_description() {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			$desc = wp_strip_all_tags( $product->get_short_description() );
			if ( '' === trim( $desc ) ) {
				$desc = wp_strip_all_tags( $product->get_description() );
			}
			return wp_html_excerpt( $desc, 155, '' );
		}
	}

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		$shop_id = wc_get_page_id( 'shop' );
		if ( $shop_id > 0 ) {
			$excerpt = get_the_excerpt( $shop_id );
			if ( $excerpt ) {
				return wp_html_excerpt( wp_strip_all_tags( $excerpt ), 155, '' );
			}
		}
	}

	if ( is_singular() ) {
		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_html_excerpt( wp_strip_all_tags( $excerpt ), 155, '' );
		}
	}

	if ( is_tax() || is_category() ) {
		$desc = term_description();
		if ( $desc ) {
			return wp_html_excerpt( wp_strip_all_tags( $desc ), 155, '' );
		}
		$term = get_queried_object();
		if ( $term ) {
			return esc_attr( $term->name );
		}
	}

	$tagline = get_bloginfo( 'description' );
	if ( $tagline ) {
		return $tagline;
	}

	$config    = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
	$site_name = isset( $config['site']['name'] ) ? $config['site']['name'] : get_bloginfo( 'name' );
	return $site_name;
}

function etheme_seo_get_og_image_url() {
	if ( function_exists( 'is_product' ) && is_product() ) {
		$product = wc_get_product( get_the_ID() );
		if ( $product ) {
			$img_id = $product->get_image_id();
			if ( $img_id ) {
				$url = wp_get_attachment_image_url( $img_id, 'etheme-og' );
				if ( $url ) {
					return $url;
				}
			}
		}
	}

	if ( is_singular() && has_post_thumbnail() ) {
		$url = get_the_post_thumbnail_url( null, 'etheme-og' );
		if ( $url ) {
			return $url;
		}
	}

	if ( is_tax() || is_category() ) {
		$thumbnail_id = get_term_meta( get_queried_object_id(), 'thumbnail_id', true );
		if ( $thumbnail_id ) {
			$url = wp_get_attachment_image_url( $thumbnail_id, 'etheme-og' );
			if ( $url ) {
				return $url;
			}
		}
	}

	$logo_id = get_theme_mod( 'custom_logo' );
	if ( $logo_id ) {
		$url = wp_get_attachment_image_url( $logo_id, 'full' );
		if ( $url ) {
			return $url;
		}
	}

	$logo_path = get_template_directory() . '/assets/images/logo-big.webp';
	if ( file_exists( $logo_path ) ) {
		return get_template_directory_uri() . '/assets/images/logo-big.webp';
	}

	return '';
}

function etheme_seo_get_og_type() {
	if ( function_exists( 'is_product' ) && is_product() ) {
		return 'product';
	}
	if ( is_singular() ) {
		return 'article';
	}
	return 'website';
}

function etheme_seo_get_canonical_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	if ( is_front_page() || is_home() ) {
		return home_url( '/' );
	}
	return get_pagenum_link();
}

function etheme_seo_emit_meta_tags() {
	$description = etheme_seo_get_meta_description();
	$og_image    = etheme_seo_get_og_image_url();
	$og_type     = etheme_seo_get_og_type();
	$og_url      = etheme_seo_get_canonical_url();
	$og_title    = wp_get_document_title();
	$locale      = function_exists( 'etheme_seo_get_locale' ) ? etheme_seo_get_locale() : 'es_AR';
	$site_name   = get_bloginfo( 'name' );

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:locale" content="%s" />' . "\n", esc_attr( $locale ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", esc_attr( $og_type ) );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $og_title ) );

	if ( $description ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $og_url ) );
	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( $site_name ) );

	if ( $og_image ) {
		printf( '<meta property="og:image" content="%s" />' . "\n", esc_url( $og_image ) );
	}

	echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
	printf( '<meta name="twitter:title" content="%s" />' . "\n", esc_attr( $og_title ) );

	if ( $description ) {
		printf( '<meta name="twitter:description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	if ( $og_image ) {
		printf( '<meta name="twitter:image" content="%s" />' . "\n", esc_url( $og_image ) );
	}
}
