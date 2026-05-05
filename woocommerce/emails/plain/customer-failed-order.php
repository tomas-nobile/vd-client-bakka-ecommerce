<?php
/**
 * Customer failed order email (C3) — plain text.
 *
 * @param WC_Order $order         Order object.
 * @param bool     $sent_to_admin Admin mail?
 * @param bool     $plain_text    Plain-text?
 * @param string   $email         Email id.
 */

defined( 'ABSPATH' ) || exit;

$etheme_config       = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
$etheme_contact_mail = isset( $etheme_config['contact']['email'] ) ? (string) $etheme_config['contact']['email'] : '';

echo "= " . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";

printf(
	esc_html__( 'Hola %s, lamentamos informarte que no pudimos procesar el pago de tu pedido. Esto puede deberse a fondos insuficientes, datos de tarjeta incorrectos o un rechazo temporal del banco.', 'etheme' ),
	esc_html( $order->get_billing_first_name() )
);
echo "\n\n";

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n----------\n\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

$payment_url = $order->get_checkout_payment_url();
if ( $payment_url ) {
	echo "\n";
	esc_html_e( 'Reintentar pago:', 'etheme' );
	echo "\n" . esc_url_raw( $payment_url ) . "\n";
}

if ( $etheme_contact_mail ) {
	echo "\n";
	printf(
		esc_html__( 'Si el problema persiste, escribinos a %s.', 'etheme' ),
		esc_html( $etheme_contact_mail )
	);
	echo "\n";
}

echo "\n\n----------\n\n";
echo esc_html( wp_strip_all_tags( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) );
