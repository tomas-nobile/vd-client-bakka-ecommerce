<?php
/**
 * Admin failed order email (A2) — plain text.
 *
 * @param WC_Order $order         Order object.
 * @param bool     $sent_to_admin Admin mail?
 * @param bool     $plain_text    Plain-text?
 * @param string   $email         Email id.
 */

defined( 'ABSPATH' ) || exit;

echo "= " . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";

esc_html_e( 'ACCIÓN REQUERIDA: el pago no se completó. Revisá el pedido en el backoffice.', 'etheme' );
echo "\n\n";

printf( esc_html__( 'El pedido #%s falló.', 'etheme' ), esc_html( $order->get_order_number() ) );
echo "\n\n";

esc_html_e( 'Datos del cliente', 'etheme' );
echo ":\n";
echo esc_html( trim( $order->get_formatted_billing_full_name() ) ) . "\n";
echo esc_html( $order->get_billing_email() ) . "\n";
if ( $order->get_billing_phone() ) {
	echo esc_html( $order->get_billing_phone() ) . "\n";
}

echo "\n----------\n\n";

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n----------\n\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n";
esc_html_e( 'Ver pedido en el backoffice:', 'etheme' );
echo "\n" . esc_url_raw( admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) );

echo "\n\n----------\n\n";
echo esc_html( wp_strip_all_tags( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) );
