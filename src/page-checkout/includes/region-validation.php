<?php
/**
 * Checkout region validation and posted data remapping.
 *
 * - Blocks checkout submission for provinces outside the delivery zone.
 * - Remaps custom province codes (BA_GBA, BA_INTERIOR) to WooCommerce standard codes.
 * - Composes billing_phone from split area + number inputs.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provinces allowed to proceed to payment.
 * BA_GBA = Gran Buenos Aires; C = Capital Federal / CABA.
 *
 * @return string[]
 */
function etheme_checkout_get_allowed_provinces() {
	return array( 'C', 'BA_GBA' );
}

/**
 * Server-side validation: blocks checkout if the province is not in the delivery zone.
 * Runs on woocommerce_checkout_process (before WC remaps the data), so
 * $shipping_state still holds the raw POST value.
 */
add_action( 'woocommerce_checkout_process', 'etheme_checkout_validate_region', 5 );
function etheme_checkout_validate_region() {
	$state = '';
	if ( isset( $_POST['checkout_province_display'] ) ) {
		$state = sanitize_text_field( wp_unslash( $_POST['checkout_province_display'] ) );
	}
	if ( '' === $state && isset( $_POST['shipping_state'] ) ) {
		$state = sanitize_text_field( wp_unslash( $_POST['shipping_state'] ) );
	}
	if ( '' === $state && isset( $_POST['billing_state'] ) ) {
		$state = sanitize_text_field( wp_unslash( $_POST['billing_state'] ) );
	}

	$allowed = etheme_checkout_get_allowed_provinces();
	if ( '' === $state || in_array( $state, $allowed, true ) ) {
		return;
	}

	wc_add_notice(
		__( 'Lo sentimos, por el momento solo realizamos envíos a Capital Federal y Gran Buenos Aires. Escribinos para coordinar tu compra.', 'etheme' ),
		'error'
	);
}

/**
 * Remaps custom province codes to WooCommerce standard codes and composes
 * billing_phone from the split inputs. Runs before WC's own validation.
 *
 * @param array $data Checkout posted data.
 * @return array
 */
add_filter( 'woocommerce_checkout_posted_data', 'etheme_checkout_remap_posted_data', 1 );
function etheme_checkout_remap_posted_data( $data ) {
	$data = etheme_checkout_remap_province( $data );
	$data = etheme_checkout_compose_billing_phone( $data );
	return $data;
}

/**
 * Remap BA_GBA / BA_INTERIOR → WooCommerce standard code 'B' (Buenos Aires province).
 * BA_INTERIOR is already blocked in woocommerce_checkout_process; this remap is a
 * fallback to avoid state-validation errors if it somehow passes through.
 *
 * @param array $data Checkout posted data.
 * @return array
 */
function etheme_checkout_remap_province( $data ) {
	$remap = array( 'BA_GBA' => 'B', 'BA_INTERIOR' => 'B' );
	foreach ( array( 'shipping_state', 'billing_state' ) as $key ) {
		if ( isset( $data[ $key ] ) && isset( $remap[ $data[ $key ] ] ) ) {
			$data[ $key ] = $remap[ $data[ $key ] ];
		}
	}
	return $data;
}

/**
 * Compose billing_phone from checkout_phone_area + checkout_phone_number if present.
 *
 * @param array $data Checkout posted data.
 * @return array
 */
function etheme_checkout_compose_billing_phone( $data ) {
	$area = isset( $_POST['checkout_phone_area'] )
		? preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_POST['checkout_phone_area'] ) ) )
		: '';
	$num  = isset( $_POST['checkout_phone_number'] )
		? preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_POST['checkout_phone_number'] ) ) )
		: '';
	if ( $area && $num ) {
		$data['billing_phone'] = $area . $num;
	}
	return $data;
}
