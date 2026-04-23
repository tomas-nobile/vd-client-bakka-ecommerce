<?php
/**
 * Order details table for emails (override).
 *
 * @param WC_Order $order         Order object.
 * @param bool     $sent_to_admin Admin mail?
 * @param bool     $plain_text    Plain-text?
 * @param string   $email         Email id.
 */

defined( 'ABSPATH' ) || exit;

if ( $plain_text ) {
	require_once WC_ABSPATH . 'includes/wc-notice-functions.php';
	wc_get_template(
		'emails/plain/email-order-details.php',
		array(
			'order'         => $order,
			'sent_to_admin' => $sent_to_admin,
			'plain_text'    => $plain_text,
			'email'         => $email,
		)
	);
	return;
}

$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );
?>
<h2 style="color:#333333;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:18px;font-weight:600;margin:24px 0 12px 0;line-height:1.3;">
	<?php
	if ( $sent_to_admin ) {
		$before = '<a class="link" style="color:#fb704f;text-decoration:none;" href="' . esc_url( $order->get_edit_order_url() ) . '">';
		$after  = '</a>';
	} else {
		$before = '';
		$after  = '';
	}
	echo wp_kses_post( $before . sprintf(
		/* translators: %s: Order ID. */
		esc_html__( 'Pedido #%s', 'etheme' ),
		$order->get_order_number()
	) . $after );
	?>
	<span style="display:block;font-size:13px;color:#666666;font-weight:400;margin-top:4px;">
		<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
	</span>
</h2>

<div style="margin-bottom:24px;">
<table class="etheme-email-order-table" cellspacing="0" cellpadding="6" border="0" style="color:#333333;border:1px solid #e5e5e5;width:100%;font-family:Jost,'Helvetica Neue',Helvetica,Arial,sans-serif;font-size:14px;" width="100%">
	<thead>
		<tr>
			<th class="td" scope="col" style="color:#666666;border-bottom:1px solid #e5e5e5;padding:12px 8px;text-align:<?php echo esc_attr( $text_align ); ?>;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;"><?php esc_html_e( 'Producto', 'etheme' ); ?></th>
			<th class="td" scope="col" style="color:#666666;border-bottom:1px solid #e5e5e5;padding:12px 8px;text-align:<?php echo esc_attr( $text_align ); ?>;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;"><?php esc_html_e( 'Cantidad', 'etheme' ); ?></th>
			<th class="td" scope="col" style="color:#666666;border-bottom:1px solid #e5e5e5;padding:12px 8px;text-align:<?php echo esc_attr( $text_align ); ?>;font-size:12px;text-transform:uppercase;letter-spacing:0.5px;font-weight:600;"><?php esc_html_e( 'Precio', 'etheme' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php
		echo wc_get_email_order_items( // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped
			$order,
			array(
				'show_sku'      => $sent_to_admin,
				'show_image'    => false,
				'image_size'    => array( 64, 64 ),
				'plain_text'    => $plain_text,
				'sent_to_admin' => $sent_to_admin,
			)
		);
		?>
	</tbody>
	<tfoot>
		<?php
		$totals = $order->get_order_item_totals();
		if ( $totals ) {
			$i = 0;
			foreach ( $totals as $total ) {
				$i++;
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;<?php echo ( 1 === $i ) ? 'border-top-width:2px;' : ''; ?>color:#333333;border-bottom:1px solid #e5e5e5;padding:8px;">
						<?php echo wp_kses_post( $total['label'] ); ?>
					</th>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;<?php echo ( 1 === $i ) ? 'border-top-width:2px;' : ''; ?>color:#333333;border-bottom:1px solid #e5e5e5;padding:8px;">
						<?php echo wp_kses_post( $total['value'] ); ?>
					</td>
				</tr>
				<?php
			}
		}
		if ( $order->get_customer_note() ) {
			?>
			<tr>
				<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;color:#333333;border-bottom:1px solid #e5e5e5;padding:8px;"><?php esc_html_e( 'Nota:', 'etheme' ); ?></th>
				<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;color:#333333;border-bottom:1px solid #e5e5e5;padding:8px;"><?php echo wp_kses_post( wptexturize( $order->get_customer_note() ) ); ?></td>
			</tr>
			<?php
		}
		?>
	</tfoot>
</table>
</div>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
