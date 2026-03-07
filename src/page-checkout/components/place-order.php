<?php
/**
 * Place order component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render place order actions.
 *
 * @return void
 */
function etheme_render_checkout_place_order() {
	$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Place order', 'woocommerce' ) );
	$button_html       = sprintf(
		'<button type="submit" class="etheme-place-order-btn w-full rounded-full bg-gray-900 px-6 py-4 text-center text-sm font-bold uppercase tracking-wide text-white transition hover:bg-black" name="woocommerce_checkout_place_order" id="place_order" value="%s" data-value="%s">%s</button>',
		esc_attr( $order_button_text ),
		esc_attr( $order_button_text ),
		esc_html( $order_button_text )
	);
	?>
	<section class="rounded-2xl border border-gray-200 bg-white p-6">
		<p class="mb-4 text-xs text-gray-500">
			<?php esc_html_e( 'Secure checkout powered by SSL encryption.', 'etheme' ); ?>
		</p>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
		<div class="etheme-place-order-wrap">
			<?php echo apply_filters( 'woocommerce_order_button_html', $button_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</section>
	<?php
}
