<?php
// home-newsletter.
/**
 * Home Newsletter Component
 *
 * Renders the newsletter subscription form — Contrive Update design.
 * Layout: two-column (optional image | content + form).
 * Submission is handled via AJAX (admin-ajax.php, action: etheme_newsletter_subscribe).
 *
 * Extension point for external providers (Mailchimp, etc.):
 *   do_action('etheme_newsletter_after_subscribe', $email)
 *   — see src/front-page/includes/home-newsletter.ajax-handlers.php
 *
 * @param array $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_home_newsletter( $attributes ) {
	$subtitle    = esc_html( $attributes['newsletterSubtitle'] ?? '' );
	$title       = esc_html( $attributes['newsletterTitle'] ?? '' );
	$button_text = esc_html( $attributes['newsletterButtonText'] ?? __( 'Suscribirse', 'etheme' ) );
	$image_id    = absint( $attributes['newsletterImageId'] ?? 0 );
	$theme_uri   = get_template_directory_uri();
	$nonce       = wp_create_nonce( 'etheme_newsletter_nonce' );
	$ajax_url    = esc_url( admin_url( 'admin-ajax.php' ) );
	$main_img    = $image_id > 0
		? wp_get_attachment_image_url( $image_id, 'large' )
		: $theme_uri . '/assets/images/update-image.png';
	$has_image   = (bool) $main_img;
	?>

	<section class="newsletter-section relative" aria-labelledby="newsletter-heading">
		<figure class="newsletter-deco newsletter-deco--left" aria-hidden="true">
			<img src="<?php echo esc_url( $theme_uri . '/assets/images/update-leftimage.png' ); ?>" alt="" class="newsletter-deco__img">
		</figure>
		<figure class="newsletter-deco newsletter-deco--right" aria-hidden="true">
			<img src="<?php echo esc_url( $theme_uri . '/assets/images/update-rightimage.png' ); ?>" alt="" class="newsletter-deco__img">
		</figure>

		<div class="container mx-auto px-4">
			<div class="grid grid-cols-1 <?php echo $has_image ? 'lg:grid-cols-12' : ''; ?> items-center">

				<?php if ( $has_image ) : ?>
					<div class="lg:col-span-5 order-first lg:order-none">
						<div class="newsletter-image-wrapper" data-aos="fade-up">
							<?php if ( $image_id > 0 ) : ?>
								<?php
								echo wp_get_attachment_image(
									$image_id,
									'large',
									false,
									array(
										'class' => 'newsletter-image',
										'alt'   => '',
									)
								);
								?>
							<?php else : ?>
								<img src="<?php echo esc_url( $main_img ); ?>" alt="" class="newsletter-image">
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="<?php echo $has_image ? 'lg:col-span-7' : 'max-w-2xl mx-auto'; ?>">
					<div class="newsletter-content<?php echo $has_image ? ' newsletter-content--with-image' : ''; ?>" data-aos="fade-up">

						<h6><?php echo $subtitle; ?></h6>

						<h2 id="newsletter-heading"><?php echo $title; ?></h2>

						<form
							id="etheme-newsletter-form"
							novalidate
							data-ajax-url="<?php echo $ajax_url; ?>"
							data-nonce="<?php echo esc_attr( $nonce ); ?>"
						>
							<div class="newsletter-form-wrapper">
								<label for="etheme-newsletter-email" class="sr-only">
									<?php esc_html_e( 'Tu email', 'etheme' ); ?>
								</label>
								<input
									type="email"
									id="etheme-newsletter-email"
									name="email"
									required
									placeholder="<?php esc_attr_e( 'Enter Your Email Address', 'etheme' ); ?>"
									aria-describedby="etheme-newsletter-message"
								/>
								<button
									type="submit"
									data-button-text="<?php echo esc_attr( $button_text ); ?>"
								>
									<?php echo $button_text; ?>
								</button>
							</div>
						</form>

						<div id="etheme-newsletter-message" class="newsletter-message" role="status" aria-live="polite"></div>

					</div>
				</div>

			</div>
		</div>
	</section>

	<?php
}
