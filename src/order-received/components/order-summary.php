<?php
/**
 * Order Received — Order Summary component.
 *
 * @param WC_Order $order Order instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'etheme_render_order_received_summary' ) ) {
	function etheme_render_order_received_summary( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$order_number   = $order->get_order_number();
		$date_created   = $order->get_date_created();
		$formatted_date = $date_created ? wc_format_datetime( $date_created ) : '';
		$billing_email  = $order->get_billing_email();
		$total_html     = $order->get_formatted_order_total();
		$payment_method = $order->get_payment_method_title();

		$rows = array(
			array(
				'label' => __( 'Número de pedido', 'etheme' ),
				'value' => '#' . $order_number,
				'html'  => false,
			),
			array(
				'label' => __( 'Fecha', 'etheme' ),
				'value' => $formatted_date,
				'html'  => false,
			),
			array(
				'label' => __( 'Email', 'etheme' ),
				'value' => $billing_email,
				'html'  => false,
			),
			array(
				'label' => __( 'Total', 'etheme' ),
				'value' => $total_html,
				'html'  => true,
			),
			array(
				'label' => __( 'Método de pago', 'etheme' ),
				'value' => $payment_method,
				'html'  => false,
			),
		);
		?>
		<section class="order-received-summary" data-aos="fade-up" data-aos-delay="100">
			<h2 class="order-received-summary__title">
				<?php esc_html_e( 'Resumen del pedido', 'etheme' ); ?>
			</h2>
			<dl class="order-received-summary__list">
				<?php foreach ( $rows as $row ) : ?>
					<?php if ( empty( $row['value'] ) ) { continue; } ?>
					<div class="order-received-summary__row">
						<dt class="order-received-summary__label"><?php echo esc_html( $row['label'] ); ?></dt>
						<dd class="order-received-summary__value">
							<?php
							if ( ! empty( $row['html'] ) ) {
								echo wp_kses_post( $row['value'] );
							} else {
								echo esc_html( $row['value'] );
							}
							?>
						</dd>
					</div>
				<?php endforeach; ?>
			</dl>
		</section>
		<?php
	}
}
