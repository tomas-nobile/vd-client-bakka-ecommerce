<?php
// home-blog-card.
/**
 * Home Blog Card Component — Instagram style
 *
 * Renders a single blog post card: multimedia at top (carousel if multiple),
 * absolute date badge, short excerpt below. No title. Clicking opens a modal.
 * Data attributes carry the full media JSON + full description for JS.
 *
 * @param WP_Post $post Post object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main card renderer.
 *
 * @param WP_Post $post Post object.
 */
function etheme_render_home_blog_card( $post ) {
	$multimedia = etheme_get_post_multimedia( $post );
	$short_desc = wp_trim_words( get_the_excerpt( $post ) ?: wp_trim_words( $post->post_content, 40 ), 12 ) . '...';
	$full_desc  = get_the_excerpt( $post ) ?: wp_trim_words( $post->post_content, 80 );
	$datetime   = get_the_date( 'c', $post );
	$date_label = strtoupper( get_the_date( 'M d, Y', $post ) );
	$media_json = esc_attr( wp_json_encode( etheme_build_blog_card_media_data( $multimedia ) ) );
	$aria_lbl   = esc_attr( sprintf( __( 'Ver post del %s', 'etheme' ), $date_label ) );
	?>

	<article
		class="blog-insta-card"
		role="button"
		tabindex="0"
		aria-label="<?php echo $aria_lbl; ?>"
		data-blog-card
		data-post-id="<?php echo esc_attr( $post->ID ); ?>"
		data-description="<?php echo esc_attr( $full_desc ); ?>"
		data-media="<?php echo $media_json; ?>"
	>
		<div class="blog-insta-card__media">
			<?php etheme_render_blog_card_media_area( $multimedia, $post ); ?>
			<time class="blog-insta-card__date" datetime="<?php echo esc_attr( $datetime ); ?>">
				<?php echo esc_html( $date_label ); ?>
			</time>
		</div>

		<div class="blog-insta-card__body">
			<p class="blog-insta-card__excerpt"><?php echo esc_html( $short_desc ); ?></p>
		</div>
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
				'class' => 'blog-insta-card__img',
				'alt'   => esc_attr( get_the_title( $post ) ),
			)
		);
		return;
	}
	if ( 'video' === $item['type'] ) {
		printf(
			'<video class="blog-insta-card__video" controls preload="metadata"><source src="%s"></video>',
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
			$data[] = array( 'type' => $item['type'], 'url' => $item['url'] );
		}
	}
	return $data;
}
