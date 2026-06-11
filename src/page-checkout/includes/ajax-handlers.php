<?php
/**
 * Checkout AJAX handlers — lead capture.
 *
 * The frontend only sends email + nonce (+ optional first name); cart items
 * are always rebuilt server-side from WC()->cart so the client can't forge them.
 *
 * @package Etheme
 * @see specs/23.checkout-leads.md
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_etheme_capture_checkout_lead', 'etheme_capture_checkout_lead_handler' );
add_action( 'wp_ajax_nopriv_etheme_capture_checkout_lead', 'etheme_capture_checkout_lead_handler' );

/**
 * Basic per-IP rate limit so the registry can't be flooded.
 *
 * @return bool True if the request is within limits.
 */
function etheme_lead_capture_rate_limit_ok() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	if ( '' === $ip ) {
		return true;
	}
	$key   = 'etheme_lead_rl_' . md5( $ip );
	$count = (int) get_transient( $key );
	if ( $count >= 10 ) {
		return false;
	}
	set_transient( $key, $count + 1, 10 * MINUTE_IN_SECONDS );
	return true;
}

/**
 * Capture a checkout lead: validate email, rebuild the cart server-side,
 * and upsert the registry entry.
 */
function etheme_capture_checkout_lead_handler() {
	check_ajax_referer( 'etheme-checkout-lead-nonce', 'nonce' );

	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		wp_send_json_error( array( 'message' => __( 'WooCommerce no disponible.', 'etheme' ) ), 400 );
	}

	if ( ! etheme_lead_capture_rate_limit_ok() ) {
		wp_send_json_error( array( 'message' => __( 'Demasiadas solicitudes. Probá más tarde.', 'etheme' ) ), 429 );
	}

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Email inválido.', 'etheme' ) ), 400 );
	}

	$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';

	if ( WC()->cart->is_empty() ) {
		wp_send_json_error( array( 'message' => __( 'El carrito está vacío.', 'etheme' ) ), 400 );
	}

	$items = array();
	foreach ( WC()->cart->get_cart() as $cart_item ) {
		$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
		if ( ! $product instanceof WC_Product ) {
			continue;
		}

		$product_id = isset( $cart_item['product_id'] ) ? absint( $cart_item['product_id'] ) : 0;
		$thumb_id   = $product->get_image_id();
		$thumb_url  = $thumb_id ? (string) wp_get_attachment_image_url( $thumb_id, 'woocommerce_thumbnail' ) : '';

		$items[] = array(
			'product_id'    => $product_id,
			'variation_id'  => isset( $cart_item['variation_id'] ) ? absint( $cart_item['variation_id'] ) : 0,
			'name'          => $product->get_name(),
			'sku'           => $product->get_sku(),
			'quantity'      => isset( $cart_item['quantity'] ) ? absint( $cart_item['quantity'] ) : 1,
			'price'         => (float) wc_get_price_to_display( $product ),
			'line_subtotal' => isset( $cart_item['line_subtotal'] ) ? (float) $cart_item['line_subtotal'] : 0,
			'permalink'     => $product_id ? (string) get_permalink( $product_id ) : '',
			'thumbnail'     => $thumb_url,
		);
	}

	if ( empty( $items ) ) {
		wp_send_json_error( array( 'message' => __( 'El carrito está vacío.', 'etheme' ) ), 400 );
	}

	$total   = (float) WC()->cart->get_total( 'edit' );
	$lead_id = etheme_lead_upsert( $email, $name, $items, $total );

	if ( ! $lead_id ) {
		wp_send_json_error( array( 'message' => __( 'No se pudo guardar el registro.', 'etheme' ) ), 500 );
	}

	wp_send_json_success( array( 'captured' => true ) );
}
