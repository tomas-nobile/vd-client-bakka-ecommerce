<?php
/**
 * Lead recovery (abandoned cart) campaign email — inner content.
 *
 * Wrapped by WC()->mailer()->wrap_message(), which applies the theme's
 * email-header.php / email-footer.php / email-styles.php (spec 22).
 *
 * Variables provided by etheme_render_email():
 *
 * @var string $name            Recipient first name (may be empty).
 * @var string $intro           Optional intro paragraph.
 * @var array  $items           Cart items snapshot.
 * @var float  $total           Cart total.
 * @var string $currency        Currency code.
 * @var array  $coupon          { code, amount_label, expires_text }.
 * @var string $recovery_url    Cart recovery URL.
 * @var string $unsubscribe_url Unsubscribe URL.
 *
 * @package Etheme
 * @see specs/23a.checkout-leads-campaign.md
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$etheme_greeting = ! empty( $name )
	/* translators: %s: customer first name */
	? sprintf( __( '¡Hola, %s!', 'etheme' ), $name )
	: __( '¡Hola!', 'etheme' );

$etheme_price_args = ! empty( $currency ) ? array( 'currency' => $currency ) : array();
?>
<p style="margin:0 0 16px;"><?php echo esc_html( $etheme_greeting ); ?></p>

<?php if ( ! empty( $intro ) ) : ?>
	<p style="margin:0 0 16px;"><?php echo esc_html( $intro ); ?></p>
<?php endif; ?>

<p style="margin:0 0 16px;">
	<?php esc_html_e( 'Vimos que dejaste estos productos en tu carrito. Te los guardamos para que puedas terminar tu compra cuando quieras.', 'etheme' ); ?>
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 16px;border:1px solid #e5e5e5;">
	<?php foreach ( (array) $items as $etheme_item ) : ?>
	<tr>
		<td style="padding:12px;border-bottom:1px solid #e5e5e5;width:64px;">
			<?php if ( ! empty( $etheme_item['thumbnail'] ) ) : ?>
				<img src="<?php echo esc_url( $etheme_item['thumbnail'] ); ?>" alt="" width="48" height="48" style="display:block;width:48px;height:48px;object-fit:cover;" />
			<?php endif; ?>
		</td>
		<td style="padding:12px;border-bottom:1px solid #e5e5e5;font-size:14px;">
			<?php echo esc_html( isset( $etheme_item['name'] ) ? $etheme_item['name'] : '' ); ?>
			<br />
			<span style="color:#777777;font-size:12px;">
				<?php
				printf(
					/* translators: %d: quantity */
					esc_html__( 'Cantidad: %d', 'etheme' ),
					isset( $etheme_item['quantity'] ) ? (int) $etheme_item['quantity'] : 1
				);
				?>
			</span>
		</td>
		<td style="padding:12px;border-bottom:1px solid #e5e5e5;font-size:14px;text-align:right;white-space:nowrap;">
			<?php
			if ( function_exists( 'wc_price' ) ) {
				echo wp_kses_post( wc_price( isset( $etheme_item['line_subtotal'] ) ? (float) $etheme_item['line_subtotal'] : 0, $etheme_price_args ) );
			}
			?>
		</td>
	</tr>
	<?php endforeach; ?>
	<tr>
		<td colspan="2" style="padding:12px;font-size:14px;font-weight:bold;">
			<?php esc_html_e( 'Total', 'etheme' ); ?>
		</td>
		<td style="padding:12px;font-size:14px;font-weight:bold;text-align:right;white-space:nowrap;">
			<?php
			if ( function_exists( 'wc_price' ) ) {
				echo wp_kses_post( wc_price( (float) $total, $etheme_price_args ) );
			}
			?>
		</td>
	</tr>
</table>

<?php if ( ! empty( $coupon['code'] ) ) : ?>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
	<tr>
		<td style="border:2px dashed #c96f5a;padding:16px;text-align:center;">
			<p style="margin:0 0 8px;font-size:14px;">
				<?php
				printf(
					/* translators: %s: discount label, e.g. "10% de descuento" */
					esc_html__( 'Y para que te decidas, te regalamos %s con este cupón:', 'etheme' ),
					esc_html( isset( $coupon['amount_label'] ) ? $coupon['amount_label'] : '' )
				);
				?>
			</p>
			<p style="margin:0 0 8px;font-size:22px;font-weight:bold;letter-spacing:2px;">
				<?php echo esc_html( $coupon['code'] ); ?>
			</p>
			<?php if ( ! empty( $coupon['expires_text'] ) ) : ?>
				<p style="margin:0;font-size:12px;color:#777777;">
					<?php
					printf(
						/* translators: %s: expiry date */
						esc_html__( 'Válido hasta el %s.', 'etheme' ),
						esc_html( $coupon['expires_text'] )
					);
					?>
				</p>
			<?php endif; ?>
		</td>
	</tr>
</table>
<?php endif; ?>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin:0 0 24px;">
	<tr>
		<td align="center">
			<a
				href="<?php echo esc_url( $recovery_url ); ?>"
				style="display:inline-block;background-color:#c96f5a;color:#ffffff;text-decoration:none;font-size:15px;font-weight:bold;padding:14px 32px;"
			>
				<?php esc_html_e( 'Volver a mi carrito', 'etheme' ); ?>
			</a>
		</td>
	</tr>
</table>

<p style="margin:0;font-size:11px;color:#999999;text-align:center;">
	<?php esc_html_e( 'Recibís este email porque dejaste productos en tu carrito en nuestra tienda.', 'etheme' ); ?>
	<a href="<?php echo esc_url( $unsubscribe_url ); ?>" style="color:#999999;text-decoration:underline;">
		<?php esc_html_e( 'No quiero recibir más estos recordatorios', 'etheme' ); ?>
	</a>
</p>

<?php // Leyendas obligatorias para comunicaciones publicitarias — Disposición DNPDP 10/2008, Ley 25.326. ?>
<p style="margin:12px 0 0;font-size:10px;color:#999999;text-align:center;">
	<?php esc_html_e( 'Podés solicitar en cualquier momento el retiro o bloqueo, total o parcial, de tu email de nuestra base de datos a través del link de baja de este correo.', 'etheme' ); ?>
	<?php esc_html_e( 'El titular de los datos personales tiene la facultad de ejercer el derecho de acceso a los mismos en forma gratuita a intervalos no inferiores a seis meses, salvo que se acredite un interés legítimo al efecto, conforme lo establecido en el artículo 14, inciso 3 de la Ley N.º 25.326.', 'etheme' ); ?>
	<?php esc_html_e( 'La Agencia de Acceso a la Información Pública, órgano de control de la Ley N.º 25.326, tiene la atribución de atender las denuncias y reclamos que se interpongan con relación al incumplimiento de las normas sobre protección de datos personales.', 'etheme' ); ?>
</p>
