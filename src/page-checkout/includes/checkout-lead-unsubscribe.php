<?php
/**
 * Checkout leads — public unsubscribe endpoint.
 *
 * Campaign emails include a per-lead signed link. Valid token → the lead is
 * flagged unsubscribed and excluded from every future send/export. Forged or
 * stale links redirect home without leaking whether the lead exists.
 *
 * @package Etheme
 * @see specs/23a.checkout-leads-campaign.md
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle ?etheme_unsub={lead_id}&t={token}.
 */
function etheme_lead_unsubscribe_endpoint() {
	if ( empty( $_GET['etheme_unsub'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public link, authenticated by signed token.
	$lead_id = absint( wp_unslash( $_GET['etheme_unsub'] ) );
	$token   = isset( $_GET['t'] ) ? sanitize_text_field( wp_unslash( $_GET['t'] ) ) : '';
	// phpcs:enable

	if (
		! $lead_id
		|| ! function_exists( 'etheme_lead_verify_token' )
		|| ! etheme_lead_verify_token( $lead_id, 'unsub', $token )
		|| 'etheme_checkout_lead' !== get_post_type( $lead_id )
	) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	update_post_meta( $lead_id, '_lead_unsubscribed', 1 );
	update_post_meta( $lead_id, '_lead_unsubscribed_at', current_time( 'mysql' ) );

	$message  = '<h1 style="font-size:1.3em;">' . esc_html__( 'Listo, te diste de baja', 'etheme' ) . '</h1>';
	$message .= '<p>' . esc_html__( 'No vas a recibir más recordatorios de tu carrito por email.', 'etheme' ) . '</p>';
	$message .= '<p><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Volver a la tienda', 'etheme' ) . '</a></p>';

	wp_die(
		wp_kses_post( $message ),
		esc_html__( 'Baja de emails', 'etheme' ),
		array( 'response' => 200 )
	);
}
add_action( 'template_redirect', 'etheme_lead_unsubscribe_endpoint', 5 );
