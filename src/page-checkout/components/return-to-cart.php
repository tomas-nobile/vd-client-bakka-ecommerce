<?php
/**
 * Return to cart component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render return to cart action.
 *
 * @return void
 */
function etheme_render_checkout_return_to_cart() {
	$cart_url = wc_get_cart_url();
	?>
	<div class="text-center">
		<a class="inline-flex items-center gap-2 text-sm text-gray-500 underline transition hover:text-gray-800" href="<?php echo esc_url( $cart_url ); ?>">
			<svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
				<path d="M12 5L7 10L12 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
			</svg>
			<?php esc_html_e( 'Volver al carrito', 'etheme' ); ?>
		</a>
	</div>
	<?php
}
