<?php
/**
 * Contact Info component.
 *
 * Renders three cards: location, phone (link), email.
 *
 * @param array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Contact Info section.
 *
 * @param array $attributes Block attributes.
 */
function etheme_render_contact_info( array $attributes ): void {
	$icon_base = esc_url( get_theme_file_uri( 'assets/images/' ) );

	$wa_url = isset( $attributes['whatsappUrl'] ) ? esc_url_raw( $attributes['whatsappUrl'] ) : '';
	$wa_url = $wa_url ? esc_url( $wa_url ) : '';

	$phone_label = isset( $attributes['phoneLabel'] ) ? trim( (string) $attributes['phoneLabel'] ) : '';
	$phone_href  = '';
	if ( $wa_url ) {
		$phone_href = $wa_url;
	} elseif ( '' !== $phone_label ) {
		$digits = preg_replace( '/\D+/', '', $phone_label );
		if ( '' !== $digits ) {
			$phone_href = 'tel:' . $digits;
		}
	}
	?>
	<section class="contactinfo-con">
		<div class="container mx-auto px-4">

			<div class="contactinfo_content text-center" data-aos="fade-up">
				<h6><?php echo esc_html( $attributes['infoEyebrow'] ); ?></h6>
				<h2><?php echo esc_html( $attributes['infoTitle'] ); ?></h2>
			</div>

			<div class="all_row grid grid-cols-1 md:grid-cols-3 gap-6" data-aos="fade-up">

				<div class="all_column">
					<div class="contact-box all_boxes">
						<figure class="icon">
							<img src="<?php echo $icon_base; ?>contactinfo-icon1.png" alt="<?php esc_attr_e( 'Icono de ubicación', 'etheme' ); ?>" class="img-fluid">
						</figure>
						<h4><?php echo esc_html( $attributes['locationTitle'] ); ?></h4>
						<a href="<?php echo esc_url( $attributes['locationUrl'] ); ?>" class="text-size-16" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $attributes['locationText'] ); ?>
						</a>
					</div>
				</div>

				<div class="all_column">
					<div class="contact-box all_boxes">
						<figure class="icon">
							<img src="<?php echo $icon_base; ?>contactinfo-icon2.png" alt="<?php esc_attr_e( 'Icono de teléfono', 'etheme' ); ?>" class="img-fluid">
						</figure>
						<h4><?php echo esc_html( $attributes['phoneTitle'] ); ?></h4>
						<?php if ( $phone_href && '' !== $phone_label ) : ?>
							<?php if ( 0 === strpos( $phone_href, 'tel:' ) ) : ?>
								<a
									href="<?php echo esc_url( $phone_href ); ?>"
									class="text-size-16 etheme-contact-action"
								>
									<?php echo esc_html( $phone_label ); ?>
								</a>
							<?php else : ?>
								<a
									href="<?php echo esc_url( $phone_href ); ?>"
									class="text-size-16 etheme-contact-action"
									target="_blank"
									rel="noopener noreferrer"
								>
									<?php echo esc_html( $phone_label ); ?>
								</a>
							<?php endif; ?>
						<?php elseif ( '' !== $phone_label ) : ?>
							<span class="text-size-16"><?php echo esc_html( $phone_label ); ?></span>
						<?php endif; ?>
					</div>
				</div>

				<div class="all_column">
					<div class="contact-box all_boxes">
						<figure class="icon">
							<img src="<?php echo $icon_base; ?>contactinfo-icon3.png" alt="<?php esc_attr_e( 'Icono de email', 'etheme' ); ?>" class="img-fluid">
						</figure>
						<h4><?php echo esc_html( $attributes['emailTitle'] ); ?></h4>
						<?php if ( ! empty( $attributes['email'] ) ) : ?>
							<a
								href="mailto:<?php echo esc_attr( $attributes['email'] ); ?>"
								class="text-size-16 etheme-contact-action"
							>
								<?php echo esc_html( $attributes['email'] ); ?>
							</a>
						<?php endif; ?>
					</div>
				</div>

			</div>
		</div>
	</section>
	<?php
}
