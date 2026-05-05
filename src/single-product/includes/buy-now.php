<?php
/**
 * Buy Now redirect.
 *
 * When a product is added to the cart via the "Comprar ahora" button, the
 * single-product form sets bakka_buy_now=1 in the query string (via formaction).
 * WooCommerce fires woocommerce_add_to_cart_redirect after adding the item —
 * this filter sends the user straight to checkout instead of the cart.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_add_to_cart_redirect', 'etheme_buy_now_redirect', 10, 2 );
function etheme_buy_now_redirect( $url, $adding_to_cart ) {
	$bakka_buy_now = isset( $_GET['bakka_buy_now'] )
		? sanitize_text_field( wp_unslash( $_GET['bakka_buy_now'] ) )
		: '';

	if ( '1' === $bakka_buy_now ) {
		// Suppress "added to cart" notices — the user is going straight to checkout.
		wc_clear_notices();
		return wc_get_checkout_url();
	}

	return $url;
}
