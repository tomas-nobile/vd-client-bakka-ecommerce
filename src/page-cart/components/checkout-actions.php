<?php
/**
 * Checkout Actions Component
 *
 * Desktop: inline checkout button + continue shopping link.
 * Mobile: "Seguir comprando" link inline + floating sticky checkout button at bottom.
 *
 * @param bool $show_continue_shopping Whether to show continue shopping link.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_checkout_actions( $show_continue_shopping = true ) {
	$checkout_url = wc_get_checkout_url();
	$shop_url = wc_get_page_permalink( 'shop' );
	?>

	<div class="checkout-actions mt-6">

		<!-- Continue Shopping (visible everywhere) -->
		<?php if ( $show_continue_shopping ) : ?>
		<p class="text-center lg:text-left mb-4 lg:mb-0 lg:order-last">
			<a href="<?php echo esc_url( $shop_url ); ?>"
			   class="text-sm text-gray-500 hover:text-gray-700 transition no-underline">
				<?php esc_html_e( 'Seguir comprando', 'etheme' ); ?>
			</a>
		</p>
		<?php endif; ?>

		<!-- Desktop checkout button (hidden on mobile) -->
		<a href="<?php echo esc_url( $checkout_url ); ?>"
		   class="checkout-btn hidden lg:block w-full px-6 py-4 text-center font-bold text-white bg-[#fb704f] hover:bg-[#e85d3e] rounded-none transition duration-200 mt-4 no-underline">
			<?php esc_html_e( 'Finalizar compra', 'etheme' ); ?>
		</a>
	</div>

	<!-- Mobile floating checkout button -->
	<div class="cart-floating-checkout fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-white border-t border-gray-200 px-4 py-3 shadow-[0_-4px_12px_rgba(0,0,0,0.08)]">
		<a href="<?php echo esc_url( $checkout_url ); ?>"
		   class="block w-full px-6 py-3.5 text-center font-bold text-white bg-[#fb704f] hover:bg-[#e85d3e] rounded-none transition duration-200 text-sm no-underline">
			<?php esc_html_e( 'Finalizar compra', 'etheme' ); ?>
		</a>
	</div>
	<?php
}
