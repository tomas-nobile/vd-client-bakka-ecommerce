<?php
/**
 * Contact Form component.
 *
 * Renders the contact form section with Name, Phone, Email, Message fields.
 * Validation happens client-side (contact-form.js). Backend submission (wp_mail)
 * is a future phase — nonce is output for when that is wired up.
 *
 * @param array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Contact Form section.
 *
 * @param array $attributes Block attributes.
 */
function etheme_render_contact_form( array $attributes ): void {
	$form_endpoint = isset( $attributes['formEndpoint'] ) ? esc_url_raw( $attributes['formEndpoint'] ) : '';
	?>
	<section class="contactform-con">
		<div class="container mx-auto px-4">

			<div class="contactform_content text-center" data-aos="fade-up">
				<h6><?php echo esc_html( $attributes['formEyebrow'] ); ?></h6>
				<h2><?php echo esc_html( $attributes['formTitle'] ); ?></h2>
			</div>

			<div class="contact_form" data-aos="fade-up">
				<form
					id="etheme-contact-form"
					method="post"
					data-endpoint="<?php echo esc_url( $form_endpoint ); ?>"
					novalidate
				>
					<?php wp_nonce_field( 'etheme_contact_send', 'etheme_contact_nonce' ); ?>

					<div class="etheme-hp-field" aria-hidden="true" tabindex="-1">
						<label for="website_url"><?php esc_html_e( 'Website', 'etheme' ); ?></label>
						<input type="text" name="website_url" id="website_url" autocomplete="off" tabindex="-1">
					</div>

					<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
						<div class="form-group">
							<input
								type="text"
								class="form_style"
								placeholder="<?php esc_attr_e( 'Name', 'etheme' ); ?>"
								name="contact_name"
								id="contact_name"
							>
							<span class="etheme-field-error" aria-live="polite"></span>
						</div>
						<div class="form-group">
							<input
								type="tel"
								class="form_style"
								placeholder="<?php esc_attr_e( 'Phone', 'etheme' ); ?>"
								name="contact_phone"
								id="contact_phone"
							>
							<span class="etheme-field-error" aria-live="polite"></span>
						</div>
						<div class="form-group">
							<input
								type="email"
								class="form_style"
								placeholder="<?php esc_attr_e( 'Email', 'etheme' ); ?>"
								name="contact_email"
								id="contact_email"
							>
							<span class="etheme-field-error" aria-live="polite"></span>
						</div>
					</div>

					<div class="form-group message">
						<textarea
							class="form_style"
							placeholder="<?php esc_attr_e( 'Message', 'etheme' ); ?>"
							rows="5"
							name="contact_message"
							id="contact_message"
						></textarea>
						<span class="etheme-field-error" aria-live="polite"></span>
					</div>

					<div class="form-group etheme-newsletter-field">
						<label for="contact_newsletter">
							<input type="checkbox" name="contact_newsletter" id="contact_newsletter" value="1">
							<?php esc_html_e( 'I want to receive newsletter updates.', 'etheme' ); ?>
						</label>
					</div>

					<div class="text-center">
						<button type="submit" class="submit_now primary_btn">
							<?php echo esc_html( $attributes['formButtonText'] ); ?>
						</button>
					</div>

					<div class="etheme-form-notice" aria-live="polite"></div>
				</form>
			</div>

		</div>
	</section>
	<?php
}
