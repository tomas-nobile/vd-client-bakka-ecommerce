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
	$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Pagar ahora', 'etheme' ) );
	$button_html       = sprintf(
		'<button type="submit" class="etheme-place-order-btn w-full bg-gray-900 px-6 py-4 text-center text-sm font-bold uppercase tracking-wide text-white transition hover:bg-black" name="woocommerce_checkout_place_order" id="place_order" value="%s" data-value="%s">%s</button>',
		esc_attr( $order_button_text ),
		esc_attr( $order_button_text ),
		esc_html( $order_button_text )
	);
	?>
	<section class="border border-gray-200 bg-white p-6" data-aos="fade-up" data-aos-delay="100">
		<div class="mb-4 flex items-center gap-2 text-xs text-gray-500">
			<svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
				<rect x="3" y="8" width="14" height="10" rx="0" stroke="currentColor" stroke-width="1.5"/>
				<path d="M7 8V6a3 3 0 016 0v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
			</svg>
			<?php esc_html_e( 'Pago seguro con encriptación SSL.', 'etheme' ); ?>
		</div>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
		<div class="etheme-place-order-wrap">
			<?php echo apply_filters( 'woocommerce_order_button_html', $button_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</section>
	<?php
}
