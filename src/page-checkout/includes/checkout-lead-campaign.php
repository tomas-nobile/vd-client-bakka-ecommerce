<?php
/**
 * Checkout leads — "Lanzar campaña" admin page.
 *
 * Manual launch only (no cron): segmentation options, live recipient counter,
 * coupon select (pre-created native WooCommerce coupons), test send, batched
 * sending with progress (driven by assets/js/admin-lead-campaign.js) and a
 * launch log for auditing.
 *
 * @package Etheme
 * @see specs/23a.checkout-leads-campaign.md
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Capability check shared by the campaign page and its AJAX endpoints.
 *
 * @return bool
 */
function etheme_lead_campaign_user_can() {
	return current_user_can( 'manage_woocommerce' ) || current_user_can( 'manage_options' );
}

/**
 * Store/read the hook suffix returned by add_submenu_page(), so the enqueue
 * matches the real screen regardless of how WP builds CPT submenu hooks.
 *
 * @param string|null $set Hook suffix to store (registration time).
 * @return string
 */
function etheme_lead_campaign_page_hook( $set = null ) {
	static $hook = '';
	if ( null !== $set ) {
		$hook = (string) $set;
	}
	return $hook;
}

/**
 * Register the submenu under the leads CPT.
 */
function etheme_lead_campaign_register_page() {
	$hook = add_submenu_page(
		'edit.php?post_type=etheme_checkout_lead',
		__( 'Lanzar campaña', 'etheme' ),
		__( 'Lanzar campaña', 'etheme' ),
		'manage_woocommerce',
		'etheme-lead-campaign',
		'etheme_lead_campaign_render_page'
	);
	if ( $hook ) {
		etheme_lead_campaign_page_hook( $hook );
	}
}
add_action( 'admin_menu', 'etheme_lead_campaign_register_page' );

/**
 * Enqueue the campaign admin script only on its own screen.
 *
 * @param string $hook Current admin page hook.
 */
function etheme_lead_campaign_enqueue_assets( $hook ) {
	if ( '' === etheme_lead_campaign_page_hook() || $hook !== etheme_lead_campaign_page_hook() ) {
		return;
	}
	$rel  = '/assets/js/admin-lead-campaign.js';
	$path = get_template_directory() . $rel;
	wp_enqueue_script(
		'etheme-admin-lead-campaign',
		get_theme_file_uri( $rel ),
		array(),
		file_exists( $path ) ? (string) filemtime( $path ) : '1.0.0',
		true
	);
}
add_action( 'admin_enqueue_scripts', 'etheme_lead_campaign_enqueue_assets' );

/**
 * Coupon summary used by the select, validation and the email body.
 *
 * @param string $code Coupon code.
 * @return array|null { code, amount_label, expires_ts, expires_text, expired, exhausted } or null if missing.
 */
function etheme_lead_get_coupon_summary( $code ) {
	if ( ! class_exists( 'WC_Coupon' ) || '' === $code ) {
		return null;
	}
	$coupon = new WC_Coupon( $code );
	if ( ! $coupon->get_id() ) {
		return null;
	}

	$amount = (float) $coupon->get_amount();
	$type   = $coupon->get_discount_type();
	if ( 'percent' === $type ) {
		/* translators: %s: discount percentage */
		$amount_label = sprintf( __( '%s%% de descuento', 'etheme' ), wc_format_localized_decimal( $amount ) );
	} else {
		/* translators: %s: discount amount */
		$amount_label = sprintf( __( '%s de descuento', 'etheme' ), wp_strip_all_tags( wc_price( $amount ) ) );
	}

	$expires      = $coupon->get_date_expires();
	$expires_ts   = $expires ? $expires->getTimestamp() : 0;
	$expires_text = $expires_ts ? date_i18n( 'd/m/Y', $expires_ts ) : '';
	$expired      = $expires_ts && $expires_ts < time();

	$usage_limit = (int) $coupon->get_usage_limit();
	$exhausted   = $usage_limit > 0 && (int) $coupon->get_usage_count() >= $usage_limit;

	return array(
		'code'         => $coupon->get_code(),
		'amount_label' => $amount_label,
		'expires_ts'   => $expires_ts,
		'expires_text' => $expires_text,
		'expired'      => $expired,
		'exhausted'    => $exhausted,
	);
}

/**
 * Append an entry to the campaign launch log (newest first, capped at 50).
 *
 * @param array $entry { date, user, coupon, filters, sent, failed }.
 */
function etheme_lead_campaign_log_entry( $entry ) {
	$log = get_option( 'etheme_lead_campaign_log', array() );
	if ( ! is_array( $log ) ) {
		$log = array();
	}
	array_unshift( $log, $entry );
	$log = array_slice( $log, 0, 50 );
	update_option( 'etheme_lead_campaign_log', $log, false );
}

/**
 * Render the campaign page.
 */
function etheme_lead_campaign_render_page() {
	if ( ! etheme_lead_campaign_user_can() ) {
		wp_die( esc_html__( 'No tenés permisos para lanzar campañas.', 'etheme' ) );
	}

	$wc_active = class_exists( 'WooCommerce' );
	$coupons   = $wc_active ? get_posts( array(
		'post_type'      => 'shop_coupon',
		'post_status'    => 'publish',
		'posts_per_page' => 50,
		'orderby'        => 'date',
		'order'          => 'DESC',
	) ) : array();

	$log = get_option( 'etheme_lead_campaign_log', array() );
	if ( ! is_array( $log ) ) {
		$log = array();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Lanzar campaña de recuperación', 'etheme' ); ?></h1>
		<p style="max-width:640px;">
			<?php esc_html_e( 'Envía un email de recordatorio con cupón a los leads en estado "Interesado". Los que ya compraron o se dieron de baja quedan excluidos automáticamente. El envío es manual: nada sale sin tu confirmación.', 'etheme' ); ?>
		</p>

		<?php if ( ! $wc_active ) : ?>
			<div class="notice notice-error"><p><?php esc_html_e( 'WooCommerce no está activo — no se pueden enviar campañas.', 'etheme' ); ?></p></div>
		<?php elseif ( empty( $coupons ) ) : ?>
			<div class="notice notice-warning"><p>
				<?php esc_html_e( 'No hay cupones creados. Creá primero el cupón de la campaña en Marketing → Cupones.', 'etheme' ); ?>
			</p></div>
		<?php endif; ?>

		<div
			id="etheme-lead-campaign"
			data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'etheme-lead-campaign-nonce' ) ); ?>"
			style="max-width:640px;"
		>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="etheme-campaign-days"><?php esc_html_e( 'Antigüedad del lead', 'etheme' ); ?></label></th>
					<td>
						<select id="etheme-campaign-days" data-campaign-filter>
							<option value="7"><?php esc_html_e( 'Últimos 7 días', 'etheme' ); ?></option>
							<option value="14" selected><?php esc_html_e( 'Últimos 14 días', 'etheme' ); ?></option>
							<option value="30"><?php esc_html_e( 'Últimos 30 días', 'etheme' ); ?></option>
							<option value="0"><?php esc_html_e( 'Todos', 'etheme' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="etheme-campaign-min-total"><?php esc_html_e( 'Monto mínimo del carrito', 'etheme' ); ?></label></th>
					<td>
						<input type="number" id="etheme-campaign-min-total" data-campaign-filter min="0" step="1" placeholder="0" />
						<p class="description"><?php esc_html_e( 'Opcional. Dejar vacío para no filtrar por monto.', 'etheme' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="etheme-campaign-coupon"><?php esc_html_e( 'Cupón de la campaña', 'etheme' ); ?></label></th>
					<td>
						<select id="etheme-campaign-coupon">
							<option value=""><?php esc_html_e( '— Elegir cupón —', 'etheme' ); ?></option>
							<?php
							foreach ( $coupons as $coupon_post ) {
								$summary = etheme_lead_get_coupon_summary( $coupon_post->post_title );
								if ( ! $summary ) {
									continue;
								}
								$label = $summary['code'] . ' — ' . $summary['amount_label'];
								if ( $summary['expires_text'] ) {
									/* translators: %s: expiry date */
									$label .= ' (' . sprintf( __( 'vence %s', 'etheme' ), $summary['expires_text'] ) . ')';
								}
								printf(
									'<option value="%s" data-expired="%d" data-exhausted="%d">%s</option>',
									esc_attr( $summary['code'] ),
									$summary['expired'] ? 1 : 0,
									$summary['exhausted'] ? 1 : 0,
									esc_html( $label )
								);
							}
							?>
						</select>
						<p id="etheme-campaign-coupon-warning" class="description" style="color:#b32d2e;" hidden></p>
						<p class="description">
							<?php esc_html_e( 'El cupón se crea de antemano en Marketing → Cupones.', 'etheme' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="etheme-campaign-subject"><?php esc_html_e( 'Asunto del email', 'etheme' ); ?></label></th>
					<td>
						<input type="text" id="etheme-campaign-subject" class="regular-text" value="<?php echo esc_attr__( 'Te quedó algo en el carrito 🛋️', 'etheme' ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="etheme-campaign-intro"><?php esc_html_e( 'Mensaje introductorio', 'etheme' ); ?></label></th>
					<td>
						<textarea id="etheme-campaign-intro" class="large-text" rows="3" placeholder="<?php echo esc_attr__( 'Opcional. Texto corto arriba del resumen del carrito.', 'etheme' ); ?>"></textarea>
					</td>
				</tr>
			</table>

			<p style="font-size:14px;">
				<strong><?php esc_html_e( 'Destinatarios:', 'etheme' ); ?></strong>
				<span id="etheme-campaign-count" aria-live="polite">…</span>
			</p>

			<p>
				<button type="button" class="button" id="etheme-campaign-test">
					<?php esc_html_e( 'Enviarme una prueba', 'etheme' ); ?>
				</button>
				<button type="button" class="button button-primary" id="etheme-campaign-launch">
					<?php esc_html_e( 'Lanzar campaña', 'etheme' ); ?>
				</button>
			</p>

			<div id="etheme-campaign-progress" hidden style="margin:12px 0;max-width:480px;">
				<div style="background:#dcdcde;height:18px;overflow:hidden;">
					<div id="etheme-campaign-progress-bar" style="background:#2271b1;height:100%;width:0%;transition:width .2s;"></div>
				</div>
				<p id="etheme-campaign-progress-text" style="margin:6px 0 0;" aria-live="polite"></p>
			</div>

			<div id="etheme-campaign-result" aria-live="polite"></div>
		</div>

		<?php if ( ! empty( $log ) ) : ?>
			<h2 style="margin-top:32px;"><?php esc_html_e( 'Campañas lanzadas', 'etheme' ); ?></h2>
			<table class="widefat striped" style="max-width:900px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Fecha', 'etheme' ); ?></th>
						<th><?php esc_html_e( 'Lanzada por', 'etheme' ); ?></th>
						<th><?php esc_html_e( 'Cupón', 'etheme' ); ?></th>
						<th><?php esc_html_e( 'Filtros', 'etheme' ); ?></th>
						<th><?php esc_html_e( 'Enviados', 'etheme' ); ?></th>
						<th><?php esc_html_e( 'Fallidos', 'etheme' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $log as $entry ) : ?>
						<tr>
							<td><?php echo esc_html( isset( $entry['date'] ) ? mysql2date( 'd/m/Y H:i', $entry['date'] ) : '' ); ?></td>
							<td><?php echo esc_html( isset( $entry['user'] ) ? $entry['user'] : '' ); ?></td>
							<td><code><?php echo esc_html( isset( $entry['coupon'] ) ? $entry['coupon'] : '' ); ?></code></td>
							<td><?php echo esc_html( isset( $entry['filters'] ) ? $entry['filters'] : '' ); ?></td>
							<td><?php echo esc_html( isset( $entry['sent'] ) ? (int) $entry['sent'] : 0 ); ?></td>
							<td><?php echo esc_html( isset( $entry['failed'] ) ? (int) $entry['failed'] : 0 ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
