<?php
/**
 * Customer on-hold order email (C4) — payment pending confirmation.
 *
 * @param WC_Order $order         Order object.
 * @param bool     $sent_to_admin Admin mail?
 * @param bool     $plain_text    Plain-text?
 * @param string   $email         Email id.
 */

defined( 'ABSPATH' ) || exit;

$etheme_config       = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
$etheme_contact_mail = isset( $etheme_config['contact']['email'] ) ? (string) $etheme_config['contact']['email'] : '';

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p class="etheme-email-body" style="margin:0 0 16px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#333333;">
	<?php
	printf(
		/* translators: %s: customer first name */
		esc_html__( 'Hola %s, recibimos tu pedido.', 'etheme' ),
		esc_html( $order->get_billing_first_name() )
	);
	?>
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px 0;background-color:#fffbf0;border:1px solid #facc15;">
	<tr>
		<td style="padding:16px;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.5;color:#78350f;">
			<?php esc_html_e( 'Tu pedido está en espera hasta que confirmemos que el pago fue procesado. Te avisaremos por email cuando esté listo.', 'etheme' ); ?>
		</td>
	</tr>
</table>

<p class="etheme-email-body" style="margin:0 0 16px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#333333;">
	<?php esc_html_e( 'Acá te recordamos lo que pediste:', 'etheme' ); ?>
</p>

<?php

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

if ( $etheme_contact_mail ) : ?>
<p class="etheme-email-body" style="margin:24px 0 0 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#666666;">
	<?php
	printf(
		/* translators: %s: contact email link */
		esc_html__( 'Si tenés dudas sobre tu pedido, escribinos a %s.', 'etheme' ),
		'<a href="' . esc_url( 'mailto:' . $etheme_contact_mail ) . '" style="color:#fb704f;text-decoration:underline;">' . esc_html( $etheme_contact_mail ) . '</a>'
	);
	?>
</p>
<?php endif; ?>

<?php
do_action( 'woocommerce_email_footer', $email );
