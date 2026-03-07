<?php
/**
 * Empty Cart Component
 *
 * Renders the empty cart state with message and return to shop button.
 *
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_empty_cart() {
	$shop_url = wc_get_page_permalink( 'shop' );
	?>
	
	<div class="empty-cart text-center py-16">
		<!-- Empty Cart Icon -->
		<div class="mb-8">
			<svg class="w-24 h-24 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
			</svg>
		</div>
		
		<!-- Empty Cart Message -->
		<h2 class="text-2xl font-bold text-gray-900 mb-3">
			<?php esc_html_e( 'Your cart is empty', 'etheme' ); ?>
		</h2>
		<p class="text-gray-500 mb-8 max-w-md mx-auto">
			<?php esc_html_e( 'Looks like you haven\'t added any products to your cart yet. Browse our store and discover amazing products!', 'etheme' ); ?>
		</p>
		
		<!-- Return to Shop Button -->
		<a href="<?php echo esc_url( $shop_url ); ?>" 
		   class="inline-flex items-center px-8 py-4 bg-amber-500 text-white font-bold rounded hover:bg-amber-600 transition duration-200">
			<svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
			</svg>
			<?php esc_html_e( 'Return to Shop', 'etheme' ); ?>
		</a>
	</div>
	<?php
}
