<?php
/**
 * Checkout helper functions.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get checkout field groups.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @return array
 */
function etheme_checkout_get_field_groups( $checkout ) {
	if ( ! $checkout || ! method_exists( $checkout, 'get_checkout_fields' ) ) {
		return array();
	}

	return $checkout->get_checkout_fields();
}

/**
 * Get field definition by group and key.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @param string      $group    Field group key.
 * @param string      $key      Field key.
 * @return array
 */
function etheme_checkout_get_field_definition( $checkout, $group, $key ) {
	$groups = etheme_checkout_get_field_groups( $checkout );

	if ( empty( $groups[ $group ][ $key ] ) || ! is_array( $groups[ $group ][ $key ] ) ) {
		return array();
	}

	return $groups[ $group ][ $key ];
}

/**
 * Resolve checkout field value with customer fallback.
 *
 * @param WC_Checkout $checkout        Checkout instance.
 * @param string      $field_key       Field key.
 * @param string      $customer_getter Customer getter method.
 * @return string
 */
function etheme_checkout_get_field_value( $checkout, $field_key, $customer_getter = '' ) {
	if ( isset( $_POST[ $field_key ] ) ) {
		return wc_clean( wp_unslash( $_POST[ $field_key ] ) );
	}

	$value = $checkout ? $checkout->get_value( $field_key ) : '';
	if ( '' !== $value ) {
		return $value;
	}

	if ( ! WC()->customer || '' === $customer_getter ) {
		return '';
	}

	return method_exists( WC()->customer, $customer_getter ) ? (string) WC()->customer->{$customer_getter}() : '';
}

/**
 * Render a WooCommerce checkout field with Tailwind-friendly classes.
 *
 * @param WC_Checkout $checkout        Checkout instance.
 * @param string      $group           Field group.
 * @param string      $field_key       Field key.
 * @param array       $overrides       Optional field overrides.
 * @param string      $customer_getter Optional customer getter.
 * @return void
 */
function etheme_checkout_render_field( $checkout, $group, $field_key, $overrides = array(), $customer_getter = '' ) {
	$field_args = etheme_checkout_get_field_definition( $checkout, $group, $field_key );
	if ( empty( $field_args ) ) {
		return;
	}

	$field_args = wp_parse_args( $overrides, $field_args );
	$value      = etheme_checkout_get_field_value( $checkout, $field_key, $customer_getter );

	if ( isset( $overrides['value'] ) ) {
		$value = $overrides['value'];
	}

	woocommerce_form_field( $field_key, $field_args, $value );
}

/**
 * Ensure shipping is calculated so packages have rates (e.g. using store base when no address).
 *
 * @return void
 */
function etheme_checkout_ensure_shipping_rates() {
	if ( ! WC()->cart->needs_shipping() ) {
		return;
	}

	$packages = WC()->shipping()->get_packages();
	$has_rates = false;
	foreach ( $packages as $package ) {
		if ( ! empty( $package['rates'] ) && is_array( $package['rates'] ) ) {
			$has_rates = true;
			break;
		}
	}

	if ( $has_rates ) {
		return;
	}

	$customer = WC()->customer;
	if ( ! $customer ) {
		return;
	}

	$base_country = WC()->countries->get_base_country();
	$base_state   = WC()->countries->get_base_state();
	$base_postcode = WC()->countries->get_base_postcode();

	$customer->set_shipping_country( $base_country );
	$customer->set_shipping_state( $base_state );
	$customer->set_shipping_postcode( $base_postcode ? $base_postcode : '' );
	$customer->set_shipping_city( '' );
	$customer->set_shipping_address( '' );
	$customer->set_shipping_address_2( '' );

	WC()->cart->calculate_shipping();
	WC()->cart->calculate_totals();
}

/**
 * Get all available shipping rates grouped by package index.
 *
 * @return array
 */
function etheme_checkout_get_shipping_rates() {
	etheme_checkout_ensure_shipping_rates();

	$packages = WC()->shipping()->get_packages();
	$rates    = array();

	foreach ( $packages as $package_index => $package ) {
		if ( empty( $package['rates'] ) || ! is_array( $package['rates'] ) ) {
			continue;
		}

		$rates[ $package_index ] = array_values( $package['rates'] );
	}

	return $rates;
}

/**
 * Get chosen shipping methods from session.
 *
 * @return array
 */
function etheme_checkout_get_chosen_shipping_methods() {
	$chosen = WC()->session ? WC()->session->get( 'chosen_shipping_methods', array() ) : array();
	return is_array( $chosen ) ? $chosen : array();
}

/**
 * Get available payment gateways for current checkout.
 *
 * @return array
 */
function etheme_checkout_get_available_gateways() {
	if ( ! WC()->payment_gateways() ) {
		return array();
	}

	return WC()->payment_gateways()->get_available_payment_gateways();
}

/**
 * Map a gateway id to a visual variant.
 *
 * @param string $gateway_id Gateway id.
 * @return string
 */
function etheme_checkout_get_gateway_variant( $gateway_id ) {
	$id = strtolower( (string) $gateway_id );

	if ( false !== strpos( $id, 'mercadopago' ) || false !== strpos( $id, 'mercado_pago' ) ) {
		return 'mercadopago';
	}

	if ( false !== strpos( $id, 'card' ) || false !== strpos( $id, 'stripe' ) ) {
		return 'card';
	}

	return 'default';
}

/**
 * Build short, plain description for a gateway tile.
 *
 * @param WC_Payment_Gateway $gateway Gateway instance.
 * @return string
 */
function etheme_checkout_get_gateway_short_description( $gateway ) {
	$description = trim( wp_strip_all_tags( (string) $gateway->get_description() ) );
	if ( '' !== $description ) {
		return wp_trim_words( $description, 18, '...' );
	}

	return __( 'Secure checkout with encrypted payment processing.', 'etheme' );
}

/**
 * Get selected shipping method label and value.
 *
 * @param array $shipping_rates  Rates grouped by package.
 * @param array $chosen_methods  Chosen rate ids.
 * @return array
 */
function etheme_checkout_get_selected_shipping_rate( $shipping_rates, $chosen_methods ) {
	foreach ( $shipping_rates as $package_index => $package_rates ) {
		$selected_id = isset( $chosen_methods[ $package_index ] ) ? $chosen_methods[ $package_index ] : '';
		foreach ( $package_rates as $rate ) {
			if ( $rate->get_id() === $selected_id ) {
				return array(
					'label' => $rate->get_label(),
					'cost'  => $rate->get_cost(),
				);
			}
		}
	}

	return array(
		'label' => __( 'Shipping', 'etheme' ),
		'cost'  => WC()->cart->get_shipping_total(),
	);
}

/**
 * Resolve terms page URL.
 *
 * @return string
 */
function etheme_checkout_get_terms_url() {
	$terms_url = wc_get_page_permalink( 'terms' );
	return $terms_url ? $terms_url : '#';
}

/**
 * Resolve privacy policy URL.
 *
 * @return string
 */
function etheme_checkout_get_privacy_url() {
	$privacy_url = get_privacy_policy_url();
	return $privacy_url ? $privacy_url : '#';
}
