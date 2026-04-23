<?php
/**
 * Customer completed order email (C2) — plain text.
 *
 * @param WC_Order $order         Order object.
 * @param bool     $sent_to_admin Admin mail?
 * @param bool     $plain_text    Plain-text?
 * @param string   $email         Email id.
 */

defined( 'ABSPATH' ) || exit;

$etheme_config      = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
$etheme_contact_mail = isset( $etheme_config['contact']['email'] ) ? (string) $etheme_config['contact']['email'] : '';

echo "= " . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";

esc_html_e( 'Te confirmamos que tu pedido fue entregado. Esperamos que disfrutes tu compra.', 'etheme' );
echo "\n\n----------\n\n";

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n----------\n\n";

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

if ( $etheme_contact_mail ) {
	echo "\n";
	printf( esc_html__( 'Si algo no salió como esperabas, escribinos a %s. Nos importa.', 'etheme' ), esc_html( $etheme_contact_mail ) );
	echo "\n";
}

echo "\n\n----------\n\n";
echo esc_html( wp_strip_all_tags( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) );
