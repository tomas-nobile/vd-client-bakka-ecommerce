<?php
/**
 * About page content component.
 *
 * Renders logo + brand description on the left, decorative image on the right,
 * followed by three value cards (what differentiates Bakka Deco).
 *
 * @param array $data About page data from etheme_get_about_data().
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_about_content( $data ) {
	$description = isset( $data['description'] ) ? $data['description'] : '';
	$values      = isset( $data['values'] ) && is_array( $data['values'] ) ? $data['values'] : array();
	$theme_uri   = get_template_directory_uri();
	$logo_src    = esc_url( $theme_uri . '/assets/images/logo-big.webp' );
	?>
	<section class="about-content">
		<div class="container mx-auto px-4">

			<div class="about-content__hero" data-aos="fade-up">
				<div class="about-content__logo-col">
					<div class="about-content__logo-bg">
						<img
							src="<?php echo $logo_src; ?>"
							alt="<?php esc_attr_e( 'Bakka Deco — logo', 'etheme' ); ?>"
							class="about-content__logo"
							width="340"
							height="auto"
							loading="lazy"
						>
					</div>
				</div>
				<div class="about-content__text-col">
					<?php if ( '' !== $description ) : ?>
						<p class="about-content__description"><?php echo esc_html( $description ); ?></p>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( ! empty( $values ) ) : ?>
				<div class="about-content__values" data-aos="fade-up" data-aos-delay="100">
					<?php foreach ( $values as $value ) :
						$v_title = isset( $value['title'] ) ? $value['title'] : '';
						$v_text  = isset( $value['text'] )  ? $value['text']  : '';
						if ( '' === $v_title && '' === $v_text ) {
							continue;
						}
						?>
						<div class="about-content__value-card">
							<?php if ( '' !== $v_title ) : ?>
								<h3 class="about-content__value-title"><?php echo esc_html( $v_title ); ?></h3>
							<?php endif; ?>
							<?php if ( '' !== $v_text ) : ?>
								<p class="about-content__value-text"><?php echo esc_html( $v_text ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

		</div>
	</section>
	<?php
}
