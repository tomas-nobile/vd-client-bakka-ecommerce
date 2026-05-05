<?php
/**
 * Cart AJAX Handlers
 *
 * Handles AJAX requests for cart operations.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register AJAX handlers
 */
function etheme_register_cart_ajax_handlers() {
	// Update cart item quantity
	add_action( 'wp_ajax_etheme_update_cart_item', 'etheme_ajax_update_cart_item' );
	add_action( 'wp_ajax_nopriv_etheme_update_cart_item', 'etheme_ajax_update_cart_item' );

	// Remove cart item
	add_action( 'wp_ajax_etheme_remove_cart_item', 'etheme_ajax_remove_cart_item' );
	add_action( 'wp_ajax_nopriv_etheme_remove_cart_item', 'etheme_ajax_remove_cart_item' );

	// Calculate shipping
	add_action( 'wp_ajax_etheme_calculate_shipping', 'etheme_ajax_calculate_shipping' );
	add_action( 'wp_ajax_nopriv_etheme_calculate_shipping', 'etheme_ajax_calculate_shipping' );

	// Update shipping method
	add_action( 'wp_ajax_etheme_update_shipping_method', 'etheme_ajax_update_shipping_method' );
	add_action( 'wp_ajax_nopriv_etheme_update_shipping_method', 'etheme_ajax_update_shipping_method' );

	// Apply coupon
	add_action( 'wp_ajax_etheme_apply_coupon', 'etheme_ajax_apply_coupon' );
	add_action( 'wp_ajax_nopriv_etheme_apply_coupon', 'etheme_ajax_apply_coupon' );

	// Remove coupon
	add_action( 'wp_ajax_etheme_remove_coupon', 'etheme_ajax_remove_coupon' );
	add_action( 'wp_ajax_nopriv_etheme_remove_coupon', 'etheme_ajax_remove_coupon' );
}
add_action( 'init', 'etheme_register_cart_ajax_handlers' );

/**
 * Verify the cart nonce for AJAX requests.
 *
 * @return void Dies with JSON error on failure.
 */
function etheme_verify_cart_nonce() {
	$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'etheme-cart-nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Falló la verificación de seguridad. Recargá la página.', 'etheme' ) ) );
	}
}

/**
 * AJAX handler for updating cart item quantity
 */
function etheme_ajax_update_cart_item() {
	etheme_verify_cart_nonce();

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? wc_clean( wp_unslash( $_POST['cart_item_key'] ) ) : '';
	$quantity      = isset( $_POST['quantity'] ) ? absint( $_POST['quantity'] ) : 0;

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Producto inválido', 'etheme' ) ) );
	}

	$cart_item = WC()->cart->get_cart_item( $cart_item_key );
	if ( ! $cart_item ) {
		wp_send_json_error( array( 'message' => __( 'El producto ya no está en el carrito', 'etheme' ) ) );
	}

	if ( $quantity > 0 ) {
		$product_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
		$product    = wc_get_product( $product_id );

		if ( ! $product || ! $product->is_in_stock() ) {
			wp_send_json_error( array( 'message' => __( 'El producto no tiene stock disponible', 'etheme' ) ) );
		}

		if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
			$stock = $product->get_stock_quantity();
			if ( $quantity > $stock ) {
				wp_send_json_error( array(
					'message' => sprintf(
						/* translators: %d: available stock quantity */
						__( 'Solo hay %d unidades disponibles', 'etheme' ),
						$stock
					),
					'max_qty' => $stock,
				) );
			}
		}
	}

	WC()->cart->set_quantity( $cart_item_key, $quantity );
	WC()->cart->calculate_totals();

	// Get updated cart item
	$cart_item = WC()->cart->get_cart_item( $cart_item_key );
	$line_total_html = '';

	if ( $cart_item ) {
		$line_total_html = wc_price( $cart_item['line_total'] );
	}

	wp_send_json_success( array(
		'message'         => __( 'Carrito actualizado', 'etheme' ),
		'line_total_html' => $line_total_html,
		'cart_totals'     => etheme_get_cart_totals_data(),
		'cart_count'      => WC()->cart->get_cart_contents_count(),
	) );
}

/**
 * AJAX handler for removing cart item
 */
function etheme_ajax_remove_cart_item() {
	etheme_verify_cart_nonce();

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? wc_clean( wp_unslash( $_POST['cart_item_key'] ) ) : '';

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error( array( 'message' => __( 'Producto inválido', 'etheme' ) ) );
	}

	// Remove the cart item
	$removed = WC()->cart->remove_cart_item( $cart_item_key );

	if ( ! $removed ) {
		wp_send_json_error( array( 'message' => __( 'No se pudo eliminar el producto', 'etheme' ) ) );
	}

	WC()->cart->calculate_totals();

	wp_send_json_success( array(
		'message'     => __( 'Producto eliminado', 'etheme' ),
		'cart_totals' => etheme_get_cart_totals_data(),
		'cart_count'  => WC()->cart->get_cart_contents_count(),
		'is_empty'    => WC()->cart->is_empty(),
	) );
}

/**
 * AJAX handler for calculating shipping
 */
function etheme_ajax_calculate_shipping() {
	etheme_verify_cart_nonce();

	$postcode = isset( $_POST['postcode'] ) ? wc_clean( wp_unslash( $_POST['postcode'] ) ) : '';
	$country = isset( $_POST['country'] ) ? wc_clean( wp_unslash( $_POST['country'] ) ) : 'AR';

	if ( empty( $postcode ) ) {
		wp_send_json_error( array( 'message' => __( 'Ingresá un código postal', 'etheme' ) ) );
	}

	// Set customer shipping location
	WC()->customer->set_shipping_postcode( $postcode );
	WC()->customer->set_shipping_country( $country );
	WC()->customer->set_shipping_city( '' );
	WC()->customer->set_shipping_state( '' );

	// Calculate shipping
	WC()->cart->calculate_shipping();
	WC()->cart->calculate_totals();

	// Get shipping packages
	$packages = WC()->shipping()->get_packages();
	$shipping_options = array();

	if ( ! empty( $packages ) ) {
		foreach ( $packages as $package ) {
			if ( ! empty( $package['rates'] ) ) {
				foreach ( $package['rates'] as $rate ) {
					$shipping_options[] = array(
						'id'        => $rate->get_id(),
						'label'     => $rate->get_label(),
						'cost'      => $rate->get_cost(),
						'cost_html' => wc_price( $rate->get_cost() ),
					);
				}
			}
		}
	}

	if ( empty( $shipping_options ) ) {
		wp_send_json_error( array( 
			'message' => __( 'No hay opciones de envío para esta ubicación', 'etheme' ),
		) );
	}

	wp_send_json_success( array(
		'shipping_options' => $shipping_options,
		'cart_totals'      => etheme_get_cart_totals_data(),
	) );
}

/**
 * AJAX handler for updating shipping method
 */
function etheme_ajax_update_shipping_method() {
	etheme_verify_cart_nonce();

	$shipping_method = isset( $_POST['shipping_method'] ) ? wc_clean( wp_unslash( $_POST['shipping_method'] ) ) : '';

	if ( empty( $shipping_method ) ) {
		wp_send_json_error( array( 'message' => __( 'Método de envío inválido', 'etheme' ) ) );
	}

	$packages = WC()->shipping()->get_packages();
	$valid = false;
	foreach ( $packages as $package ) {
		if ( isset( $package['rates'][ $shipping_method ] ) ) {
			$valid = true;
			break;
		}
	}

	if ( ! $valid ) {
		wp_send_json_error( array( 'message' => __( 'Método de envío no disponible', 'etheme' ) ) );
	}

	WC()->session->set( 'chosen_shipping_methods', array( $shipping_method ) );

	WC()->cart->calculate_totals();

	wp_send_json_success( array(
		'cart_totals' => etheme_get_cart_totals_data(),
	) );
}

/**
 * AJAX handler for applying coupon
 */
function etheme_ajax_apply_coupon() {
	etheme_verify_cart_nonce();

	$coupon_code = isset( $_POST['coupon_code'] ) ? wc_clean( wp_unslash( $_POST['coupon_code'] ) ) : '';

	if ( empty( $coupon_code ) ) {
		wp_send_json_error( array( 'message' => __( 'Ingresá un código de cupón', 'etheme' ) ) );
	}

	// Try to apply the coupon
	$applied = WC()->cart->apply_coupon( $coupon_code );

	if ( ! $applied ) {
		wp_send_json_error( array( 
			'message' => __( 'Código de cupón inválido', 'etheme' ),
		) );
	}

	WC()->cart->calculate_totals();
	$discount = WC()->cart->get_coupon_discount_amount( $coupon_code );

	wp_send_json_success( array(
		'message'       => __( 'Cupón aplicado con éxito', 'etheme' ),
		'discount_html' => wc_price( $discount ),
		'cart_totals'   => etheme_get_cart_totals_data(),
	) );
}

/**
 * AJAX handler for removing coupon
 */
function etheme_ajax_remove_coupon() {
	etheme_verify_cart_nonce();

	$coupon_code = isset( $_POST['coupon_code'] ) ? wc_clean( wp_unslash( $_POST['coupon_code'] ) ) : '';

	if ( empty( $coupon_code ) ) {
		wp_send_json_error( array( 'message' => __( 'Cupón inválido', 'etheme' ) ) );
	}

	WC()->cart->remove_coupon( $coupon_code );
	WC()->cart->calculate_totals();

	wp_send_json_success( array(
		'message'     => __( 'Cupón eliminado', 'etheme' ),
		'cart_totals' => etheme_get_cart_totals_data(),
	) );
}

/**
 * Get cart totals data for AJAX responses
 *
 * @return array Cart totals data.
 */
function etheme_get_cart_totals_data() {
	$cart = WC()->cart;

	return array(
		'subtotal'      => $cart->get_subtotal(),
		'subtotal_html' => wc_price( $cart->get_subtotal() ),
		'discount'      => $cart->get_discount_total(),
		'discount_html' => wc_price( $cart->get_discount_total() ),
		'shipping'      => $cart->get_shipping_total(),
		'shipping_html' => $cart->get_shipping_total() > 0 
			? wc_price( $cart->get_shipping_total() ) 
			: __( 'Gratis', 'etheme' ),
		'tax'           => $cart->get_total_tax(),
		'tax_html'      => wc_price( $cart->get_total_tax() ),
		'total'         => $cart->get_total( 'edit' ),
		'total_html'    => wc_price( $cart->get_total( 'edit' ) ),
	);
}
