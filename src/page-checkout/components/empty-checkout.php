<?php
/**
 * Empty checkout state component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render empty checkout state.
 *
 * @return void
 */
function etheme_render_checkout_empty_state() {
	$cart_url = wc_get_cart_url();
	$shop_url = wc_get_page_permalink( 'shop' );
	?>
	<section class="rounded-2xl border border-gray-200 bg-white px-6 py-12 text-center md:px-10">
		<div class="mx-auto mb-6 h-16 w-16 rounded-full bg-gray-100 p-4 text-gray-500">
			<svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
				<path d="M7 8H21L19.4 16.4C19.2 17.3 18.4 18 17.5 18H10.2C9.2 18 8.4 17.3 8.2 16.4L7 8Z" stroke="currentColor" stroke-width="1.8"></path>
				<path d="M7 8L6.3 5.3C6.1 4.5 5.3 4 4.5 4H3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"></path>
				<circle cx="10.5" cy="20" r="1.2" fill="currentColor"></circle>
				<circle cx="17" cy="20" r="1.2" fill="currentColor"></circle>
			</svg>
		</div>
		<h2 class="text-2xl font-bold text-gray-900"><?php esc_html_e( 'Your cart is empty', 'etheme' ); ?></h2>
		<p class="mx-auto mt-2 max-w-xl text-sm text-gray-500">
			<?php esc_html_e( 'Add products to your cart before completing checkout.', 'etheme' ); ?>
		</p>

		<div class="mt-8 flex flex-wrap items-center justify-center gap-3">
			<a href="<?php echo esc_url( $shop_url ); ?>" class="rounded-full bg-gray-900 px-6 py-3 text-sm font-bold uppercase tracking-wide text-white transition hover:bg-black">
				<?php esc_html_e( 'Start shopping', 'etheme' ); ?>
			</a>
			<a href="<?php echo esc_url( $cart_url ); ?>" class="rounded-full border border-gray-300 px-6 py-3 text-sm font-semibold text-gray-700 transition hover:border-gray-900 hover:text-gray-900">
				<?php esc_html_e( 'Go to cart', 'etheme' ); ?>
			</a>
		</div>
	</section>
	<?php
}
