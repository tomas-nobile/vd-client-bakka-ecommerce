<?php
/**
 * Cart Header Component
 *
 * Renders the cart page header with title and item count.
 * Compact on mobile, prominent on desktop.
 *
 * @param WC_Cart $cart WooCommerce cart object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_cart_header( $cart ) {
	$item_count = $cart->get_cart_contents_count();
	?>
	<header class="cart-header mb-6 lg:mb-12">
		<h1 class="text-xl lg:text-4xl text-gray-900 font-bold">
			<?php esc_html_e( 'Shopping Cart', 'etheme' ); ?>
			<?php if ( $item_count > 0 ) : ?>
				<span class="text-sm lg:text-base font-normal text-gray-500" id="cart-item-count">
					(<?php echo esc_html( $item_count ); ?>)
				</span>
			<?php endif; ?>
		</h1>
	</header>
	<?php
}
