<?php
/**
 * Order summary component (visible only in step 2).
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render checkout order summary.
 *
 * @return void
 */
function etheme_render_checkout_order_summary() {
	$cart           = WC()->cart;
	$cart_items     = $cart->get_cart();
	$shipping_rates = etheme_checkout_get_shipping_rates();
	$chosen_methods = etheme_checkout_get_chosen_shipping_methods();
	$selected_rate  = etheme_checkout_get_selected_shipping_rate( $shipping_rates, $chosen_methods );
	$total_value    = (float) $cart->get_total( 'edit' );
	?>
	<section class="border border-gray-200 bg-white p-6" aria-labelledby="checkout-order-summary" data-aos="fade-up" data-aos-delay="50">
		<h2 id="checkout-order-summary" class="text-xl font-bold text-gray-900">
			<?php esc_html_e( 'Resumen del pedido', 'etheme' ); ?>
		</h2>

		<div class="mt-5 space-y-4">
			<?php foreach ( $cart_items as $cart_item ) : ?>
				<?php
				$product = isset( $cart_item['data'] ) ? $cart_item['data'] : false;
				if ( ! $product || ! $product->exists() ) {
					continue;
				}
				?>
				<div class="flex items-start justify-between gap-4 text-sm">
					<div>
						<p class="font-semibold text-gray-900"><?php echo esc_html( $product->get_name() ); ?></p>
						<p class="text-gray-500">
							<?php
							printf(
								/* translators: %d: quantity */
								esc_html__( 'Cant. %d', 'etheme' ),
								absint( $cart_item['quantity'] )
							);
							?>
						</p>
					</div>
					<p class="font-semibold text-gray-900">
						<?php echo wp_kses_post( wc_price( $cart_item['line_total'] ) ); ?>
					</p>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="mt-6 space-y-3 border-t border-gray-200 pt-4 text-sm">
			<div class="flex items-center justify-between text-gray-700">
				<span><?php esc_html_e( 'Subtotal', 'etheme' ); ?></span>
				<span class="font-medium text-gray-900"><?php echo wp_kses_post( wc_price( $cart->get_subtotal() ) ); ?></span>
			</div>
			<div class="flex items-center justify-between text-gray-700">
				<span><?php echo esc_html( $selected_rate['label'] ); ?></span>
				<span class="font-medium text-gray-900">
					<?php echo $selected_rate['cost'] > 0 ? wp_kses_post( wc_price( $selected_rate['cost'] ) ) : esc_html__( 'Gratis', 'etheme' ); ?>
				</span>
			</div>
			<div class="flex items-center justify-between border-t border-gray-200 pt-3 text-base font-bold text-gray-900">
				<span><?php esc_html_e( 'Total', 'etheme' ); ?></span>
				<span><?php echo wp_kses_post( wc_price( $total_value ) ); ?></span>
			</div>
		</div>
	</section>
	<?php
}
