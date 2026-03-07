<?php
// home-blog-card.
/**
 * Home Blog Card Component
 *
 * Renders a single blog post card for the front page article section.
 * Receives a WP_Post object — performs no queries of its own.
 *
 * @param WP_Post $post Post object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_home_blog_card( $post ) {
	$permalink    = get_permalink( $post );
	$title        = get_the_title( $post );
	$excerpt      = get_the_excerpt( $post ) ?: wp_trim_words( $post->post_content, 20 );
	$thumbnail_id = get_post_thumbnail_id( $post->ID );

	// Badge: "NOV 08, 2024" format.
	$date_badge = strtoupper( get_the_date( 'M d, Y', $post ) );
	$datetime   = get_the_date( 'c', $post );
	?>

	<article class="article-box">
		<div class="image position-relative">
			<figure class="article-image">
				<?php if ( $thumbnail_id ) :
					echo wp_get_attachment_image(
						$thumbnail_id,
						'medium_large',
						false,
						array(
							'class' => 'w-full',
							'alt'   => esc_attr( $title ),
						)
					);
				else : ?>
					<div class="article-image__placeholder" aria-hidden="true"></div>
				<?php endif; ?>
			</figure>
			<time class="date" datetime="<?php echo esc_attr( $datetime ); ?>">
				<?php echo esc_html( $date_badge ); ?>
			</time>
		</div>
		<div class="box-content">
			<a href="<?php echo esc_url( $permalink ); ?>" class="text-decoration-none">
				<h3><?php echo esc_html( $title ); ?></h3>
			</a>
			<p class="text-size-16 mb-0"><?php echo esc_html( $excerpt ); ?></p>
		</div>
	</article>

	<?php
}
