<?php
/**
 * Order Received — Hero component.
 *
 * @param WC_Order $order Order instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'etheme_render_order_received_hero' ) ) {
	function etheme_render_order_received_hero( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$billing_email = $order->get_billing_email();
		?>
		<section class="order-received-hero text-center" data-aos="fade-up">
			<figure class="order-received-hero__icon" aria-hidden="true">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" fill="none" focusable="false">
					<circle cx="40" cy="40" r="38" stroke="currentColor" stroke-width="2"/>
					<path d="M25 41.5L35.5 52L56 30" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
				</svg>
			</figure>

			<h1 class="order-received-hero__title">
				<?php esc_html_e( '¡Gracias por tu compra!', 'etheme' ); ?>
			</h1>

			<p class="order-received-hero__text">
				<?php
				if ( ! empty( $billing_email ) ) {
					printf(
						/* translators: %s: buyer email address */
						esc_html__( 'Tu pedido fue recibido correctamente. Te enviamos la confirmación a %s.', 'etheme' ),
						'<strong>' . esc_html( $billing_email ) . '</strong>'
					);
				} else {
					esc_html_e( 'Tu pedido fue recibido correctamente. Pronto recibirás la confirmación por email.', 'etheme' );
				}
				?>
			</p>
		</section>
		<?php
	}
}
