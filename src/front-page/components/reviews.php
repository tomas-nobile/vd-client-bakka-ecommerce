<?php
// home-reviews.
/**
 * Home Reviews / Testimonials Component
 *
 * Displays reviews from CPT etheme_review (not WooCommerce product reviews).
 * Uses ACF fields: review_client_name, review_client_role, review_rating, review_avatar.
 *
 * @param array $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_home_reviews( $attributes ) {
	$count    = absint( $attributes['reviewsCount'] );
	$order_by = $attributes['reviewsOrderBy'];
	$reviews  = etheme_get_home_reviews( $count, $order_by );

	if ( empty( $reviews ) ) {
		return;
	}
	?>

	<section class="py-16 md:py-24 bg-white" aria-labelledby="reviews-heading">
		<div class="container mx-auto px-6 md:px-12 lg:px-20">
			<div class="text-center mb-12" data-aos="fade-up">
				<p class="text-sm uppercase tracking-widest text-stone-500 font-semibold mb-2">
					<?php esc_html_e( 'Testimonios', 'etheme' ); ?>
				</p>
				<h2 id="reviews-heading" class="text-2xl md:text-3xl font-bold text-stone-900">
					<?php esc_html_e( 'Lo Que Dicen Nuestros Clientes', 'etheme' ); ?>
				</h2>
			</div>
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8" data-aos="fade-up">
				<?php foreach ( $reviews as $review ) :
					etheme_render_home_review_card( $review );
				endforeach; ?>
			</div>
		</div>
	</section>

	<?php
}

function etheme_render_home_review_card( $review ) {
	$client_name = etheme_get_review_field( 'review_client_name', $review->ID );
	$client_role = etheme_get_review_field( 'review_client_role', $review->ID );
	$rating      = etheme_get_review_field( 'review_rating', $review->ID );
	$avatar_id   = etheme_get_review_field( 'review_avatar', $review->ID );
	$content     = get_the_excerpt( $review ) ?: wp_trim_words( $review->post_content, 30 );

	if ( ! $client_name ) {
		$client_name = get_the_title( $review );
	}
	if ( ! $rating ) {
		$rating = 5;
	}
	?>

	<article class="bg-stone-50 rounded-xl p-6 md:p-8 flex flex-col">
		<div class="mb-4 text-stone-300">
			<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
				<path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10H14.017zM0 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151C7.546 6.068 5.983 8.789 5.983 11h4v10H0z"/>
			</svg>
		</div>

		<p class="text-stone-600 text-sm leading-relaxed mb-6 flex-1">
			<?php echo esc_html( $content ); ?>
		</p>

		<div class="mb-4">
			<?php echo etheme_render_stars( $rating ); ?>
		</div>

		<div class="flex items-center gap-3">
			<?php etheme_render_review_avatar( $avatar_id, $client_name ); ?>
			<div>
				<p class="font-semibold text-stone-900 text-sm"><?php echo esc_html( $client_name ); ?></p>
				<?php if ( $client_role ) : ?>
				<p class="text-stone-500 text-xs"><?php echo esc_html( $client_role ); ?></p>
				<?php endif; ?>
			</div>
		</div>
	</article>

	<?php
}

function etheme_render_review_avatar( $avatar_id, $name ) {
	if ( $avatar_id ) {
		echo wp_get_attachment_image( $avatar_id, 'thumbnail', false, array(
			'class' => 'w-10 h-10 rounded-full object-cover',
			'alt'   => esc_attr( $name ),
		) );
		return;
	}
	?>
	<div class="w-10 h-10 rounded-full bg-stone-300 flex items-center justify-center text-stone-600 font-bold text-sm" aria-hidden="true">
		<?php echo esc_html( mb_substr( $name, 0, 1 ) ); ?>
	</div>
	<?php
}
