<?php
/**
 * Legal terms component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render legal and terms acknowledgement section.
 *
 * @return void
 */
function etheme_render_checkout_legal_terms() {
	$terms_url      = etheme_checkout_get_terms_url();
	$privacy_url    = etheme_checkout_get_privacy_url();
	$terms_required = wc_terms_and_conditions_checkbox_enabled();
	$is_checked     = ! empty( $_POST['terms'] );
	?>
	<section class="rounded-2xl border border-gray-200 bg-white p-6" aria-labelledby="checkout-legal-terms">
		<h2 id="checkout-legal-terms" class="text-lg font-bold text-gray-900">
			<?php esc_html_e( 'Terms and privacy', 'etheme' ); ?>
		</h2>

		<p class="mt-2 text-sm leading-6 text-gray-600">
			<?php esc_html_e( 'By proceeding with your purchase you agree to our', 'etheme' ); ?>
			<a class="font-medium text-gray-900 underline hover:text-black" href="<?php echo esc_url( $terms_url ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Terms and Conditions', 'etheme' ); ?>
			</a>
			<?php esc_html_e( 'and', 'etheme' ); ?>
			<a class="font-medium text-gray-900 underline hover:text-black" href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Privacy Policy', 'etheme' ); ?>
			</a>.
		</p>

		<?php if ( $terms_required ) : ?>
			<label for="terms" class="mt-4 inline-flex items-start gap-2 text-sm text-gray-700">
				<input
					type="checkbox"
					class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900"
					name="terms"
					id="terms"
					value="1"
					<?php checked( $is_checked ); ?>
				/>
				<span><?php esc_html_e( 'I have read and accept the terms and conditions.', 'etheme' ); ?></span>
			</label>
		<?php endif; ?>
	</section>
	<?php
}
