<?php
// core/blog-card.
/**
 * Blog Card Component — Instagram style (core, reusable)
 *
 * Renders a single social post card: multimedia at top (carousel if multiple),
 * absolute date badge, short excerpt below. No title. Clicking opens a shared modal.
 * Data attributes carry the full media JSON + full description for JS.
 *
 * Used by: front-page-index (home blog section), page-trabajos-realizados-index.
 *
 * Requires: social-posts.helpers.php (loaded by the block render.php before this file).
 *
 * @param WP_Post $post Post object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render a single blog card (Instagram style).
 *
 * @param WP_Post $post Post object.
 */
function etheme_render_home_blog_card( $post ) {
	$multimedia  = etheme_get_post_multimedia_for_display( $post );
	$short_desc  = etheme_get_blog_card_description_short( $post );
	$full_desc   = etheme_get_blog_card_description_full( $post );
	$datetime    = etheme_get_blog_card_datetime( $post );
	$date_label  = etheme_get_blog_card_date_label( $post );
	$media_json  = esc_attr( wp_json_encode( etheme_build_blog_card_media_data( $multimedia ) ) );
	$aria_lbl    = esc_attr( sprintf( __( 'Ver post del %s', 'etheme' ), $date_label ) );
	$network     = etheme_get_social_post_network( $post );
	$social_cfg  = etheme_get_social_network_config( $network );
	$handle      = isset( $social_cfg['handle'] ) ? $social_cfg['handle'] : '';
	$profile_url = isset( $social_cfg['url'] ) ? $social_cfg['url'] : '';
	$icon_url    = isset( $social_cfg['icon'] ) ? $social_cfg['icon'] : '';
	$post_link   = get_post_meta( $post->ID, ETHEME_SOCIAL_POST_META_LINK, true );
	$social_url  = $post_link ? $post_link : $profile_url;
	?>

	<article
		class="blog-insta-card"
		role="button"
		tabindex="0"
		aria-label="<?php echo $aria_lbl; ?>"
		data-blog-card
		data-post-id="<?php echo esc_attr( $post->ID ); ?>"
		data-description="<?php echo esc_attr( $full_desc ); ?>"
		data-date="<?php echo esc_attr( $date_label ); ?>"
		data-datetime="<?php echo esc_attr( $datetime ); ?>"
		data-media="<?php echo $media_json; ?>"
		data-social-handle="<?php echo esc_attr( $handle ); ?>"
		data-social-url="<?php echo esc_url( $social_url ); ?>"
		data-social-icon="<?php echo esc_url( $icon_url ); ?>"
	>
		<div class="blog-insta-card__media">
			<?php etheme_render_blog_card_media_area( $multimedia, $post ); ?>
		</div>

		<div class="blog-insta-card__body">
			<div class="blog-insta-card__caption-row">
				<p class="blog-insta-card__excerpt">
					<?php if ( $handle ) : ?>
						<strong class="blog-insta-card__instagram-handle">@<?php echo esc_html( $handle ); ?></strong>
						&nbsp;
					<?php endif; ?>
					<?php echo esc_html( $short_desc ); ?>
				</p>
			</div>
			<time class="blog-insta-card__date" datetime="<?php echo esc_attr( $datetime ); ?>">
				<?php echo esc_html( $date_label ); ?>
			</time>
		</div>

		<?php if ( $social_url && $icon_url ) : ?>
			<a
				href="<?php echo esc_url( $social_url ); ?>"
				class="blog-insta-card__instagram-link"
				target="_blank"
				rel="noopener noreferrer"
				aria-label="<?php echo esc_attr( sprintf( __( 'Ir al post de %s', 'etheme' ), $handle ? '@' . $handle : $network ) ); ?>"
				data-social-link
			>
				<img src="<?php echo esc_url( $icon_url ); ?>" class="blog-insta-card__instagram-icon" width="24" height="24" alt="" aria-hidden="true" />
			</a>
		<?php endif; ?>
	</article>

	<?php
}

/**
 * Render the media area: carousel (multiple) or single item.
 *
 * @param array   $multimedia Multimedia items array.
 * @param WP_Post $post       Post object.
 */
function etheme_render_blog_card_media_area( $multimedia, $post ) {
	if ( count( $multimedia ) > 1 ) {
		etheme_render_blog_card_carousel( $multimedia, $post );
		return;
	}
	if ( ! empty( $multimedia ) ) {
		etheme_render_blog_card_media_item( $multimedia[0], $post );
	} else {
		echo '<div class="blog-insta-card__img-placeholder" aria-hidden="true"></div>';
	}
}

/**
 * Render a carousel of multiple media items with prev/next arrows.
 *
 * @param array   $multimedia Multimedia items.
 * @param WP_Post $post       Post object.
 */
function etheme_render_blog_card_carousel( $multimedia, $post ) {
	?>
	<div class="blog-insta-card__carousel" aria-label="<?php esc_attr_e( 'Imágenes del post', 'etheme' ); ?>">
		<?php foreach ( $multimedia as $i => $item ) : ?>
			<div class="blog-insta-card__slide<?php echo 0 === $i ? ' is-active' : ''; ?>">
				<?php etheme_render_blog_card_media_item( $item, $post ); ?>
			</div>
		<?php endforeach; ?>

		<button type="button" class="blog-insta-card__arrow blog-insta-card__arrow--prev" data-carousel-prev
			aria-label="<?php esc_attr_e( 'Anterior', 'etheme' ); ?>">
			<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
			</svg>
		</button>
		<button type="button" class="blog-insta-card__arrow blog-insta-card__arrow--next" data-carousel-next
			aria-label="<?php esc_attr_e( 'Siguiente', 'etheme' ); ?>">
			<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
			</svg>
		</button>

		<div class="blog-insta-card__dots" aria-hidden="true">
			<?php foreach ( $multimedia as $i => $_ ) : ?>
				<span class="blog-insta-card__dot<?php echo 0 === $i ? ' is-active' : ''; ?>"></span>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Render a single media item: image, video, or placeholder.
 *
 * @param array   $item Media item array.
 * @param WP_Post $post Post object.
 */
function etheme_render_blog_card_media_item( $item, $post ) {
	if ( 'image' === $item['type'] ) {
		echo wp_get_attachment_image(
			$item['id'],
			'medium_large',
			false,
			array(
				'class'    => 'blog-insta-card__img',
				'alt'      => esc_attr( get_the_title( $post ) ),
				'loading'  => 'lazy',
				'decoding' => 'async',
			)
		);
		return;
	}
	if ( 'video' === $item['type'] ) {
		$poster = ! empty( $item['thumbnail_id'] )
			? wp_get_attachment_image_url( (int) $item['thumbnail_id'], 'medium_large' )
			: '';
		printf(
			'<video class="blog-insta-card__video" controls preload="none"%s><source src="%s"></video>',
			$poster ? ' poster="' . esc_url( $poster ) . '"' : '',
			esc_url( $item['url'] )
		);
		return;
	}
	echo '<div class="blog-insta-card__img-placeholder" aria-hidden="true"></div>';
}

/**
 * Build the JSON-safe media array for data-media attribute (consumed by JS modal).
 *
 * @param array $multimedia Multimedia items.
 * @return array[] JS-safe items.
 */
function etheme_build_blog_card_media_data( $multimedia ) {
	$data = array();
	foreach ( $multimedia as $item ) {
		if ( 'image' === $item['type'] ) {
			$data[] = array(
				'type'   => 'image',
				'src'    => (string) wp_get_attachment_image_url( $item['id'], 'large' ),
				'srcset' => (string) ( wp_get_attachment_image_srcset( $item['id'], 'large' ) ?: '' ),
				'alt'    => (string) get_post_meta( $item['id'], '_wp_attachment_image_alt', true ),
			);
		} elseif ( 'video' === $item['type'] || 'embed' === $item['type'] ) {
			$entry = array( 'type' => $item['type'], 'url' => $item['url'] );
			if ( ! empty( $item['thumbnail_id'] ) ) {
				$entry['poster'] = (string) ( wp_get_attachment_image_url( (int) $item['thumbnail_id'], 'large' ) ?: '' );
			}
			$data[] = $entry;
		}
	}
	return $data;
}
