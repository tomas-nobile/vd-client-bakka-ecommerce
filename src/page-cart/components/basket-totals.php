<?php
/**
 * Basket Totals Component
 *
 * Renders the cart totals section with title, subtotal, discount, taxes, and total (no shipping line).
 *
 * @param WC_Cart $cart WooCommerce cart object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_basket_totals( $cart ) {
	$subtotal = $cart->get_subtotal();
	$discount_total = $cart->get_discount_total();
	$tax_total = $cart->get_total_tax();
	$total = $cart->get_total( 'edit' );
	$show_taxes = wc_tax_enabled() && ! WC()->cart->display_prices_including_tax();
	?>

	<div class="basket-totals" id="basket-totals">

		<!-- Subtotal -->
		<div class="flex items-center justify-between py-2">
			<span class="text-sm text-gray-600">
				<?php esc_html_e( 'Subtotal', 'etheme' ); ?>
			</span>
			<span class="subtotal-value text-sm text-gray-900">
				<?php echo wp_kses_post( wc_price( $subtotal ) ); ?>
			</span>
		</div>

		<!-- Discount -->
		<?php if ( $discount_total > 0 ) : ?>
		<div class="flex items-center justify-between py-2">
			<span class="text-sm text-green-600">
				<?php esc_html_e( 'Descuento', 'etheme' ); ?>
			</span>
			<span class="discount-value text-sm font-medium text-green-600">
				-<?php echo wp_kses_post( wc_price( $discount_total ) ); ?>
			</span>
		</div>
		<?php endif; ?>

		<!-- Tax -->
		<?php if ( $show_taxes && $tax_total > 0 ) : ?>
		<div class="flex items-center justify-between py-2">
			<span class="text-sm text-gray-600">
				<?php esc_html_e( 'Impuestos', 'etheme' ); ?>
			</span>
			<span class="tax-value text-sm text-gray-900">
				<?php echo wp_kses_post( wc_price( $tax_total ) ); ?>
			</span>
		</div>
		<?php endif; ?>

		<!-- Total -->
		<div class="flex items-center justify-between pt-3 mt-2 border-t border-gray-300">
			<span class="text-base font-bold text-gray-900">
				<?php esc_html_e( 'Total', 'etheme' ); ?>
			</span>
			<span class="total-value text-lg font-bold text-gray-900">
				<?php echo wp_kses_post( wc_price( $total ) ); ?>
			</span>
		</div>

	</div>
	<?php
}
