<?php
/**
 * Admin failed order email (A2).
 *
 * @param WC_Order $order         Order object.
 * @param bool     $sent_to_admin Admin mail?
 * @param bool     $plain_text    Plain-text?
 * @param string   $email         Email id.
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 20px 0;background-color:#fff1f0;border:1px solid #ff4d4f;">
	<tr>
		<td style="padding:16px;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.5;color:#a8071a;">
			<strong><?php esc_html_e( 'Acción requerida:', 'etheme' ); ?></strong>
			<?php esc_html_e( 'El pago no se completó. Revisá el pedido en el backoffice.', 'etheme' ); ?>
		</td>
	</tr>
</table>

<p class="etheme-email-body" style="margin:0 0 16px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#333333;">
	<?php
	printf(
		/* translators: %s: order number */
		esc_html__( 'El pedido #%s falló.', 'etheme' ),
		esc_html( $order->get_order_number() )
	);
	?>
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 16px 0;background-color:#f7f7f7;border-left:3px solid #fb704f;">
	<tr>
		<td style="padding:16px;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;line-height:1.6;color:#333333;">
			<strong style="display:block;margin-bottom:4px;"><?php esc_html_e( 'Datos del cliente', 'etheme' ); ?></strong>
			<?php echo esc_html( trim( $order->get_formatted_billing_full_name() ) ); ?><br />
			<a href="<?php echo esc_url( 'mailto:' . $order->get_billing_email() ); ?>" style="color:#fb704f;text-decoration:underline;"><?php echo esc_html( $order->get_billing_email() ); ?></a><br />
			<?php if ( $order->get_billing_phone() ) : ?>
				<?php echo esc_html( $order->get_billing_phone() ); ?>
			<?php endif; ?>
		</td>
	</tr>
</table>

<?php

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
?>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:24px 0;">
	<tr>
		<td align="center">
			<a href="<?php echo esc_url( admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' ) ); ?>" style="display:inline-block;background-color:#fb704f;color:#ffffff;text-decoration:none;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;font-weight:600;padding:14px 32px;border-radius:0;">
				<?php esc_html_e( 'Ver pedido en el backoffice', 'etheme' ); ?>
			</a>
		</td>
	</tr>
</table>

<?php
do_action( 'woocommerce_email_footer', $email );
