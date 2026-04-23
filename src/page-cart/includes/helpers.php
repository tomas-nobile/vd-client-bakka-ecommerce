<?php
/**
 * Cart Helper Functions
 *
 * Utility functions for cart page components.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get formatted cart item data (variation attributes)
 *
 * @param array $cart_item Cart item data.
 * @return array Array of formatted attributes.
 */
function etheme_get_cart_item_attributes( $cart_item ) {
	$attributes = array();
	
	if ( ! empty( $cart_item['variation'] ) ) {
		foreach ( $cart_item['variation'] as $key => $value ) {
			$attribute_name = wc_attribute_label( str_replace( 'attribute_', '', $key ) );
			$attributes[] = array(
				'name'  => $attribute_name,
				'value' => $value,
			);
		}
	}
	
	return $attributes;
}

/**
 * Get cart item stock status information
 *
 * @param WC_Product $product Product object.
 * @return array Stock status info with 'status' and 'message' keys.
 */
function etheme_get_stock_status_info( $product ) {
	$stock_status = $product->get_stock_status();
	$stock_quantity = $product->get_stock_quantity();
	
	$info = array(
		'status'  => $stock_status,
		'message' => '',
		'icon'    => '',
	);
	
	switch ( $stock_status ) {
		case 'instock':
			$info['message'] = __( 'En stock', 'etheme' );
			$info['icon']    = 'check';
			break;
		case 'outofstock':
			$info['message'] = __( 'Sin stock', 'etheme' );
			$info['icon']    = 'x';
			break;
		case 'onbackorder':
			$info['message'] = __( 'Envío en 3-4 semanas', 'etheme' );
			$info['icon']    = 'clock';
			break;
	}
	
	if ( $stock_quantity !== null && $stock_quantity <= 5 && $stock_status === 'instock' ) {
		/* translators: %d: stock quantity */
		$info['message'] = sprintf( __( 'Quedan solo %d en stock', 'etheme' ), $stock_quantity );
	}
	
	return $info;
}

/**
 * Get the cart update nonce
 *
 * @return string Nonce value.
 */
function etheme_get_cart_nonce() {
	return wp_create_nonce( 'etheme-cart-nonce' );
}

/**
 * Calculate shipping for a postal code
 *
 * @param string $postal_code Postal code.
 * @param string $country Country code (default: AR for Argentina).
 * @return array Shipping options with rates.
 */
function etheme_calculate_shipping_for_postal_code( $postal_code, $country = 'AR' ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}
	
	// Store original customer data
	$original_postcode = WC()->customer->get_shipping_postcode();
	$original_country = WC()->customer->get_shipping_country();
	
	// Set temporary customer location
	WC()->customer->set_shipping_postcode( wc_clean( $postal_code ) );
	WC()->customer->set_shipping_country( wc_clean( $country ) );
	
	// Calculate shipping
	WC()->cart->calculate_shipping();
	WC()->cart->calculate_totals();
	
	$packages = WC()->shipping()->get_packages();
	$shipping_options = array();
	
	if ( ! empty( $packages ) ) {
		foreach ( $packages as $package ) {
			if ( ! empty( $package['rates'] ) ) {
				foreach ( $package['rates'] as $rate ) {
					$shipping_options[] = array(
						'id'       => $rate->get_id(),
						'label'    => $rate->get_label(),
						'cost'     => $rate->get_cost(),
						'cost_html' => wc_price( $rate->get_cost() ),
					);
				}
			}
		}
	}
	
	// Restore original customer data
	WC()->customer->set_shipping_postcode( $original_postcode );
	WC()->customer->set_shipping_country( $original_country );
	
	return $shipping_options;
}

/**
 * Get available countries for shipping
 *
 * @return array Countries with code and name.
 */
function etheme_get_shipping_countries() {
	$countries = WC()->countries->get_shipping_countries();
	$result = array();
	
	foreach ( $countries as $code => $name ) {
		$result[] = array(
			'code' => $code,
			'name' => $name,
		);
	}
	
	return $result;
}

/**
 * Format cart item price based on quantity
 *
 * @param array $cart_item Cart item.
 * @return array Price info with unit_price, quantity, and line_total.
 */
function etheme_get_cart_item_price_info( $cart_item ) {
	$product = $cart_item['data'];
	$quantity = $cart_item['quantity'];
	
	return array(
		'unit_price'      => $product->get_price(),
		'unit_price_html' => wc_price( $product->get_price() ),
		'quantity'        => $quantity,
		'line_total'      => $cart_item['line_total'],
		'line_total_html' => wc_price( $cart_item['line_total'] ),
	);
}
