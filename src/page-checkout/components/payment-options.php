<?php
/**
 * Payment options component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render payment options section.
 *
 * @return void
 */
function etheme_render_checkout_payment_options() {
	$available_gateways = etheme_checkout_get_available_gateways();
	?>
	<section class="rounded-2xl border border-gray-200 bg-white p-6 md:p-7" aria-labelledby="checkout-payment-options">
		<h2 id="checkout-payment-options" class="text-xl font-bold text-gray-900">
			<?php esc_html_e( 'Payment options', 'etheme' ); ?>
		</h2>
		<p class="mt-1 mb-4 text-sm text-gray-500">
			<?php esc_html_e( 'Choose how you want to pay for your order.', 'etheme' ); ?>
		</p>

		<?php if ( empty( $available_gateways ) ) : ?>
			<p class="mt-5 rounded-md border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-600">
				<?php esc_html_e( 'No payment methods are currently available for your order.', 'etheme' ); ?>
			</p>
		<?php else : ?>
			<div class="mt-5 etheme-payment-content">
				<?php woocommerce_checkout_payment(); ?>
			</div>
		<?php endif; ?>
	</section>
	<?php
}
