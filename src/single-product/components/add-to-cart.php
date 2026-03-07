<?php
/**
 * Add to Cart Component
 *
 * Renders quantity selector and add to cart button.
 *
 * @param WC_Product $product WooCommerce product object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_add_to_cart( $product ) {
	if ( ! $product->is_purchasable() ) {
		return;
	}
	
	$is_variable = $product->is_type( 'variable' );
	$is_in_stock = $product->is_in_stock();
	$min_qty = $product->get_min_purchase_quantity();
	$max_qty = $product->get_max_purchase_quantity();
	$step = 1;
	
	// For variable products, button starts disabled until variation is selected
	$button_disabled = $is_variable || ! $is_in_stock;
	$button_text = $is_variable ? __( 'Select options', 'etheme' ) : __( 'Add to Cart', 'etheme' );
	
	if ( ! $is_in_stock && ! $is_variable ) {
		$button_text = __( 'Out of Stock', 'etheme' );
	}

	$form_attr = $is_variable ? ' form="variations-form"' : '';
	?>
	
	<div class="add-to-cart-wrapper mt-8">
		
		<?php if ( ! $is_variable ) : ?>
		<form class="cart" method="post" enctype="multipart/form-data" id="add-to-cart-form">
		<?php endif; ?>
		
		<?php if ( $is_in_stock || $is_variable ) : ?>
			
			<!-- Quantity + Add to Cart -->
			<div class="quantity-wrapper flex flex-wrap md:flex-nowrap items-stretch gap-3 mb-6">
				<label for="quantity" class="sr-only">
					<?php esc_html_e( 'Quantity', 'etheme' ); ?>
				</label>
				
				<div class="w-full md:w-auto">
					<div class="quantity-input inline-flex items-center justify-between w-full md:w-auto py-3 px-4 rounded-sm border border-gray-200 gap-4">
						<button type="button"
								class="quantity-btn decrement cursor-pointer text-gray-500 hover:text-gray-900 transition duration-200"
								id="qty-decrement"
								aria-label="<?php esc_attr_e( 'Decrease quantity', 'etheme' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewBox="0 0 16 17" fill="none">
								<path d="M12.6667 7.49988H3.33341C3.1566 7.49988 2.98703 7.57012 2.86201 7.69514C2.73699 7.82016 2.66675 7.98973 2.66675 8.16654C2.66675 8.34336 2.73699 8.51292 2.86201 8.63795C2.98703 8.76297 3.1566 8.83321 3.33341 8.83321H12.6667C12.8436 8.83321 13.0131 8.76297 13.1382 8.63795C13.2632 8.51292 13.3334 8.34336 13.3334 8.16654C13.3334 7.98973 13.2632 7.82016 13.1382 7.69514C13.0131 7.57012 12.8436 7.49988 12.6667 7.49988Z" fill="currentColor" />
							</svg>
						</button>
						
						<input type="number"<?php echo $form_attr; ?>
							   id="quantity"
							   name="quantity"
							   class="quantity-field w-10 text-center text-gray-800 text-sm bg-transparent border-0 focus:ring-0 font-medium"
							   value="<?php echo esc_attr( $min_qty ); ?>"
							   min="<?php echo esc_attr( $min_qty ); ?>"
							   <?php echo $max_qty > 0 ? 'max="' . esc_attr( $max_qty ) . '"' : ''; ?>
							   step="<?php echo esc_attr( $step ); ?>"
							   inputmode="numeric"
							   data-min="<?php echo esc_attr( $min_qty ); ?>"
							   data-max="<?php echo esc_attr( $max_qty > 0 ? $max_qty : '' ); ?>" />
						
						<button type="button"
								class="quantity-btn increment cursor-pointer text-gray-500 hover:text-gray-900 transition duration-200"
								id="qty-increment"
								aria-label="<?php esc_attr_e( 'Increase quantity', 'etheme' ); ?>">
							<svg xmlns="http://www.w3.org/2000/svg" width="16" height="17" viewBox="0 0 16 17" fill="none">
								<path d="M12.6667 7.4998H8.66675V3.4998C8.66675 3.32299 8.59651 3.15342 8.47149 3.02839C8.34646 2.90337 8.17689 2.83313 8.00008 2.83313C7.82327 2.83313 7.6537 2.90337 7.52868 3.02839C7.40365 3.15342 7.33341 3.32299 7.33341 3.4998V7.4998H3.33341C3.1566 7.4998 2.98703 7.57003 2.86201 7.69506C2.73699 7.82008 2.66675 7.98965 2.66675 8.16646C2.66675 8.34327 2.73699 8.51284 2.86201 8.63787C2.98703 8.76289 3.1566 8.83313 3.33341 8.83313H7.33341V12.8331C7.33341 13.0099 7.40365 13.1795 7.52868 13.3045C7.6537 13.4296 7.82327 13.4998 8.00008 13.4998C8.17689 13.4998 8.34646 13.4296 8.47149 13.3045C8.59651 13.1795 8.66675 13.0099 8.66675 12.8331V8.83313H12.6667C12.8436 8.83313 13.0131 8.76289 13.1382 8.63787C13.2632 8.51284 13.3334 8.34327 13.3334 8.16646C13.3334 7.98965 13.2632 7.82008 13.1382 7.69506C13.0131 7.57003 12.8436 7.4998 12.6667 7.4998Z" fill="currentColor" />
							</svg>
						</button>
					</div>
				</div>
				
				<div class="w-full md:flex-1">
					<button type="submit"<?php echo $form_attr; ?>
							name="add-to-cart"
							value="<?php echo esc_attr( $product->get_id() ); ?>"
							id="add-to-cart-button"
							class="block w-full px-3 py-4 rounded-sm text-center text-white text-sm font-semibold uppercase tracking-widest bg-black hover:bg-gray-900 transition duration-200 disabled:bg-gray-400 <?php echo $button_disabled ? 'opacity-50 cursor-not-allowed' : ''; ?>"
							<?php echo $button_disabled ? 'disabled' : ''; ?>
							data-add-text="<?php esc_attr_e( 'Add to Cart', 'etheme' ); ?>"
							data-adding-text="<?php esc_attr_e( 'Adding...', 'etheme' ); ?>"
							data-added-text="<?php esc_attr_e( 'Added!', 'etheme' ); ?>"
							data-out-of-stock-text="<?php esc_attr_e( 'Out of Stock', 'etheme' ); ?>">
						<span class="button-text"><?php echo esc_html( $button_text ); ?></span>
					</button>
				</div>
			</div>
			
			<?php else : ?>
				<button type="submit"<?php echo $form_attr; ?>
						name="add-to-cart"
						value="<?php echo esc_attr( $product->get_id() ); ?>"
						id="add-to-cart-button"
						class="block w-full px-3 py-4 rounded-sm text-center text-white text-sm font-semibold uppercase tracking-widest bg-black hover:bg-gray-900 transition duration-200 disabled:bg-gray-400 <?php echo $button_disabled ? 'opacity-50 cursor-not-allowed' : ''; ?>"
						<?php echo $button_disabled ? 'disabled' : ''; ?>
						data-add-text="<?php esc_attr_e( 'Add to Cart', 'etheme' ); ?>"
						data-adding-text="<?php esc_attr_e( 'Adding...', 'etheme' ); ?>"
						data-added-text="<?php esc_attr_e( 'Added!', 'etheme' ); ?>"
						data-out-of-stock-text="<?php esc_attr_e( 'Out of Stock', 'etheme' ); ?>">
					<span class="button-text"><?php echo esc_html( $button_text ); ?></span>
				</button>
			<?php endif; ?>
			
			<?php if ( ! $is_variable ) : ?>
			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />
			<?php endif; ?>
		
		<?php if ( ! $is_variable ) : ?>
		</form>
		<?php endif; ?>
		
	</div>
	<?php
}
