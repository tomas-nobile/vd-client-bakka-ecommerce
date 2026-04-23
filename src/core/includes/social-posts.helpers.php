<?php
// core/social-posts.helpers.
/**
 * Core Social Posts & Blog Card Helper Functions
 *
 * Shared helpers for rendering social posts (CPT social_post) and blog cards.
 * Used by: front-page-index block, page-posteos-index block, AJAX handlers.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ─── Theme config ─────────────────────────────────────────────────────────────

/**
 * Get theme config from src/core/config/config.json.
 *
 * @return array Decoded config or empty array on failure.
 */
function etheme_get_theme_config() {
	static $config = null;
	if ( null !== $config ) {
		return $config;
	}
	$path = get_template_directory() . '/src/core/config/config.json';
	if ( ! is_readable( $path ) ) {
		$config = array();
		return $config;
	}
	$json   = file_get_contents( $path );
	$config = json_decode( $json, true );
	return is_array( $config ) ? $config : array();
}

// ─── Social Post (CPT) meta helpers ───────────────────────────────────────────
// If an older copy of these helpers was already loaded (e.g. from a legacy
// front-page helpers file), don't redeclare them to avoid fatal errors.
if ( function_exists( 'etheme_get_social_post_description' ) ) {
	return;
}

/**
 * Get description meta for a social post. Empty for non–social_post.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return string
 */
function etheme_get_social_post_description( $post ) {
	$post_id = $post instanceof WP_Post ? $post->ID : (int) $post;
	if ( get_post_type( $post_id ) !== 'social_post' ) {
		return '';
	}
	$v = get_post_meta( $post_id, 'social_post_description', true );
	return is_string( $v ) ? $v : '';
}

/**
 * Get optional date meta (Y-m-d) for social post. Empty if not set.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return string
 */
function etheme_get_social_post_date_meta( $post ) {
	$post_id = $post instanceof WP_Post ? $post->ID : (int) $post;
	if ( get_post_type( $post_id ) !== 'social_post' ) {
		return '';
	}
	$v = get_post_meta( $post_id, 'social_post_date', true );
	return is_string( $v ) ? $v : '';
}

/**
 * Get social network key for post. Default 'instagram'.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return string
 */
function etheme_get_social_post_network( $post ) {
	$post_id = $post instanceof WP_Post ? $post->ID : (int) $post;
	if ( get_post_type( $post_id ) !== 'social_post' ) {
		return 'instagram';
	}
	$v = get_post_meta( $post_id, 'social_post_network', true );
	return is_string( $v ) && $v !== '' ? $v : 'instagram';
}

/**
 * Get social config (handle, url, icon) for a given network key.
 *
 * Falls back to Instagram config when the requested network is not configured.
 *
 * @param string $network Network key (e.g. 'instagram', 'facebook').
 * @return array {
 *   @type string $handle Handle without @ (e.g. 'bakka.deco').
 *   @type string $url    Profile URL.
 *   @type string $icon   Absolute URL to icon SVG, or empty string.
 * }
 */
function etheme_get_social_network_config( $network ) {
	$config = etheme_get_theme_config();

	$social = isset( $config['social'] ) && is_array( $config['social'] ) ? $config['social'] : array();

	if ( ! isset( $social[ $network ] ) && isset( $social['instagram'] ) ) {
		$network = 'instagram';
	}

	$data = isset( $social[ $network ] ) && is_array( $social[ $network ] ) ? $social[ $network ] : array();

	$handle = isset( $data['handle'] ) ? (string) $data['handle'] : '';
	$url    = isset( $data['url'] ) ? (string) $data['url'] : '';
	$icon   = isset( $data['icon'] ) ? (string) $data['icon'] : '';

	$icon_url = '';
	if ( $icon !== '' ) {
		$icon_url = trailingslashit( get_template_directory_uri() ) . ltrim( $icon, '/' );
	}

	return array(
		'handle' => $handle,
		'url'    => $url,
		'icon'   => $icon_url,
	);
}

/**
 * Get image attachment IDs from social post meta.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return int[]
 */
function etheme_get_social_post_images_meta( $post ) {
	$post_id = $post instanceof WP_Post ? $post->ID : (int) $post;
	if ( get_post_type( $post_id ) !== 'social_post' ) {
		return array();
	}
	$v = get_post_meta( $post_id, 'social_post_images', true );
	if ( ! is_string( $v ) || $v === '' ) {
		return array();
	}
	$decoded = json_decode( $v, true );
	return is_array( $decoded ) ? array_map( 'absint', $decoded ) : array();
}

/**
 * Get video URLs from social post meta.
 *
 * @param int|WP_Post $post Post ID or object.
 * @return string[]
 */
function etheme_get_social_post_videos_meta( $post ) {
	$post_id = $post instanceof WP_Post ? $post->ID : (int) $post;
	if ( get_post_type( $post_id ) !== 'social_post' ) {
		return array();
	}
	$v = get_post_meta( $post_id, 'social_post_videos', true );
	if ( ! is_string( $v ) || $v === '' ) {
		return array();
	}
	$decoded = json_decode( $v, true );
	if ( ! is_array( $decoded ) ) {
		return array();
	}
	return array_map( 'esc_url_raw', array_filter( $decoded ) );
}

// ─── Multimedia helpers ────────────────────────────────────────────────────────

/**
 * Extract multimedia items (images, video, embed) from a post's block content.
 *
 * Recursively walks all blocks and innerBlocks. Fallback: featured image.
 *
 * @param WP_Post $post Post object.
 * @return array[] Multimedia items.
 */
function etheme_get_post_multimedia( $post ) {
	$blocks    = parse_blocks( $post->post_content );
	$collected = etheme_collect_media_from_blocks( $blocks );
	return etheme_append_featured_image( $post, $collected['items'], $collected['gallery_ids'] );
}

/**
 * Recursively collect media items and gallery IDs from an array of blocks (including innerBlocks).
 *
 * @param array[] $blocks Array of parsed blocks.
 * @return array{ items: array[], gallery_ids: int[] }
 */
function etheme_collect_media_from_blocks( $blocks ) {
	$items       = array();
	$gallery_ids = array();

	foreach ( $blocks as $block ) {
		if ( empty( $block['blockName'] ) ) {
			continue;
		}
		$items       = etheme_collect_block_media( $block, $items, $gallery_ids );
		$gallery_ids = etheme_collect_gallery_ids( $block, $gallery_ids );

		if ( ! empty( $block['innerBlocks'] ) ) {
			$inner       = etheme_collect_media_from_blocks( $block['innerBlocks'] );
			$items       = array_merge( $items, $inner['items'] );
			$gallery_ids = array_merge( $gallery_ids, $inner['gallery_ids'] );
		}
	}

	return array( 'items' => $items, 'gallery_ids' => $gallery_ids );
}

/**
 * Collect media from a single block, appending to $items.
 *
 * @param array  $block       Parsed block.
 * @param array  $items       Existing items.
 * @param array  $gallery_ids Already-collected gallery attachment IDs.
 * @return array Updated items.
 */
function etheme_collect_block_media( $block, $items, $gallery_ids ) {
	$name = $block['blockName'];

	if ( 'core/gallery' === $name && ! empty( $block['attrs']['ids'] ) ) {
		foreach ( $block['attrs']['ids'] as $id ) {
			$items[] = array( 'type' => 'image', 'id' => (int) $id );
		}
	}
	if ( 'core/image' === $name && ! empty( $block['attrs']['id'] ) ) {
		$items[] = array( 'type' => 'image', 'id' => (int) $block['attrs']['id'] );
	}
	if ( 'core/video' === $name ) {
		$video_url = '';
		if ( ! empty( $block['attrs']['id'] ) ) {
			$video_url = wp_get_attachment_url( (int) $block['attrs']['id'] );
		}
		if ( ( '' === $video_url || ! $video_url ) && ! empty( $block['attrs']['src'] ) ) {
			$video_url = $block['attrs']['src'];
		}
		if ( ( '' === $video_url || ! $video_url ) && ! empty( $block['innerHTML'] ) && preg_match( '/<source\s[^>]*\ssrc=["\']([^"\']+)["\']/', $block['innerHTML'], $m ) ) {
			$video_url = $m[1];
		}
		if ( $video_url ) {
			$items[] = array( 'type' => 'video', 'url' => esc_url_raw( $video_url ) );
		}
	}
	if ( 'core/embed' === $name && ! empty( $block['attrs']['url'] ) ) {
		$items[] = array( 'type' => 'embed', 'url' => esc_url_raw( $block['attrs']['url'] ) );
	}

	return $items;
}

/**
 * Collect attachment IDs from a gallery block (for featured-image deduplication).
 *
 * @param array $block       Parsed block.
 * @param array $gallery_ids Existing IDs.
 * @return array Updated IDs.
 */
function etheme_collect_gallery_ids( $block, $gallery_ids ) {
	if ( 'core/gallery' === $block['blockName'] && ! empty( $block['attrs']['ids'] ) ) {
		foreach ( $block['attrs']['ids'] as $id ) {
			$gallery_ids[] = (int) $id;
		}
	}
	return $gallery_ids;
}

/**
 * Append the featured image after gallery images (if not already included).
 *
 * @param WP_Post $post        Post object.
 * @param array   $items       Current items.
 * @param array   $gallery_ids Gallery attachment IDs already in $items.
 * @return array Updated items.
 */
function etheme_append_featured_image( $post, $items, $gallery_ids ) {
	$thumb_id = (int) get_post_thumbnail_id( $post->ID );
	if ( ! $thumb_id ) {
		return $items;
	}
	if ( in_array( $thumb_id, $gallery_ids, true ) ) {
		return $items;
	}

	$featured = array( 'type' => 'image', 'id' => $thumb_id );

	if ( empty( $items ) ) {
		return array( $featured );
	}

	$images = array_values( array_filter( $items, fn( $i ) => 'image' === $i['type'] ) );
	$others = array_values( array_filter( $items, fn( $i ) => 'image' !== $i['type'] ) );

	return array_merge( $images, array( $featured ), $others );
}

/**
 * Get multimedia items for display.
 *
 * For social_post: meta images/videos are merged with block-based media.
 * Order: meta images → meta videos → block/featured images (not in meta) → block videos/embeds.
 *
 * @param WP_Post $post Post object.
 * @return array[] Multimedia items.
 */
function etheme_get_post_multimedia_for_display( $post ) {
	$meta_images = etheme_get_social_post_images_meta( $post );
	$meta_videos = etheme_get_social_post_videos_meta( $post );

	$items = array();
	foreach ( $meta_images as $id ) {
		if ( $id > 0 ) {
			$items[] = array( 'type' => 'image', 'id' => $id );
		}
	}
	foreach ( $meta_videos as $url ) {
		if ( $url !== '' ) {
			$items[] = array( 'type' => 'video', 'url' => $url );
		}
	}

	if ( get_post_type( $post->ID ) === 'social_post' && ( ! empty( $meta_images ) || ! empty( $meta_videos ) ) ) {
		$from_blocks = etheme_get_post_multimedia( $post );
		$used_ids    = array_flip( $meta_images );
		$used_urls   = array_flip( $meta_videos );
		foreach ( $from_blocks as $item ) {
			if ( $item['type'] === 'image' && ! isset( $used_ids[ $item['id'] ] ) ) {
				$items[]             = $item;
				$used_ids[ $item['id'] ] = true;
			}
			if ( ( $item['type'] === 'video' || $item['type'] === 'embed' ) && ! isset( $used_urls[ $item['url'] ] ) ) {
				$items[]               = $item;
				$used_urls[ $item['url'] ] = true;
			}
		}
		return $items;
	}

	return etheme_get_post_multimedia( $post );
}

// ─── Blog card data helpers ────────────────────────────────────────────────────

/**
 * Short description for blog card (12 words + "..."). Uses social post meta when available.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function etheme_get_blog_card_description_short( $post ) {
	$custom = etheme_get_social_post_description( $post );
	if ( $custom !== '' ) {
		return wp_trim_words( $custom, 12 ) . '...';
	}
	return wp_trim_words( get_the_excerpt( $post ) ?: wp_trim_words( $post->post_content, 40 ), 12 ) . '...';
}

/**
 * Full description for blog card modal. Uses social post meta when available.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function etheme_get_blog_card_description_full( $post ) {
	$custom = etheme_get_social_post_description( $post );
	if ( $custom !== '' ) {
		return $custom;
	}
	return get_the_excerpt( $post ) ?: wp_trim_words( $post->post_content, 80 );
}

/**
 * Date label for display (e.g. "NOV 08, 2024"). Uses social post date meta when set.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function etheme_get_blog_card_date_label( $post ) {
	$meta_date = etheme_get_social_post_date_meta( $post );
	if ( $meta_date !== '' ) {
		$ts = strtotime( $meta_date );
		return $ts ? strtoupper( gmdate( 'M d, Y', $ts ) ) : strtoupper( get_the_date( 'M d, Y', $post ) );
	}
	return strtoupper( get_the_date( 'M d, Y', $post ) );
}

/**
 * ISO datetime for time element. Uses social post date meta when set.
 *
 * @param WP_Post $post Post object.
 * @return string
 */
function etheme_get_blog_card_datetime( $post ) {
	$meta_date = etheme_get_social_post_date_meta( $post );
	if ( $meta_date !== '' ) {
		$ts = strtotime( $meta_date );
		return $ts ? gmdate( 'c', $ts ) : get_the_date( 'c', $post );
	}
	return get_the_date( 'c', $post );
}

// ─── Social posts queries ─────────────────────────────────────────────────────

/**
 * Get social posts (CPT social_post) ordered by date, newest first.
 *
 * @param int $count  Number of posts to retrieve.
 * @param int $offset Number of posts to skip (for pagination/load more).
 * @return WP_Post[]
 */
function etheme_get_social_posts( int $count = 15, int $offset = 0, string $category_slug = '' ): array {
	$count  = max( 1, absint( $count ) );
	$offset = max( 0, absint( $offset ) );

	$args = array(
		'post_type'      => 'social_post',
		'posts_per_page' => $count,
		'offset'         => $offset,
		'meta_key'       => 'social_post_date',
		'orderby'        => 'meta_value',
		'meta_type'      => 'DATE',
		'order'          => 'DESC',
		'post_status'    => 'publish',
	);

	if ( $category_slug !== '' ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'posteo_category',
				'field'    => 'slug',
				'terms'    => sanitize_key( $category_slug ),
			),
		);
	}

	return get_posts( $args );
}

/**
 * Get recent social posts for home blog section. Thin wrapper over etheme_get_social_posts().
 *
 * @param int $count Number of posts.
 * @return WP_Post[]
 */
function etheme_get_home_social_posts( $count = 3 ) {
	return etheme_get_social_posts( $count, 0 );
}
