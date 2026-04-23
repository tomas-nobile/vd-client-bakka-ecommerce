<?php
/**
 * Product Cart Component
 *
 * Renders a single cart item. Compact card on mobile (Nike-style), expanded on desktop.
 *
 * @param array  $cart_item     Cart item data.
 * @param string $cart_item_key Cart item key.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_product_cart( $cart_item, $cart_item_key ) {
	$product = $cart_item['data'];

	if ( ! $product || ! $product->exists() ) {
		return;
	}

	$product_permalink = $product->is_visible() ? $product->get_permalink( $cart_item ) : '';
	$product_name = $product->get_name();
	$thumbnail = $product->get_image( 'woocommerce_thumbnail', array(
		'class' => 'w-full h-full object-cover rounded',
	) );

	$price_info = etheme_get_cart_item_price_info( $cart_item );
	$attributes = etheme_get_cart_item_attributes( $cart_item );

	$min_qty = $product->get_min_purchase_quantity();
	$max_qty = $product->get_max_purchase_quantity();
	$quantity = $cart_item['quantity'];
	$remove_url = wc_get_cart_remove_url( $cart_item_key );
	?>

	<div class="cart-item py-4 lg:py-6 border-b border-gray-200"
		 data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
		 data-product-id="<?php echo esc_attr( $cart_item['product_id'] ); ?>">

		<!-- Compact mobile layout (Nike-style) -->
		<div class="flex gap-3 lg:gap-6">

			<!-- Thumbnail -->
			<div class="w-20 h-20 lg:w-28 lg:h-28 flex-shrink-0">
				<?php if ( $product_permalink ) : ?>
					<a href="<?php echo esc_url( $product_permalink ); ?>" class="block w-full h-full">
						<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				<?php else : ?>
					<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<?php endif; ?>
			</div>

			<!-- Info + controls -->
			<div class="flex-1 min-w-0">

				<!-- Top row: name + remove -->
				<div class="flex items-start justify-between gap-2">
					<div class="min-w-0">
						<?php if ( $product_permalink ) : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>"
							   class="block text-sm lg:text-base font-medium text-gray-900 hover:text-gray-700 transition truncate no-underline">
								<?php echo esc_html( $product_name ); ?>
							</a>
						<?php else : ?>
							<span class="block text-sm lg:text-base font-medium text-gray-900 truncate">
								<?php echo esc_html( $product_name ); ?>
							</span>
						<?php endif; ?>

						<!-- Attributes inline -->
						<?php if ( ! empty( $attributes ) ) : ?>
							<p class="text-xs lg:text-sm text-gray-500 mt-0.5">
								<?php
								$attr_parts = array();
								foreach ( $attributes as $attr ) {
									$attr_parts[] = esc_html( $attr['name'] ) . ': ' . esc_html( $attr['value'] );
								}
								echo implode( ' &middot; ', $attr_parts );
								?>
							</p>
						<?php endif; ?>
					</div>

					<!-- Remove button (trash icon) -->
					<a href="<?php echo esc_url( $remove_url ); ?>"
					   class="remove-item flex-shrink-0 p-1 text-gray-400 hover:text-gray-700 transition"
					   data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
					   aria-label="<?php esc_attr_e( 'Eliminar este producto', 'etheme' ); ?>">
						<svg class="w-4 h-4 lg:w-5 lg:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
								  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
						</svg>
					</a>
				</div>

				<!-- Bottom row: qty selector + price -->
				<div class="flex items-center justify-between mt-3">
					<?php etheme_render_quantity_selector( $cart_item_key, $quantity, $min_qty, $max_qty ); ?>
					<span class="line-total text-sm lg:text-base font-bold text-gray-900 ml-3">
						<?php echo wp_kses_post( $price_info['line_total_html'] ); ?>
					</span>
				</div>

			</div>
		</div>
	</div>
	<?php
}

/**
 * Render quantity selector for cart item
 *
 * @param string $cart_item_key Cart item key.
 * @param int    $quantity      Current quantity.
 * @param int    $min_qty       Minimum quantity.
 * @param int    $max_qty       Maximum quantity.
 */
function etheme_render_quantity_selector( $cart_item_key, $quantity, $min_qty, $max_qty ) {
	?>
	<div class="quantity-selector inline-flex items-center border border-gray-300 rounded overflow-hidden"
		 data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
		 data-min="<?php echo esc_attr( $min_qty ); ?>"
		 data-max="<?php echo esc_attr( $max_qty > 0 ? $max_qty : '' ); ?>">
		<button type="button"
				class="qty-btn qty-decrease w-9 h-9 lg:w-10 lg:h-10 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition disabled:opacity-40"
				<?php echo $quantity <= $min_qty ? 'disabled' : ''; ?>
				aria-label="<?php esc_attr_e( 'Disminuir cantidad', 'etheme' ); ?>">
			<span class="text-base leading-none pointer-events-none">&minus;</span>
		</button>

		<input type="text"
			   class="qty-input w-9 h-9 lg:w-10 lg:h-10 text-center text-sm font-medium text-gray-900 bg-transparent border-x border-gray-300 focus:outline-none"
			   value="<?php echo esc_attr( $quantity ); ?>"
			   inputmode="numeric"
			   pattern="[0-9]*"
			   aria-label="<?php esc_attr_e( 'Cantidad', 'etheme' ); ?>" />

		<button type="button"
				class="qty-btn qty-increase w-9 h-9 lg:w-10 lg:h-10 flex items-center justify-center text-gray-600 hover:bg-gray-100 transition disabled:opacity-40"
				<?php echo $max_qty > 0 && $quantity >= $max_qty ? 'disabled' : ''; ?>
				aria-label="<?php esc_attr_e( 'Aumentar cantidad', 'etheme' ); ?>">
			<span class="text-base leading-none pointer-events-none">+</span>
		</button>
	</div>
	<?php
}

/**
 * Render stock status icon
 *
 * @param string $icon Icon type (check, x, clock).
 */
function etheme_render_stock_icon( $icon ) {
	switch ( $icon ) {
		case 'check':
			?>
			<svg width="14" height="10" viewBox="0 0 14 10" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M13.7071 0.292893C14.0976 0.683417 14.0976 1.31658 13.7071 1.70711L5.70711 9.70711C5.31658 10.0976 4.68342 10.0976 4.29289 9.70711L0.292893 5.70711C-0.0976311 5.31658 -0.0976311 4.68342 0.292893 4.29289C0.683417 3.90237 1.31658 3.90237 1.70711 4.29289L5 7.58579L12.2929 0.292893C12.6834 -0.0976311 13.3166 -0.0976311 13.7071 0.292893Z" fill="#22C55E"/>
			</svg>
			<?php
			break;
		case 'x':
			?>
			<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M13 1L1 13M1 1L13 13" stroke="#EF4444" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<?php
			break;
		case 'clock':
			?>
			<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M8 16C12.4183 16 16 12.4183 16 8C16 3.58172 12.4183 0 8 0C3.58172 0 0 3.58172 0 8C0 12.4183 3.58172 16 8 16ZM9 4C9 3.44772 8.55229 3 8 3C7.44772 3 7 3.44772 7 4V8C7 8.26522 7.10536 8.51957 7.29289 8.70711L10.1213 11.5355C10.5118 11.9261 11.145 11.9261 11.5355 11.5355C11.9261 11.145 11.9261 10.5118 11.5355 10.1213L9 7.58579V4Z" fill="#F59E0B"/>
			</svg>
			<?php
			break;
	}
}
