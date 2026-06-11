<?php
/**
 * Checkout leads — marketing layer.
 *
 * Upsert by email, signed tokens, cart-recovery URL + endpoint, and the
 * order hook that marks leads as "purchased" so campaigns never target buyers.
 *
 * Production constraint: the purchase hook runs inside a real order flow —
 * it must never throw past its own boundary.
 *
 * @package Etheme
 * @see specs/23.checkout-leads.md
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Find a lead by email.
 *
 * @param string $email Sanitized email.
 * @return int Lead post ID, or 0.
 */
function etheme_lead_find_by_email( $email ) {
	$posts = get_posts( array(
		'post_type'      => 'etheme_checkout_lead',
		'post_status'    => 'publish',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_lead_email', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => $email, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		'no_found_rows'  => true,
	) );
	return $posts ? (int) $posts[0] : 0;
}

/**
 * Set a lead status without ever downgrading (interested < contacted < recovered < purchased).
 *
 * @param int    $lead_id Lead ID.
 * @param string $status  New status slug.
 * @return bool True if the status changed.
 */
function etheme_lead_set_status( $lead_id, $status ) {
	if ( ! array_key_exists( $status, etheme_lead_get_statuses() ) ) {
		return false;
	}
	$current = (string) get_post_meta( $lead_id, '_lead_status', true );
	if ( $current && etheme_lead_status_rank( $status ) <= etheme_lead_status_rank( $current ) ) {
		return false;
	}
	update_post_meta( $lead_id, '_lead_status', $status );
	return true;
}

/**
 * Create or update a lead from the current cart snapshot (upsert by email).
 *
 * @param string $email Sanitized, validated email.
 * @param string $name  Sanitized first name (may be empty).
 * @param array  $items Cart items snapshot.
 * @param float  $total Cart total.
 * @return int Lead ID, or 0 on failure.
 */
function etheme_lead_upsert( $email, $name, $items, $total ) {
	$now      = current_time( 'mysql' );
	$currency = function_exists( 'get_woocommerce_currency' ) ? get_woocommerce_currency() : '';
	$lead_id  = etheme_lead_find_by_email( $email );

	if ( ! $lead_id ) {
		$lead_id = wp_insert_post( array(
			'post_type'   => 'etheme_checkout_lead',
			'post_status' => 'publish',
			'post_title'  => $email,
		), true );

		if ( is_wp_error( $lead_id ) || ! $lead_id ) {
			return 0;
		}

		update_post_meta( $lead_id, '_lead_email', $email );
		update_post_meta( $lead_id, '_lead_status', 'interested' );
		update_post_meta( $lead_id, '_lead_captured_at', $now );
		update_post_meta( $lead_id, '_lead_source', 'checkout_step1' );
	}

	if ( $name ) {
		update_post_meta( $lead_id, '_lead_name', $name );
	}
	update_post_meta( $lead_id, '_lead_items', $items );
	update_post_meta( $lead_id, '_lead_total', $total );
	update_post_meta( $lead_id, '_lead_currency', $currency );
	update_post_meta( $lead_id, '_lead_updated_at', $now );
	update_post_meta( $lead_id, '_lead_recovery_url', etheme_lead_get_recovery_url( $lead_id ) );

	return (int) $lead_id;
}

/* -------------------------------------------------------------------------
 * Signed tokens + recovery URL
 * ---------------------------------------------------------------------- */

/**
 * Signed token for public lead URLs (recovery / unsubscribe).
 *
 * @param int    $lead_id Lead ID.
 * @param string $action  Token namespace ('recover' | 'unsub').
 * @return string
 */
function etheme_lead_token( $lead_id, $action ) {
	return substr( hash_hmac( 'sha256', $action . '|' . (int) $lead_id, wp_salt( 'auth' ) ), 0, 20 );
}

/**
 * Constant-time token verification.
 *
 * @param int    $lead_id Lead ID.
 * @param string $action  Token namespace.
 * @param string $token   Token from the request.
 * @return bool
 */
function etheme_lead_verify_token( $lead_id, $action, $token ) {
	return hash_equals( etheme_lead_token( $lead_id, $action ), (string) $token );
}

/**
 * Public URL that repopulates the cart with the lead's saved items.
 *
 * @param int $lead_id Lead ID.
 * @return string
 */
function etheme_lead_get_recovery_url( $lead_id ) {
	$base = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
	return add_query_arg(
		array(
			'etheme_recover' => (int) $lead_id,
			't'              => etheme_lead_token( $lead_id, 'recover' ),
		),
		$base
	);
}

/**
 * Public URL that unsubscribes the lead from campaign emails.
 *
 * @param int $lead_id Lead ID.
 * @return string
 */
function etheme_lead_get_unsubscribe_url( $lead_id ) {
	return add_query_arg(
		array(
			'etheme_unsub' => (int) $lead_id,
			't'            => etheme_lead_token( $lead_id, 'unsub' ),
		),
		home_url( '/' )
	);
}

/* -------------------------------------------------------------------------
 * Cart recovery endpoint
 * ---------------------------------------------------------------------- */

/**
 * Repopulate the cart from a recovery link, apply the campaign coupon and
 * land on the cart page. Invalid/forged links fall through to the normal page.
 */
function etheme_lead_recovery_endpoint() {
	if ( empty( $_GET['etheme_recover'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification.Recommended -- public link, authenticated by signed token.
	$lead_id = absint( wp_unslash( $_GET['etheme_recover'] ) );
	$token   = isset( $_GET['t'] ) ? sanitize_text_field( wp_unslash( $_GET['t'] ) ) : '';
	// phpcs:enable

	if ( ! $lead_id || ! etheme_lead_verify_token( $lead_id, 'recover', $token ) ) {
		return;
	}
	if ( 'etheme_checkout_lead' !== get_post_type( $lead_id ) ) {
		return;
	}

	try {
		$items = get_post_meta( $lead_id, '_lead_items', true );
		if ( is_array( $items ) ) {
			foreach ( $items as $item ) {
				$product_id   = isset( $item['product_id'] ) ? absint( $item['product_id'] ) : 0;
				$variation_id = isset( $item['variation_id'] ) ? absint( $item['variation_id'] ) : 0;
				$quantity     = isset( $item['quantity'] ) ? max( 1, absint( $item['quantity'] ) ) : 1;

				if ( ! $product_id ) {
					continue;
				}
				$product = wc_get_product( $variation_id ? $variation_id : $product_id );
				if ( ! $product || ! $product->is_purchasable() || ! $product->is_in_stock() ) {
					continue;
				}

				// Skip items already in the cart so reopening the link doesn't double quantities.
				$cart_item_id = WC()->cart->generate_cart_id( $product_id, $variation_id );
				if ( WC()->cart->find_product_in_cart( $cart_item_id ) ) {
					continue;
				}

				WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );
			}
		}

		$coupon_code = (string) get_post_meta( $lead_id, '_lead_recovery_coupon', true );
		if ( $coupon_code && ! WC()->cart->has_discount( $coupon_code ) ) {
			$coupon = new WC_Coupon( $coupon_code );
			if ( $coupon->get_id() ) {
				WC()->cart->apply_coupon( $coupon_code );
			}
		}

		etheme_lead_set_status( $lead_id, 'recovered' );
	} catch ( Throwable $e ) {
		error_log( 'etheme_lead_recovery_endpoint: ' . $e->getMessage() );
	}

	wp_safe_redirect( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) );
	exit;
}
add_action( 'template_redirect', 'etheme_lead_recovery_endpoint', 5 );

/* -------------------------------------------------------------------------
 * Purchase hook — exclude buyers from campaigns automatically
 * ---------------------------------------------------------------------- */

/**
 * Mark the matching lead as "purchased" when an order is created/confirmed.
 *
 * Runs inside the real checkout flow: any failure is logged and swallowed
 * so it can never interrupt the order.
 *
 * @param int $order_id Order ID.
 */
function etheme_lead_mark_purchased_on_order( $order_id ) {
	try {
		if ( ! $order_id || ! function_exists( 'wc_get_order' ) ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}
		$email = sanitize_email( $order->get_billing_email() );
		if ( ! is_email( $email ) ) {
			return;
		}
		$lead_id = etheme_lead_find_by_email( $email );
		if ( $lead_id ) {
			etheme_lead_set_status( $lead_id, 'purchased' );
		}
	} catch ( Throwable $e ) {
		error_log( 'etheme_lead_mark_purchased_on_order: ' . $e->getMessage() );
	}
}
add_action( 'woocommerce_checkout_order_processed', 'etheme_lead_mark_purchased_on_order', 10, 1 );
add_action( 'woocommerce_thankyou', 'etheme_lead_mark_purchased_on_order', 10, 1 );
