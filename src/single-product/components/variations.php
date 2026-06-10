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

if ( ! function_exists( 'etheme_resolve_term_color' ) ) {
	require_once get_template_directory() . '/src/front-page/includes/front-page-index.helpers.php';
}

function etheme_render_product_variations( $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return;
	}

	$attributes           = $product->get_variation_attributes();
	$available_variations = $product->get_available_variations();

	if ( empty( $attributes ) ) {
		return;
	}

	// Detect dual-color mode: two variation attributes whose name contains "color"/"colour".
	$color_attrs = array();
	foreach ( array_keys( $attributes ) as $attr_name ) {
		$lower = strtolower( $attr_name );
		if ( false !== strpos( $lower, 'color' ) || false !== strpos( $lower, 'colour' ) ) {
			$color_attrs[] = $attr_name;
		}
	}
	$dual_color = count( $color_attrs ) >= 2;
	list( $primary_color_attr, $secondary_color_attr ) = $dual_color
		? etheme_pick_primary_color_attrs( $color_attrs )
		: array( null, null );

	$color_pairs = $dual_color
		? etheme_build_color_pairs( $product, $primary_color_attr, $secondary_color_attr, $available_variations )
		: array();

	$variation_data = etheme_get_variation_data( $product );
	?>

	<div class="product-variations mb-6" id="product-variations" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">

		<form class="variations_form cart" id="variations-form" method="post" enctype="multipart/form-data" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>">

			<?php foreach ( $attributes as $attribute_name => $options ) : ?>
				<?php
				if ( $dual_color && $attribute_name === $secondary_color_attr ) {
					// Secondary color: render hidden select only — its UI is merged into the primary row.
					etheme_render_variation_hidden_select( $product, $attribute_name, $options );
				} elseif ( $dual_color && $attribute_name === $primary_color_attr ) {
					etheme_render_variation_dual_color_row( $product, $primary_color_attr, $secondary_color_attr, $options, $color_pairs );
				} else {
					etheme_render_variation_row( $product, $attribute_name, $options );
				}
				?>
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

/**
 * Pick primary / secondary color attributes from a list of color-like attribute names.
 * Prefers shorter names (e.g. "pa_color" over "pa_color2", "pa_color-secundario").
 *
 * @param string[] $names
 * @return array{0:string,1:string}
 */
function etheme_pick_primary_color_attrs( $names ) {
	$sorted = $names;
	usort( $sorted, function ( $a, $b ) {
		return strlen( $a ) - strlen( $b );
	} );
	return array( $sorted[0], $sorted[1] );
}

/**
 * Compute the currently selected slug for a variation attribute.
 */
function etheme_variation_selected_slug( $product, $attribute_name ) {
	$req_key = 'attribute_' . sanitize_title( $attribute_name );
	if ( isset( $_REQUEST[ $req_key ] ) ) {
		return wc_clean( wp_unslash( $_REQUEST[ $req_key ] ) );
	}
	return $product->get_variation_default_attribute( $attribute_name );
}

/**
 * Get the human-facing label for a term slug within a variation attribute.
 */
function etheme_variation_term_label( $attribute_name, $slug ) {
	if ( '' === $slug || null === $slug ) {
		return '';
	}
	if ( taxonomy_exists( $attribute_name ) ) {
		$term = get_term_by( 'slug', $slug, $attribute_name );
		if ( $term && ! is_wp_error( $term ) ) {
			return $term->name;
		}
	}
	return $slug;
}

/**
 * Render the hidden `<select>` used by the JS variation matcher.
 *
 * @param WC_Product $product
 * @param string     $attribute_name
 * @param array      $options
 */
function etheme_render_variation_hidden_select( $product, $attribute_name, $options ) {
	$selected = etheme_variation_selected_slug( $product, $attribute_name );
	?>
	<select
		id="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
		name="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
		class="variation-select sr-only"
		data-attribute_name="attribute_<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>"
		required>
		<option value=""></option>
		<?php
		if ( ! empty( $options ) ) {
			if ( taxonomy_exists( $attribute_name ) ) {
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
}

/**
 * Render a single-attribute variation row (label + hidden select + custom dropdown).
 *
 * @param WC_Product $product
 * @param string     $attribute_name
 * @param array      $options
 */
function etheme_render_variation_row( $product, $attribute_name, $options ) {
	$attribute_label = wc_attribute_label( $attribute_name );
	$selected        = etheme_variation_selected_slug( $product, $attribute_name );
	$selected_label  = etheme_variation_term_label( $attribute_name, $selected );
	$dropdown_id     = 'etheme-variation-dd-' . sanitize_title( $attribute_name );
	?>
	<div class="variation-row mb-3">
		<div class="variation-input flex flex-nowrap items-center w-full min-w-0 py-3 px-4 rounded-sm border border-gray-200 gap-4">
			<label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>" class="text-sm font-medium text-gray-700 self-center leading-none">
				<?php echo esc_html( $attribute_label ); ?>
				<span class="text-red-500">*</span>
			</label>

			<?php etheme_render_variation_hidden_select( $product, $attribute_name, $options ); ?>

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
	<?php
}

/**
 * Build de-duplicated (color1, color2) pairs from available variations.
 *
 * @param WC_Product $product
 * @param string     $primary_attr   Primary color attribute name (e.g. "pa_color").
 * @param string     $secondary_attr Secondary color attribute name (e.g. "pa_color2").
 * @param array      $variations     Result of $product->get_available_variations().
 * @return array[] List of pairs: { slug1, label1, css1, slug2, label2, css2 } (slug2/label2/css2 may be null).
 */
function etheme_build_color_pairs( $product, $primary_attr, $secondary_attr, $variations ) {
	$primary_key   = 'attribute_' . sanitize_title( $primary_attr );
	$secondary_key = 'attribute_' . sanitize_title( $secondary_attr );

	$pairs = array();
	$seen  = array();
	foreach ( $variations as $variation ) {
		$slug1 = isset( $variation['attributes'][ $primary_key ] ) ? $variation['attributes'][ $primary_key ] : '';
		$slug2 = isset( $variation['attributes'][ $secondary_key ] ) ? $variation['attributes'][ $secondary_key ] : '';
		if ( '' === $slug1 && '' === $slug2 ) {
			continue;
		}
		$key = $slug1 . '|' . $slug2;
		if ( isset( $seen[ $key ] ) ) {
			continue;
		}
		$seen[ $key ] = true;

		$label1 = etheme_variation_term_label( $primary_attr, $slug1 );
		$label2 = etheme_variation_term_label( $secondary_attr, $slug2 );
		$css1   = $slug1 ? etheme_resolve_term_color( (object) array( 'slug' => $slug1, 'name' => $label1 ?: $slug1 ) ) : null;
		$css2   = $slug2 ? etheme_resolve_term_color( (object) array( 'slug' => $slug2, 'name' => $label2 ?: $slug2 ) ) : null;

		$pairs[] = array(
			'slug1'  => $slug1,
			'label1' => $label1,
			'css1'   => $css1,
			'slug2'  => $slug2,
			'label2' => $label2,
			'css2'   => $css2,
		);
	}
	return $pairs;
}

/**
 * Build the visible label for a color pair (e.g. "Blanco / Azul" or "Blanco").
 */
function etheme_color_pair_label( $pair ) {
	$parts = array_filter( array( $pair['label1'], $pair['label2'] ) );
	return implode( ' / ', $parts );
}

/**
 * Render the small split-circle swatch for a color pair.
 */
function etheme_color_pair_swatch( $pair ) {
	$css1 = $pair['css1'];
	$css2 = $pair['css2'];
	if ( $css1 && $css2 ) {
		printf(
			'<span class="etheme-variation-swatch etheme-variation-swatch--split" style="--dot-color1:%1$s;--dot-color2:%2$s;--dot-color:%1$s;" aria-hidden="true"></span>',
			esc_attr( $css1 ),
			esc_attr( $css2 )
		);
		return;
	}
	if ( $css1 ) {
		printf(
			'<span class="etheme-variation-swatch" style="--dot-color:%1$s;background-color:%1$s;" aria-hidden="true"></span>',
			esc_attr( $css1 )
		);
	}
}

/**
 * Render the unified dual-color row: one visible dropdown that drives both hidden selects.
 *
 * @param WC_Product $product
 * @param string     $primary_attr
 * @param string     $secondary_attr
 * @param array      $primary_options
 * @param array      $pairs
 */
function etheme_render_variation_dual_color_row( $product, $primary_attr, $secondary_attr, $primary_options, $pairs ) {
	$attribute_label = wc_attribute_label( $primary_attr );
	$selected1       = etheme_variation_selected_slug( $product, $primary_attr );
	$selected2       = etheme_variation_selected_slug( $product, $secondary_attr );
	$primary_id      = sanitize_title( $primary_attr );
	$secondary_id    = sanitize_title( $secondary_attr );
	$dropdown_id     = 'etheme-variation-dd-' . $primary_id;

	$selected_label = '';
	foreach ( $pairs as $p ) {
		if ( $p['slug1'] === $selected1 && $p['slug2'] === $selected2 ) {
			$selected_label = etheme_color_pair_label( $p );
			break;
		}
	}
	?>
	<div class="variation-row mb-3">
		<div class="variation-input flex flex-nowrap items-center w-full min-w-0 py-3 px-4 rounded-sm border border-gray-200 gap-4">
			<label for="<?php echo esc_attr( $primary_id ); ?>" class="text-sm font-medium text-gray-700 self-center leading-none">
				<?php echo esc_html( $attribute_label ); ?>
				<span class="text-red-500">*</span>
			</label>

			<?php etheme_render_variation_hidden_select( $product, $primary_attr, $primary_options ); ?>

			<div
				class="ml-auto relative w-44"
				data-etheme-variation-dropdown
				data-dual-color="1"
			>
				<button
					type="button"
					class="w-full relative text-sm font-medium text-gray-800 bg-transparent border-0 p-0 pr-6 focus:outline-none"
					aria-haspopup="listbox"
					aria-expanded="false"
					data-etheme-dd-button
					data-target-select="<?php echo esc_attr( $primary_id ); ?>"
					data-target-select2="<?php echo esc_attr( $secondary_id ); ?>"
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
					class="hidden absolute z-50 left-1/2 -translate-x-1/2 mt-3 w-64 max-w-[90vw] bg-white border border-gray-200 rounded-md shadow-lg overflow-hidden"
					role="listbox"
					data-etheme-dd-menu
				>
					<button
						type="button"
						class="w-full px-4 py-3 text-sm text-gray-700 text-center hover:bg-gray-50 transition"
						role="option"
						data-etheme-dd-option
						data-value=""
						data-value2=""
					>
						<?php echo esc_html( sprintf( __( 'Elegí %s', 'etheme' ), $attribute_label ) ); ?>
					</button>

					<?php foreach ( $pairs as $pair ) : ?>
						<button
							type="button"
							class="etheme-variation-pair-option w-full px-4 py-3 text-sm text-gray-900 hover:bg-gray-50 transition flex items-center gap-2 justify-center"
							role="option"
							data-etheme-dd-option
							data-value="<?php echo esc_attr( $pair['slug1'] ); ?>"
							data-value2="<?php echo esc_attr( $pair['slug2'] ); ?>"
						>
							<?php etheme_color_pair_swatch( $pair ); ?>
							<span><?php echo esc_html( etheme_color_pair_label( $pair ) ); ?></span>
						</button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
