<?php
/**
 * Customer completed order email (C2) — order delivered.
 *
 * @param WC_Order $order         Order object.
 * @param bool     $sent_to_admin Admin mail?
 * @param bool     $plain_text    Plain-text?
 * @param string   $email         Email id.
 */

defined( 'ABSPATH' ) || exit;

$etheme_config      = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
$etheme_contact_mail = isset( $etheme_config['contact']['email'] ) ? (string) $etheme_config['contact']['email'] : '';

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p class="etheme-email-body" style="margin:0 0 16px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#333333;">
	<?php esc_html_e( 'Te confirmamos que tu pedido fue entregado. Esperamos que disfrutes tu compra.', 'etheme' ); ?>
</p>

<?php

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

if ( $etheme_contact_mail ) : ?>
<p class="etheme-email-body" style="margin:24px 0 0 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#666666;">
	<?php
	printf(
		/* translators: %s: contact email link */
		esc_html__( 'Si algo no salió como esperabas, escribinos a %s. Nos importa.', 'etheme' ),
		'<a href="' . esc_url( 'mailto:' . $etheme_contact_mail ) . '" style="color:#fb704f;text-decoration:underline;">' . esc_html( $etheme_contact_mail ) . '</a>'
	);
	?>
</p>
<?php endif; ?>

<?php
do_action( 'woocommerce_email_footer', $email );
