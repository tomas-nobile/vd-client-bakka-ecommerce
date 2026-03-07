<?php
/**
 * Checkout header component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render checkout page header.
 *
 * @param WC_Cart $cart WooCommerce cart object.
 * @return void
 */
function etheme_render_checkout_header( $cart ) {
	$item_count = $cart ? $cart->get_cart_contents_count() : 0;
	?>
	<header class="mb-8 border-b border-gray-200 pb-6">
		<h1 class="text-3xl font-bold tracking-tight text-gray-900 md:text-4xl">
			<?php esc_html_e( 'Checkout', 'etheme' ); ?>
		</h1>
		<p class="mt-2 text-sm text-gray-500">
			<?php
			printf(
				/* translators: %d: number of cart items */
				esc_html__( '%d item(s) ready to purchase', 'etheme' ),
				absint( $item_count )
			);
			?>
		</p>
	</header>
	<?php
}
