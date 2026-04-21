<?php
/**
 * Order summary — Contrive checkout.html pattern:
 * .cart-total-outer > .top-heading + .list-items > .each-item (.product-items / .product-prices).
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Short plain-text line for a cart line (product short description).
 *
 * @param WC_Product|false $product Product instance.
 * @return string
 */
function etheme_checkout_order_item_summary_text( $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return '';
	}
	$raw = $product->get_short_description();
	if ( '' === (string) $raw ) {
		return '';
	}
	$plain = wp_strip_all_tags( $raw );
	return $plain ? (string) wp_trim_words( $plain, 18, '…' ) : '';
}

/**
 * Render checkout order summary (Contrive cart-total-outer).
 *
 * @return void
 */
function etheme_render_checkout_order_summary() {
	$cart           = WC()->cart;
	$shipping_rates = etheme_checkout_get_shipping_rates();
	$chosen_methods = etheme_checkout_get_chosen_shipping_methods();
	$selected_rate  = etheme_checkout_get_selected_shipping_rate( $shipping_rates, $chosen_methods );
	?>
	<div
		class="cart-total-outer checkout-order-summary"
		role="region"
		aria-labelledby="checkout-order-summary-title"
	>
		<div class="top-heading">
			<span class="product-items" id="checkout-order-summary-title"><?php esc_html_e( 'Artículos', 'etheme' ); ?></span>
			<span class="product-prices"><?php esc_html_e( 'Precio', 'etheme' ); ?></span>
		</div>

		<div class="list-items">
			<?php foreach ( $cart->get_cart() as $cart_item ) : ?>
				<?php
				$product = isset( $cart_item['data'] ) ? $cart_item['data'] : false;
				if ( ! $product || ! $product->exists() ) {
					continue;
				}
				$title = sprintf(
					/* translators: 1: quantity, 2: product name */
					__( '%1$s × %2$s', 'etheme' ),
					absint( $cart_item['quantity'] ),
					$product->get_name()
				);
				$desc = etheme_checkout_order_item_summary_text( $product );
				?>
				<div class="each-item">
					<div class="product-items">
						<span class="heading"><?php echo esc_html( $title ); ?></span>
						<?php if ( '' !== $desc ) : ?>
							<p class="checkout-order-summary__desc"><?php echo esc_html( $desc ); ?></p>
						<?php endif; ?>
					</div>
					<div class="product-prices">
						<span class="dollar"><?php echo wp_kses_post( wc_price( $cart_item['line_total'] ) ); ?></span>
					</div>
				</div>
			<?php endforeach; ?>

			<?php if ( $cart->needs_shipping() ) : ?>
				<?php
				$ship_html = $selected_rate['cost'] > 0
					? wc_price( $selected_rate['cost'] )
					: esc_html__( 'Gratis', 'etheme' );
				?>
				<div class="each-item each-item--shipping">
					<div class="product-items">
						<span class="heading"><?php echo esc_html( $selected_rate['label'] ); ?></span>
					</div>
					<div class="product-prices">
						<span class="dollar"><?php echo wp_kses_post( $ship_html ); ?></span>
					</div>
				</div>
			<?php endif; ?>

			<div class="each-item each-item--grand-total">
				<div class="product-items">
					<span class="heading"><?php esc_html_e( 'Total', 'etheme' ); ?></span>
				</div>
				<div class="product-prices">
					<span class="dollar total-price"><?php echo wp_kses_post( wc_price( (float) $cart->get_total( 'edit' ) ) ); ?></span>
				</div>
			</div>
		</div>
	</div>
	<?php
}
