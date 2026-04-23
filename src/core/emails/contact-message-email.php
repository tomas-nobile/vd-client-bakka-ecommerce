<?php
/**
 * Contact form notification email (A3) — HTML.
 *
 * Rendered either by WC_Email::style_inline() (via wrapper in class-etheme-contact-message-email.php)
 * or standalone by etheme_render_email() with do_action('woocommerce_email_header'/'woocommerce_email_footer').
 *
 * @param string $email_heading Heading.
 * @param array  $data          Sender data (name, email, phone, message, date).
 */

defined( 'ABSPATH' ) || exit;

$etheme_name    = isset( $data['name'] ) ? (string) $data['name'] : '';
$etheme_email   = isset( $data['email'] ) ? (string) $data['email'] : '';
$etheme_phone   = isset( $data['phone'] ) ? (string) $data['phone'] : '';
$etheme_message = isset( $data['message'] ) ? (string) $data['message'] : '';
$etheme_date    = isset( $data['date'] ) ? (string) $data['date'] : '';

do_action( 'woocommerce_email_header', $email_heading, isset( $email ) ? $email : null );
?>

<p class="etheme-email-body" style="margin:0 0 16px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;line-height:1.6;color:#333333;">
	<?php esc_html_e( 'Recibiste un nuevo mensaje desde el formulario de contacto.', 'etheme' ); ?>
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px 0;border:1px solid #e5e5e5;">
	<tr>
		<th scope="row" style="background-color:#f7f7f7;padding:12px;text-align:left;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;color:#666666;font-weight:600;width:120px;border-bottom:1px solid #e5e5e5;"><?php esc_html_e( 'Nombre', 'etheme' ); ?></th>
		<td style="padding:12px;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;color:#333333;border-bottom:1px solid #e5e5e5;"><?php echo esc_html( $etheme_name ); ?></td>
	</tr>
	<tr>
		<th scope="row" style="background-color:#f7f7f7;padding:12px;text-align:left;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;color:#666666;font-weight:600;width:120px;border-bottom:1px solid #e5e5e5;"><?php esc_html_e( 'Email', 'etheme' ); ?></th>
		<td style="padding:12px;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;color:#333333;border-bottom:1px solid #e5e5e5;">
			<a href="<?php echo esc_url( 'mailto:' . $etheme_email ); ?>" style="color:#fb704f;text-decoration:underline;"><?php echo esc_html( $etheme_email ); ?></a>
		</td>
	</tr>
	<?php if ( '' !== $etheme_phone ) : ?>
		<tr>
			<th scope="row" style="background-color:#f7f7f7;padding:12px;text-align:left;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;color:#666666;font-weight:600;width:120px;border-bottom:1px solid #e5e5e5;"><?php esc_html_e( 'Teléfono', 'etheme' ); ?></th>
			<td style="padding:12px;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;color:#333333;border-bottom:1px solid #e5e5e5;"><?php echo esc_html( $etheme_phone ); ?></td>
		</tr>
	<?php endif; ?>
	<?php if ( '' !== $etheme_date ) : ?>
		<tr>
			<th scope="row" style="background-color:#f7f7f7;padding:12px;text-align:left;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;color:#666666;font-weight:600;width:120px;"><?php esc_html_e( 'Fecha', 'etheme' ); ?></th>
			<td style="padding:12px;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;color:#333333;"><?php echo esc_html( mysql2date( 'd/m/Y H:i', $etheme_date ) ); ?></td>
		</tr>
	<?php endif; ?>
</table>

<h3 style="margin:0 0 12px 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:16px;font-weight:600;color:#333333;"><?php esc_html_e( 'Mensaje', 'etheme' ); ?></h3>
<div style="padding:16px;background-color:#f7f7f7;border-left:3px solid #fb704f;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:15px;line-height:1.6;color:#333333;">
	<?php echo wp_kses_post( wpautop( $etheme_message ) ); ?>
</div>

<p class="etheme-email-body" style="margin:24px 0 0 0;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:13px;line-height:1.6;color:#666666;">
	<?php esc_html_e( 'Para responder, usá el email del cliente directamente (el Reply-To de este mail ya está apuntado a su dirección).', 'etheme' ); ?>
</p>

<?php
do_action( 'woocommerce_email_footer', isset( $email ) ? $email : null );
