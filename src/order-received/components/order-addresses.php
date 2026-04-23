<?php
/**
 * Order Received — Addresses component (shipping + billing).
 *
 * @param WC_Order $order Order instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'etheme_render_order_received_addresses' ) ) {
	function etheme_render_order_received_addresses( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$show_shipping     = $order->needs_shipping_address();
		$shipping_html     = $show_shipping ? $order->get_formatted_shipping_address() : '';
		$billing_html      = $order->get_formatted_billing_address();
		$shipping_phone    = $order->get_shipping_phone();
		$billing_phone     = $order->get_billing_phone();

		if ( empty( $shipping_html ) && empty( $billing_html ) ) {
			return;
		}
		?>
		<section class="order-received-addresses" data-aos="fade-up" data-aos-delay="200">
			<div class="order-received-addresses__grid">
				<?php if ( $show_shipping && ! empty( $shipping_html ) ) : ?>
					<div class="order-received-addresses__card">
						<h3 class="order-received-addresses__title">
							<?php esc_html_e( 'Dirección de envío', 'etheme' ); ?>
						</h3>
						<address class="order-received-addresses__body">
							<?php echo wp_kses_post( $shipping_html ); ?>
							<?php if ( ! empty( $shipping_phone ) ) : ?>
								<br><?php echo esc_html( $shipping_phone ); ?>
							<?php endif; ?>
						</address>
					</div>
				<?php endif; ?>

				<?php if ( ! empty( $billing_html ) ) : ?>
					<div class="order-received-addresses__card">
						<h3 class="order-received-addresses__title">
							<?php esc_html_e( 'Dirección de facturación', 'etheme' ); ?>
						</h3>
						<address class="order-received-addresses__body">
							<?php echo wp_kses_post( $billing_html ); ?>
							<?php if ( ! empty( $billing_phone ) ) : ?>
								<br><?php echo esc_html( $billing_phone ); ?>
							<?php endif; ?>
						</address>
					</div>
				<?php endif; ?>
			</div>
		</section>
		<?php
	}
}
