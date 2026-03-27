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

				$selected_display = '';
				if ( $selected ) {
					if ( taxonomy_exists( $attribute_name ) ) {
						$term = get_term_by( 'slug', $selected, $attribute_name );
						if ( $term && ! is_wp_error( $term ) ) {
							$selected_display = $term->name;
						}
					} else {
						$selected_display = $selected;
					}
				}
			?>
			
			<div class="variation-row mb-3">
				<div class="variation-input flex flex-nowrap items-center w-full min-w-0 py-3 px-4 rounded-sm border border-gray-200 gap-4">
					<label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>" class="text-sm font-medium text-gray-700 self-center leading-none">
						<?php echo esc_html( $attribute_label ); ?>
						<span class="text-red-500">*</span>
					</label>
					
					<select 
						id="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
						name="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
						class="variation-select sr-only"
						data-attribute_name="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
						required>
						
						<option value=""><?php echo esc_html( sprintf( __( 'Elegí %s', 'etheme' ), $attribute_label ) ); ?></option>
						
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

					<?php
					$dropdown_id = 'etheme-variation-dd-' . sanitize_title( $attribute_name );
					$selected_label = $selected_display ? $selected_display : '';
					?>
					<div class="ml-auto relative w-44" data-etheme-variation-dropdown>
						<button
							type="button"
							class="w-full relative text-sm font-medium text-gray-800 bg-transparent border-0 p-0 pr-6 focus:outline-none"
							aria-haspopup="listbox"
							aria-expanded="false"
							data-etheme-dd-button
							data-target-select="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
						>
							<span class="block truncate text-right" data-etheme-dd-label>
								<?php echo esc_html( $selected_label ? $selected_label : sprintf( __( 'Elegí %s', 'etheme' ), $attribute_label ) ); ?>
							</span>
							<svg class="absolute right-0 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500 transition-transform duration-150" viewBox="0 0 20 20" fill="none" aria-hidden="true" data-etheme-dd-caret>
								<path d="M6 8l4 4 4-4" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" />
							</svg>
						</button>

						<div
							id="<?php echo esc_attr( $dropdown_id ); ?>"
							class="hidden absolute z-50 left-1/2 -translate-x-1/2 mt-3 w-56 max-w-[90vw] bg-white border border-gray-200 rounded-md shadow-lg overflow-hidden"
							role="listbox"
							data-etheme-dd-menu
						>
							<button
								type="button"
								class="w-full px-4 py-3 text-sm text-gray-700 text-center hover:bg-gray-50 transition"
								role="option"
								data-etheme-dd-option
								data-value=""
							>
								<?php echo esc_html( sprintf( __( 'Elegí %s', 'etheme' ), $attribute_label ) ); ?>
							</button>

							<?php
							if ( ! empty( $options ) ) {
								if ( taxonomy_exists( $attribute_name ) ) {
									$terms = wc_get_product_terms( $product->get_id(), $attribute_name, array( 'fields' => 'all' ) );
									foreach ( $terms as $term ) {
										if ( ! in_array( $term->slug, $options, true ) ) {
											continue;
										}
										printf(
											'<button type="button" class="w-full px-4 py-3 text-sm text-gray-900 text-center hover:bg-gray-50 transition" role="option" data-etheme-dd-option data-value="%s">%s</button>',
											esc_attr( $term->slug ),
											esc_html( $term->name )
										);
									}
								} else {
									foreach ( $options as $option ) {
										printf(
											'<button type="button" class="w-full px-4 py-3 text-sm text-gray-900 text-center hover:bg-gray-50 transition" role="option" data-etheme-dd-option data-value="%s">%s</button>',
											esc_attr( $option ),
											esc_html( $option )
										);
									}
								}
							}
							?>
						</div>
					</div>
				</div>
			</div>
			
			<?php endforeach; ?>
			
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
