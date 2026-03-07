<?php
/**
 * Product Variations Component
 *
 * Renders attribute selectors for variable products.
 *
 * @param WC_Product_Variable $product Variable product object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_product_variations( $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return;
	}
	
	$attributes = $product->get_variation_attributes();
	$available_variations = $product->get_available_variations();
	
	if ( empty( $attributes ) ) {
		return;
	}
	
	// Get variation data for JavaScript
	$variation_data = etheme_get_variation_data( $product );
	?>
	
	<div class="product-variations mb-6" id="product-variations" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
		
		<form class="variations_form cart" id="variations-form" method="post" enctype="multipart/form-data" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>">
			
			<?php foreach ( $attributes as $attribute_name => $options ) : 
				$attribute_label = wc_attribute_label( $attribute_name );
				$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) 
					? wc_clean( wp_unslash( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) 
					: $product->get_variation_default_attribute( $attribute_name );
			?>
			
			<div class="variation-row mb-4">
				<label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>" class="block text-sm font-medium text-gray-700 mb-2">
					<?php echo esc_html( $attribute_label ); ?>
					<span class="text-red-500">*</span>
				</label>
				
				<select 
					id="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
					name="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
					class="variation-select w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
					data-attribute_name="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
					required>
					
					<option value=""><?php echo esc_html( sprintf( __( 'Choose %s', 'etheme' ), $attribute_label ) ); ?></option>
					
					<?php
					if ( ! empty( $options ) ) {
						if ( taxonomy_exists( $attribute_name ) ) {
							// Taxonomy-based attribute
							$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );
							foreach ( $terms as $term ) {
								if ( in_array( $term->slug, $options, true ) ) {
									printf(
										'<option value="%s" %s>%s</option>',
										esc_attr( $term->slug ),
										selected( sanitize_title( $selected ), $term->slug, false ),
										esc_html( $term->name )
									);
								}
							}
						} else {
							// Custom attribute
							foreach ( $options as $option ) {
								printf(
									'<option value="%s" %s>%s</option>',
									esc_attr( $option ),
									selected( $selected, sanitize_title( $option ), false ),
									esc_html( $option )
								);
							}
						}
					}
					?>
				</select>
			</div>
			
			<?php endforeach; ?>
			
			<!-- Validation Message -->
			<div id="variation-message" class="hidden text-red-600 text-sm mb-4">
				<?php esc_html_e( 'Please select all options before adding to cart.', 'etheme' ); ?>
			</div>
			
			<!-- Reset Link -->
			<a href="#" class="reset_variations text-sm text-blue-600 hover:text-blue-800 transition-colors duration-200 hidden" id="reset-variations">
				<?php esc_html_e( 'Clear selection', 'etheme' ); ?>
			</a>
			
			<input type="hidden" name="variation_id" id="variation_id" value="" />
			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />
			<input type="hidden" name="product_id" value="<?php echo esc_attr( $product->get_id() ); ?>" />
			
		</form>
		
	</div>
	
	<!-- Variation Data for JavaScript -->
	<script type="application/json" id="variation-data">
		<?php echo wp_json_encode( $variation_data ); ?>
	</script>
	<?php
}
