<?php
/**
 * Checkout leads — campaign AJAX endpoints.
 *
 * count → live recipient counter; ids → snapshot at launch; send_batch →
 * batched wp_mail() with per-lead "contacted" marking (idempotent if the run
 * is interrupted); test → sample email to the current admin; finish → audit log.
 *
 * All endpoints: nonce + manage_woocommerce. Sending reuses the theme's
 * WooCommerce email header/footer/styles (spec 22).
 *
 * @package Etheme
 * @see specs/23a.checkout-leads-campaign.md
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/core/emails/helpers.php';

add_action( 'wp_ajax_etheme_lead_campaign_count', 'etheme_lead_campaign_count_handler' );
add_action( 'wp_ajax_etheme_lead_campaign_ids', 'etheme_lead_campaign_ids_handler' );
add_action( 'wp_ajax_etheme_lead_campaign_send_batch', 'etheme_lead_campaign_send_batch_handler' );
add_action( 'wp_ajax_etheme_lead_campaign_test', 'etheme_lead_campaign_test_handler' );
add_action( 'wp_ajax_etheme_lead_campaign_finish', 'etheme_lead_campaign_finish_handler' );

/**
 * Shared guard: nonce + capability. Dies with JSON error on failure.
 */
function etheme_lead_campaign_guard() {
	check_ajax_referer( 'etheme-lead-campaign-nonce', 'nonce' );
	if ( ! function_exists( 'etheme_lead_campaign_user_can' ) || ! etheme_lead_campaign_user_can() ) {
		wp_send_json_error( array( 'message' => __( 'Sin permisos.', 'etheme' ) ), 403 );
	}
}

/**
 * WP_Query args for campaign targets: status "interested", not unsubscribed,
 * optional capture-date and minimum-total filters.
 *
 * @param int   $days      Max lead age in days (0 = all).
 * @param float $min_total Minimum cart total (0 = no filter).
 * @return array
 */
function etheme_lead_campaign_query_args( $days, $min_total ) {
	$meta_query = array(
		array(
			'key'   => '_lead_status',
			'value' => 'interested',
		),
		array(
			'relation' => 'OR',
			array(
				'key'     => '_lead_unsubscribed',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_lead_unsubscribed',
				'value'   => '1',
				'compare' => '!=',
			),
		),
	);

	if ( $min_total > 0 ) {
		$meta_query[] = array(
			'key'     => '_lead_total',
			'value'   => $min_total,
			'compare' => '>=',
			'type'    => 'NUMERIC',
		);
	}

	$args = array(
		'post_type'      => 'etheme_checkout_lead',
		'post_status'    => 'publish',
		'posts_per_page' => 2000,
		'fields'         => 'ids',
		'meta_query'     => $meta_query, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		'no_found_rows'  => true,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( $days > 0 ) {
		$args['date_query'] = array(
			array( 'after' => $days . ' days ago' ),
		);
	}

	return $args;
}

/**
 * Read + sanitize the segmentation filters from the request.
 *
 * @return array{days:int,min_total:float}
 */
function etheme_lead_campaign_read_filters() {
	$days      = isset( $_POST['days'] ) ? absint( wp_unslash( $_POST['days'] ) ) : 0;
	$min_total = isset( $_POST['min_total'] ) ? (float) wp_unslash( $_POST['min_total'] ) : 0;
	return array(
		'days'      => $days,
		'min_total' => max( 0, $min_total ),
	);
}

/**
 * Live recipient counter.
 */
function etheme_lead_campaign_count_handler() {
	etheme_lead_campaign_guard();
	$filters = etheme_lead_campaign_read_filters();
	$ids     = get_posts( etheme_lead_campaign_query_args( $filters['days'], $filters['min_total'] ) );
	wp_send_json_success( array( 'count' => count( $ids ) ) );
}

/**
 * Snapshot of target IDs at launch time.
 */
function etheme_lead_campaign_ids_handler() {
	etheme_lead_campaign_guard();
	$filters = etheme_lead_campaign_read_filters();
	$ids     = get_posts( etheme_lead_campaign_query_args( $filters['days'], $filters['min_total'] ) );
	wp_send_json_success( array( 'ids' => array_map( 'intval', $ids ) ) );
}

/**
 * Validate the selected coupon. Dies with JSON error if unusable.
 *
 * @param string $coupon_code Coupon code.
 * @return array Coupon summary.
 */
function etheme_lead_campaign_require_valid_coupon( $coupon_code ) {
	$summary = function_exists( 'etheme_lead_get_coupon_summary' ) ? etheme_lead_get_coupon_summary( $coupon_code ) : null;
	if ( ! $summary ) {
		wp_send_json_error( array( 'message' => __( 'Elegí un cupón válido. Se crea en Marketing → Cupones.', 'etheme' ) ), 400 );
	}
	if ( $summary['expired'] ) {
		wp_send_json_error( array( 'message' => __( 'El cupón elegido está vencido.', 'etheme' ) ), 400 );
	}
	if ( $summary['exhausted'] ) {
		wp_send_json_error( array( 'message' => __( 'El cupón elegido agotó sus usos disponibles.', 'etheme' ) ), 400 );
	}
	return $summary;
}

/**
 * Build and send one campaign email.
 *
 * @param array $args {
 *     @type string $to             Recipient.
 *     @type string $name           Recipient first name (may be empty).
 *     @type array  $items          Cart items snapshot.
 *     @type float  $total          Cart total.
 *     @type string $currency       Currency code.
 *     @type string $subject        Email subject (used as heading too).
 *     @type string $intro          Optional intro paragraph.
 *     @type array  $coupon         Coupon summary from etheme_lead_get_coupon_summary().
 *     @type string $recovery_url   Cart recovery URL.
 *     @type string $unsubscribe_url Unsubscribe URL.
 * }
 * @return bool
 */
function etheme_lead_campaign_send_email( $args ) {
	$template = get_template_directory() . '/src/core/emails/lead-recovery-email.php';
	$content  = etheme_render_email( $template, $args );
	if ( '' === $content ) {
		return false;
	}

	$html = $content;
	if ( function_exists( 'WC' ) && WC() && WC()->mailer() ) {
		$mailer  = WC()->mailer();
		$wrapped = $mailer->wrap_message( $args['subject'], $content );
		$wc_mail = new WC_Email();
		$html    = $wc_mail->style_inline( $wrapped );
	}

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . etheme_get_email_from_name() . ' <' . etheme_get_email_from_address() . '>',
		'Reply-To: ' . etheme_get_email_reply_to(),
	);

	return (bool) wp_mail( $args['to'], $args['subject'], $html, $headers );
}

/**
 * Send a batch of campaign emails. Marks each lead "contacted" as it goes,
 * so an interrupted run never re-sends. One bad email never aborts the batch.
 */
function etheme_lead_campaign_send_batch_handler() {
	etheme_lead_campaign_guard();

	$lead_ids = isset( $_POST['lead_ids'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['lead_ids'] ) ) : array();
	$lead_ids = array_slice( array_filter( $lead_ids ), 0, 30 );
	if ( empty( $lead_ids ) ) {
		wp_send_json_error( array( 'message' => __( 'Lote vacío.', 'etheme' ) ), 400 );
	}

	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	if ( '' === $subject ) {
		$subject = __( 'Te quedó algo en el carrito', 'etheme' );
	}
	$intro       = isset( $_POST['intro'] ) ? sanitize_textarea_field( wp_unslash( $_POST['intro'] ) ) : '';
	$coupon_code = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
	$coupon      = etheme_lead_campaign_require_valid_coupon( $coupon_code );

	$sent   = 0;
	$failed = 0;

	foreach ( $lead_ids as $lead_id ) {
		try {
			if ( 'etheme_checkout_lead' !== get_post_type( $lead_id ) ) {
				continue;
			}
			// Re-check at send time: status may have changed since the snapshot.
			$status = (string) get_post_meta( $lead_id, '_lead_status', true );
			if ( 'interested' !== $status || get_post_meta( $lead_id, '_lead_unsubscribed', true ) ) {
				continue;
			}

			$email = (string) get_post_meta( $lead_id, '_lead_email', true );
			$items = get_post_meta( $lead_id, '_lead_items', true );
			if ( ! is_email( $email ) || ! is_array( $items ) || empty( $items ) ) {
				$failed++;
				continue;
			}

			$ok = etheme_lead_campaign_send_email( array(
				'to'              => $email,
				'name'            => (string) get_post_meta( $lead_id, '_lead_name', true ),
				'items'           => $items,
				'total'           => (float) get_post_meta( $lead_id, '_lead_total', true ),
				'currency'        => (string) get_post_meta( $lead_id, '_lead_currency', true ),
				'subject'         => $subject,
				'intro'           => $intro,
				'coupon'          => $coupon,
				'recovery_url'    => etheme_lead_get_recovery_url( $lead_id ),
				'unsubscribe_url' => etheme_lead_get_unsubscribe_url( $lead_id ),
			) );

			if ( $ok ) {
				update_post_meta( $lead_id, '_lead_recovery_coupon', $coupon['code'] );
				update_post_meta( $lead_id, '_lead_contacted_at', current_time( 'mysql' ) );
				etheme_lead_set_status( $lead_id, 'contacted' );
				$sent++;
			} else {
				$failed++;
				error_log( 'etheme_lead_campaign: wp_mail() failed for lead ' . $lead_id );
			}
		} catch ( Throwable $e ) {
			$failed++;
			error_log( 'etheme_lead_campaign: ' . $e->getMessage() );
		}
	}

	wp_send_json_success( array(
		'sent'   => $sent,
		'failed' => $failed,
	) );
}

/**
 * Send a sample email (fake cart data) to the logged-in admin.
 */
function etheme_lead_campaign_test_handler() {
	etheme_lead_campaign_guard();

	$user = wp_get_current_user();
	if ( ! $user || ! is_email( $user->user_email ) ) {
		wp_send_json_error( array( 'message' => __( 'Tu usuario no tiene un email válido.', 'etheme' ) ), 400 );
	}

	$subject = isset( $_POST['subject'] ) ? sanitize_text_field( wp_unslash( $_POST['subject'] ) ) : '';
	if ( '' === $subject ) {
		$subject = __( 'Te quedó algo en el carrito', 'etheme' );
	}
	$intro       = isset( $_POST['intro'] ) ? sanitize_textarea_field( wp_unslash( $_POST['intro'] ) ) : '';
	$coupon_code = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
	$coupon      = etheme_lead_campaign_require_valid_coupon( $coupon_code );

	$sample_items = array(
		array(
			'name'          => __( 'Producto de ejemplo', 'etheme' ),
			'quantity'      => 2,
			'line_subtotal' => 100,
			'thumbnail'     => '',
			'permalink'     => home_url( '/' ),
		),
	);

	$ok = etheme_lead_campaign_send_email( array(
		'to'              => $user->user_email,
		'name'            => $user->display_name,
		'items'           => $sample_items,
		'total'           => 100,
		'currency'        => function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '',
		'subject'         => '[TEST] ' . $subject,
		'intro'           => $intro,
		'coupon'          => $coupon,
		'recovery_url'    => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ),
		'unsubscribe_url' => home_url( '/' ),
	) );

	if ( ! $ok ) {
		wp_send_json_error( array( 'message' => __( 'No se pudo enviar la prueba. Revisá la configuración SMTP.', 'etheme' ) ), 500 );
	}

	wp_send_json_success( array(
		/* translators: %s: admin email address */
		'message' => sprintf( __( 'Prueba enviada a %s.', 'etheme' ), $user->user_email ),
	) );
}

/**
 * Write the audit log entry when the JS finishes a launch.
 */
function etheme_lead_campaign_finish_handler() {
	etheme_lead_campaign_guard();

	$sent        = isset( $_POST['sent'] ) ? absint( wp_unslash( $_POST['sent'] ) ) : 0;
	$failed      = isset( $_POST['failed'] ) ? absint( wp_unslash( $_POST['failed'] ) ) : 0;
	$coupon_code = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';
	$filters     = etheme_lead_campaign_read_filters();

	$filters_text = $filters['days'] > 0
		/* translators: %d: number of days */
		? sprintf( __( 'últimos %d días', 'etheme' ), $filters['days'] )
		: __( 'todos', 'etheme' );
	if ( $filters['min_total'] > 0 ) {
		/* translators: %s: minimum cart total */
		$filters_text .= ', ' . sprintf( __( 'mínimo %s', 'etheme' ), $filters['min_total'] );
	}

	$user = wp_get_current_user();

	etheme_lead_campaign_log_entry( array(
		'date'    => current_time( 'mysql' ),
		'user'    => $user ? $user->display_name : '',
		'coupon'  => $coupon_code,
		'filters' => $filters_text,
		'sent'    => $sent,
		'failed'  => $failed,
	) );

	wp_send_json_success();
}
