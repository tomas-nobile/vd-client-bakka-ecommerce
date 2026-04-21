<?php
/**
 * Place order component.
 * Button styled as Contrive .submit_now (accent bg, hover dark-cyan, uppercase).
 * Includes legal terms text above the button (equiv. Contrive p.text-center + .terms-btn).
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build legal terms HTML string.
 * Uses modal triggers so content is shown inline without navigating away.
 *
 * @return string Safe HTML for inline display.
 */
function etheme_checkout_legal_terms_html() {
	$terms_label   = esc_html__( 'Términos y Condiciones', 'etheme' );
	$privacy_label = esc_html__( 'Política de Privacidad', 'etheme' );

	$terms_btn = sprintf(
		'<button type="button" class="checkout-legal-trigger" data-legal-trigger="terms" aria-haspopup="dialog">%s</button>',
		$terms_label
	);

	$privacy_btn = sprintf(
		'<button type="button" class="checkout-legal-trigger" data-legal-trigger="privacy" aria-haspopup="dialog">%s</button>',
		$privacy_label
	);

	return sprintf(
		/* translators: 1: terms button, 2: privacy button */
		__( 'Al hacer clic, aceptás nuestros %1$s y la %2$s.', 'etheme' ),
		$terms_btn,
		$privacy_btn
	);
}

/**
 * Render place order button and security note.
 *
 * @return void
 */
function etheme_render_checkout_place_order() {
	$order_button_text = apply_filters( 'woocommerce_order_button_text', __( 'Pagar ahora', 'etheme' ) );
	$button_html       = sprintf(
		'<button type="submit" class="checkout-place-order-btn" name="woocommerce_checkout_place_order" id="place_order" value="%1$s" data-value="%1$s">%2$s</button>',
		esc_attr( $order_button_text ),
		esc_html( $order_button_text )
	);
	?>
	<div class="checkout-place-order-box" data-aos="fade-up" data-aos-delay="100">
		<div class="mb-4 flex items-center gap-2 text-xs" style="color: var(--co-text);">
			<svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 20 20" fill="none" aria-hidden="true">
				<rect x="3" y="8" width="14" height="10" rx="0" stroke="currentColor" stroke-width="1.5"/>
				<path d="M7 8V6a3 3 0 016 0v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/>
			</svg>
			<?php esc_html_e( 'Pago seguro con encriptación SSL.', 'etheme' ); ?>
		</div>

		<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

		<p class="checkout-legal-terms">
			<?php
			echo wp_kses(
				etheme_checkout_legal_terms_html(),
				array(
					'button' => array(
						'type'           => true,
						'class'          => true,
						'aria-haspopup'  => true,
						'data-legal-trigger' => true,
					),
				)
			);
			?>
		</p>

		<div class="etheme-place-order-wrap">
			<?php echo apply_filters( 'woocommerce_order_button_html', $button_html ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
		</div>

		<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
	</div>
	<?php
}
