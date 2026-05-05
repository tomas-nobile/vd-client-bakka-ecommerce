<?php
/**
 * Customer failed order email (C3) — payment could not be processed.
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

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px 0;background-color:#fff8f0;border:1px solid #fb704f;">
	<tr>
		<td style="padding:16px;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.5;color:#7c3310;">
			<?php esc_html_e( 'Hubo un problema al procesar el pago de tu pedido. Podés intentarlo nuevamente con el botón de abajo.', 'etheme' ); ?>
		</td>
	</tr>
</table>

<p class="etheme-email-body" style="margin:0 0 16px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#333333;">
	<?php
	printf(
		/* translators: %s: customer first name */
		esc_html__( 'Hola %s, lamentamos informarte que no pudimos procesar el pago de tu pedido. Esto puede deberse a fondos insuficientes, datos de tarjeta incorrectos o un rechazo temporal del banco.', 'etheme' ),
		esc_html( $order->get_billing_first_name() )
	);
	?>
</p>

<?php

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

$payment_url = $order->get_checkout_payment_url();
if ( $payment_url ) : ?>
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
		<tr>
			<td align="center">
				<a href="<?php echo esc_url( $payment_url ); ?>" style="display:inline-block;background-color:#fb704f;color:#ffffff;text-decoration:none;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;font-weight:600;padding:14px 32px;border-radius:0;">
					<?php esc_html_e( 'Reintentar pago', 'etheme' ); ?>
				</a>
			</td>
		</tr>
	</table>
<?php endif; ?>

<?php if ( $etheme_contact_mail ) : ?>
<p class="etheme-email-body" style="margin:24px 0 0 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#666666;">
	<?php
	printf(
		/* translators: %s: contact email link */
		esc_html__( 'Si el problema persiste o necesitás ayuda, escribinos a %s.', 'etheme' ),
		'<a href="' . esc_url( 'mailto:' . $etheme_contact_mail ) . '" style="color:#fb704f;text-decoration:underline;">' . esc_html( $etheme_contact_mail ) . '</a>'
	);
	?>
</p>
<?php endif; ?>

<?php
do_action( 'woocommerce_email_footer', $email );
