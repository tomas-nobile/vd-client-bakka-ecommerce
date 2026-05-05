<?php
/**
 * Customer processing order email (C1) — payment received / preparing order.
 *
 * @param WC_Order $order         Order object.
 * @param bool     $sent_to_admin Admin mail?
 * @param bool     $plain_text    Plain-text?
 * @param string   $email         Email id.
 */

defined( 'ABSPATH' ) || exit;

$etheme_config      = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
$etheme_prep_time   = isset( $etheme_config['shipping']['preparationTime'] ) ? (string) $etheme_config['shipping']['preparationTime'] : '3 a 5 días hábiles';
$etheme_contact_mail = isset( $etheme_config['contact']['email'] ) ? (string) $etheme_config['contact']['email'] : '';

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p class="etheme-email-body" style="margin:0 0 16px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#333333;">
	<?php
	printf(
		/* translators: %s: customer first name */
		esc_html__( '¡Gracias por tu compra, %s!', 'etheme' ),
		esc_html( $order->get_billing_first_name() )
	);
	?>
</p>

<p class="etheme-email-body" style="margin:0 0 16px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#333333;">
	<?php esc_html_e( 'Recibimos tu pedido y el pago está confirmado. Ya lo estamos preparando para enviártelo.', 'etheme' ); ?>
</p>


<?php

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

$view_url = $order->get_view_order_url();
if ( $view_url && $order->get_user_id() ) : ?>
	<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
		<tr>
			<td align="center">
				<a href="<?php echo esc_url( $view_url ); ?>" style="display:inline-block;background-color:#fb704f;color:#ffffff;text-decoration:none;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;font-weight:600;padding:14px 32px;border-radius:0;">
					<?php esc_html_e( 'Ver mi pedido', 'etheme' ); ?>
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
		esc_html__( 'Si tenés dudas sobre tu pedido, escribinos a %s.', 'etheme' ),
		'<a href="' . esc_url( 'mailto:' . $etheme_contact_mail ) . '" style="color:#fb704f;text-decoration:underline;">' . esc_html( $etheme_contact_mail ) . '</a>'
	);
	?>
</p>
<?php endif; ?>

<?php
do_action( 'woocommerce_email_footer', $email );
