<?php
/**
 * Product Filter Menu Component
 *
 * Renders a comprehensive filter menu with categories and dynamic filters (color, attribute, price).
 * When $filter_config is provided, filters are rendered from config; otherwise legacy colors/sizes/price.
 *
 * @param array|null $filter_params   Optional filter parameters. If null, fetches from request.
 * @param bool       $is_open         Whether the filter menu should be visible by default.
 * @param array|null $sorting_data   Optional sorting render data.
 * @param bool       $exclude_parent_categories Whether to hide parent categories.
 * @param bool       $use_parent_children_only Whether to show only children of active parent.
 * @param array|null $categories_override Optional categories list to render.
 * @param array      $exclude_category_ids Optional term IDs to exclude.
 * @param array|null $filter_config   Optional. List of filter items (type: category|color|attribute|price). When set, renders filters dynamically.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_filter_menu( $filter_params = null, $is_open = false, $sorting_data = null, $exclude_parent_categories = false, $use_parent_children_only = false, $categories_override = null, $exclude_category_ids = array(), $filter_config = null ) {
	if ( ! $filter_params ) {
		$attribute_taxonomies = etheme_get_archive_attribute_taxonomies();
		$filter_params = etheme_get_filter_params( $attribute_taxonomies );
	}

	$categories = $categories_override;
	if ( null === $categories_override ) {
		$categories = etheme_get_product_categories();
		if ( $use_parent_children_only ) {
			$active_category_id = etheme_get_active_category_id( $filter_params );
			$categories = $active_category_id ? etheme_get_child_categories_by_parent( $active_category_id ) : array();
		} elseif ( $exclude_parent_categories ) {
			$categories = etheme_get_child_product_categories();
		}
	}
	if ( ! empty( $exclude_category_ids ) ) {
		if ( empty( $categories ) || is_wp_error( $categories ) ) {
			$categories = etheme_get_child_product_categories();
		}
		if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
			$exclude_ids = array_map( 'absint', $exclude_category_ids );
			$categories = array_filter( $categories, function ( $cat ) use ( $exclude_ids ) {
				return ! in_array( absint( $cat->term_id ), $exclude_ids, true );
			} );
			$categories = array_values( $categories );
		}
	}
	$has_filters = etheme_has_active_filters( $filter_params );

	$price_range  = etheme_get_price_range();
	$min_price    = $price_range['min'];
	$max_price    = $price_range['max'];
	$current_min  = $filter_params['min_price'] > 0 ? $filter_params['min_price'] : $min_price;
	$current_max  = $filter_params['max_price'] > 0 ? $filter_params['max_price'] : $max_price;
	if ( $current_max < $current_min ) {
		$current_max = $current_min;
	}

	$use_dynamic = ! empty( $filter_config ) && is_array( $filter_config );
	if ( ! $use_dynamic ) {
		$colors = etheme_get_product_colors();
		$sizes  = etheme_get_product_sizes();
	}
	?>
	
	<div id="filters-content" class="<?php echo $is_open ? '' : 'hidden'; ?> md:block w-full md:w-1/3 lg:w-1/4 fixed inset-0 z-50 bg-black/50 overflow-y-auto md:static md:inset-auto md:z-auto md:bg-transparent md:overflow-visible">
		<div class="min-h-full md:min-h-0 flex items-end md:block">
			<div class="w-full bg-white md:bg-transparent rounded-t-2xl md:rounded-none p-4 md:p-0 md:px-4 md:pt-[4vw]">
				<div class="flex items-center justify-between mb-4 md:hidden">
					<p class="text-rhino-700 font-semibold"><?php esc_html_e( 'Filters', 'etheme' ); ?></p>
					<button type="button" id="close-filters" class="text-rhino-700 text-sm font-semibold">
						<?php esc_html_e( 'Close', 'etheme' ); ?>
					</button>
				</div>

				<form method="GET" action="<?php echo esc_url( etheme_get_current_url() ); ?>" class="filters-form auto-apply-filters">
			<?php
			// Preserve search and sort when filtering
			etheme_render_preserved_params( $filter_params, array(
				'search'  => true,
				'sort'    => ! $sorting_data,
				'filters' => false,
			) );
			?>
			
			<!-- Sorting (Mobile Only) -->
			<?php if ( $sorting_data ) : ?>
			<div class="bg-white p-6 mb-6 rounded-xl md:hidden">
				<p class="text-rhino-700 font-semibold mb-4"><?php esc_html_e( 'Sort By', 'etheme' ); ?></p>
				<?php etheme_render_sorting_select( $sorting_data['current_sort'], true, false ); ?>
			</div>
			<?php endif; ?>
			
			<!-- Categories Filter (always shown) -->
			<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
			<div class="bg-white p-6 mb-6 rounded-xl">
				<p class="text-rhino-700 font-semibold mb-4"><?php esc_html_e( 'Category', 'etheme' ); ?></p>
				<ul class="text-coolGray-700 flex flex-col gap-2">
					<?php foreach ( $categories as $category ) :
						$is_selected = in_array( (int) $category->term_id, $filter_params['categories'], true );
					?>
					<li class="hover:text-coolGray-800 transition duration-200">
						<label class="flex items-center gap-2 cursor-pointer">
							<input
								type="checkbox"
								name="filter_categories[]"
								value="<?php echo esc_attr( $category->term_id ); ?>"
								<?php checked( $is_selected, true ); ?>
								class="category-filter-checkbox w-4 h-4 text-purple-500 border-coolGray-200 rounded focus:ring-purple-500"
							>
							<span class="text-sm no-underline"><?php echo esc_html( $category->name ); ?></span>
						</label>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
			<?php endif; ?>

			<?php if ( $use_dynamic ) : ?>
				<?php foreach ( $filter_config as $item ) : ?>
					<?php
					if ( ! is_array( $item ) || empty( $item['type'] ) || $item['type'] === 'category' ) {
						continue;
					}
					if ( $item['type'] === 'color' ) :
						$taxonomy = isset( $item['taxonomy'] ) ? $item['taxonomy'] : 'pa_color';
						$overrides = isset( $item['termOverrides'] ) && is_array( $item['termOverrides'] ) ? $item['termOverrides'] : array();
						$colors_dyn = etheme_get_product_colors_for_taxonomy( $taxonomy, $overrides );
						$param_slugs = isset( $filter_params['attributes'][ $taxonomy ] ) ? $filter_params['attributes'][ $taxonomy ] : array();
					?>
					<div class="bg-white p-6 mb-6 rounded-xl">
						<p class="text-rhino-700 font-semibold mb-4"><?php esc_html_e( 'Color', 'etheme' ); ?></p>
						<div class="flex flex-wrap gap-2 mb-4">
							<?php foreach ( $colors_dyn as $color ) :
								$is_selected = in_array( $color['slug'], $param_slugs, true );
							?>
							<label class="inline-flex items-center justify-center w-8 h-8 rounded-full cursor-pointer shrink-0 <?php echo $is_selected ? 'ring-2 ring-purple-500 ring-offset-2 ring-offset-white' : ''; ?>">
								<input type="checkbox" name="filter_<?php echo esc_attr( $taxonomy ); ?>[]" value="<?php echo esc_attr( $color['slug'] ); ?>" <?php checked( $is_selected, true ); ?> class="color-filter-checkbox hidden">
								<span class="block w-full h-full rounded-full" style="background-color: <?php echo esc_attr( $color['hex'] ); ?>;" title="<?php echo esc_attr( $color['name'] ); ?>"></span>
							</label>
							<?php endforeach; ?>
						</div>
					</div>
					<?php elseif ( $item['type'] === 'attribute' ) :
						$taxonomy = isset( $item['taxonomy'] ) ? $item['taxonomy'] : 'pa_size';
						$label = isset( $item['label'] ) ? $item['label'] : __( 'Attribute', 'etheme' );
						$terms_attr = etheme_get_product_attribute_terms( $taxonomy );
						$param_slugs = isset( $filter_params['attributes'][ $taxonomy ] ) ? $filter_params['attributes'][ $taxonomy ] : array();
					?>
					<?php if ( ! empty( $terms_attr ) ) : ?>
					<div class="bg-white p-6 mb-6 rounded-xl">
						<p class="text-rhino-700 font-semibold mb-4"><?php echo esc_html( $label ); ?></p>
						<div class="max-h-64 overflow-y-auto pr-2 -mr-2">
							<div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-3 lg:grid-cols-4 gap-2">
								<?php foreach ( $terms_attr as $term_item ) :
									$is_selected = in_array( $term_item['slug'], $param_slugs, true );
								?>
								<label class="flex items-center justify-center border-2 py-2.5 px-3 rounded-full text-center text-sm font-medium cursor-pointer transition-all duration-200 <?php echo $is_selected ? 'border-black bg-black text-white' : 'border-coolGray-200 bg-white text-coolGray-700 hover:border-coolGray-300 hover:bg-coolGray-50'; ?>">
									<input type="checkbox" name="filter_<?php echo esc_attr( $taxonomy ); ?>[]" value="<?php echo esc_attr( $term_item['slug'] ); ?>" <?php checked( $is_selected, true ); ?> class="size-filter-checkbox hidden">
									<span class="whitespace-nowrap"><?php echo esc_html( $term_item['name'] ); ?></span>
								</label>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
					<?php endif; ?>
					<?php elseif ( $item['type'] === 'price' ) :
						$range_color = isset( $item['rangeColor'] ) ? $item['rangeColor'] : '#7573F9';
						$range_style = $min_price !== $max_price ? 'left: ' . esc_attr( ( ( $current_min - $min_price ) / ( $max_price - $min_price ) ) * 100 ) . '%; width: ' . esc_attr( ( ( $current_max - $current_min ) / ( $max_price - $min_price ) ) * 100 ) . '%;' : 'left: 0; width: 100%;';
					?>
					<div class="bg-white p-6 rounded-xl filters-price-section">
						<p class="text-rhino-700 font-semibold mb-4"><?php esc_html_e( 'Price', 'etheme' ); ?></p>
						<div class="price-filter-content price-range-wrapper" style="--price-range-color: <?php echo esc_attr( $range_color ); ?>">
							<div class="price-range-slider-container relative">
								<div class="price-range-track relative h-2 bg-coolGray-200 rounded-full">
									<div class="price-range-fill absolute h-2 rounded-full" style="<?php echo $range_style; ?>"></div>
								</div>
								<input type="range" name="min_price" min="<?php echo esc_attr( $min_price ); ?>" max="<?php echo esc_attr( $max_price ); ?>" value="<?php echo esc_attr( $current_min ); ?>" class="price-range-input price-range-min absolute w-full h-2 bg-transparent appearance-none cursor-pointer" data-min="<?php echo esc_attr( $min_price ); ?>" data-max="<?php echo esc_attr( $max_price ); ?>">
								<input type="range" name="max_price" min="<?php echo esc_attr( $min_price ); ?>" max="<?php echo esc_attr( $max_price ); ?>" value="<?php echo esc_attr( $current_max ); ?>" class="price-range-input price-range-max absolute w-full h-2 bg-transparent appearance-none cursor-pointer" data-min="<?php echo esc_attr( $min_price ); ?>" data-max="<?php echo esc_attr( $max_price ); ?>">
							</div>
							<div class="flex items-center justify-between flex-wrap gap-2 mt-4">
								<p class="text-coolGray-700 text-sm font-medium price-min-display">$<?php echo number_format( $current_min, 0 ); ?></p>
								<p class="text-coolGray-700 text-sm font-medium price-max-display">$<?php echo number_format( $current_max, 0 ); ?></p>
							</div>
						</div>
					</div>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php else : ?>
				<!-- Legacy: Color Filter -->
				<?php if ( ! empty( $colors ) ) : ?>
				<div class="bg-white p-6 mb-6 rounded-xl">
					<p class="text-rhino-700 font-semibold mb-4"><?php esc_html_e( 'Color', 'etheme' ); ?></p>
					<div class="flex flex-wrap gap-2 mb-4">
						<?php foreach ( $colors as $color ) :
							$is_selected = isset( $filter_params['colors'] ) && in_array( $color['slug'], $filter_params['colors'], true );
						?>
						<label class="inline-flex items-center justify-center w-8 h-8 rounded-full cursor-pointer shrink-0 <?php echo $is_selected ? 'ring-2 ring-purple-500 ring-offset-2 ring-offset-white' : ''; ?>">
							<input type="checkbox" name="filter_colors[]" value="<?php echo esc_attr( $color['slug'] ); ?>" <?php checked( $is_selected, true ); ?> class="color-filter-checkbox hidden">
							<span class="block w-full h-full rounded-full" style="background-color: <?php echo esc_attr( $color['hex'] ); ?>;" title="<?php echo esc_attr( $color['name'] ); ?>"></span>
						</label>
						<?php endforeach; ?>
					</div>
				</div>
				<?php endif; ?>
				<!-- Legacy: Size Filter -->
				<?php if ( ! empty( $sizes ) ) : ?>
				<div class="bg-white p-6 mb-6 rounded-xl">
					<p class="text-rhino-700 font-semibold mb-4"><?php esc_html_e( 'Size', 'etheme' ); ?></p>
					<div class="max-h-64 overflow-y-auto pr-2 -mr-2">
						<div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-3 lg:grid-cols-4 gap-2">
							<?php foreach ( $sizes as $size ) :
								$is_selected = isset( $filter_params['sizes'] ) && in_array( $size['slug'], $filter_params['sizes'], true );
							?>
							<label class="flex items-center justify-center border-2 py-2.5 px-3 rounded-full text-center text-sm font-medium cursor-pointer transition-all duration-200 <?php echo $is_selected ? 'border-black bg-black text-white' : 'border-coolGray-200 bg-white text-coolGray-700 hover:border-coolGray-300 hover:bg-coolGray-50'; ?>">
								<input type="checkbox" name="filter_sizes[]" value="<?php echo esc_attr( $size['slug'] ); ?>" <?php checked( $is_selected, true ); ?> class="size-filter-checkbox hidden">
								<span class="whitespace-nowrap"><?php echo esc_html( $size['name'] ); ?></span>
							</label>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
				<?php endif; ?>
				<!-- Legacy: Price Range Filter -->
				<div class="bg-white p-6 rounded-xl price-range-wrapper filters-price-section" style="--price-range-color: #7573F9">
					<p class="text-rhino-700 font-semibold mb-4"><?php esc_html_e( 'Price', 'etheme' ); ?></p>
					<div class="price-filter-content">
						<div class="price-range-slider-container relative">
							<div class="price-range-track relative h-2 bg-coolGray-200 rounded-full">
								<div class="price-range-fill absolute h-2 rounded-full" style="left: <?php echo esc_attr( ( ( $current_min - $min_price ) / max( 1, $max_price - $min_price ) ) * 100 ); ?>%; width: <?php echo esc_attr( ( ( $current_max - $current_min ) / max( 1, $max_price - $min_price ) ) * 100 ); ?>%;"></div>
							</div>
							<input type="range" name="min_price" min="<?php echo esc_attr( $min_price ); ?>" max="<?php echo esc_attr( $max_price ); ?>" value="<?php echo esc_attr( $current_min ); ?>" class="price-range-input price-range-min absolute w-full h-2 bg-transparent appearance-none cursor-pointer" data-min="<?php echo esc_attr( $min_price ); ?>" data-max="<?php echo esc_attr( $max_price ); ?>">
							<input type="range" name="max_price" min="<?php echo esc_attr( $min_price ); ?>" max="<?php echo esc_attr( $max_price ); ?>" value="<?php echo esc_attr( $current_max ); ?>" class="price-range-input price-range-max absolute w-full h-2 bg-transparent appearance-none cursor-pointer" data-min="<?php echo esc_attr( $min_price ); ?>" data-max="<?php echo esc_attr( $max_price ); ?>">
						</div>
						<div class="flex items-center justify-between flex-wrap gap-2 mt-4">
							<p class="text-coolGray-700 text-sm font-medium price-min-display">$<?php echo number_format( $current_min, 0 ); ?></p>
							<p class="text-coolGray-700 text-sm font-medium price-max-display">$<?php echo number_format( $current_max, 0 ); ?></p>
						</div>
					</div>
				</div>
			<?php endif; ?>
			
			<!-- Clear Filters Button -->
			<?php if ( $has_filters ) : ?>
			<div class="mt-6">
				<a 
					href="<?php echo esc_url( etheme_get_clear_filters_url( true, true, $filter_params ) ); ?>" 
					class="inline-flex items-center gap-2 px-6 py-3 bg-red-100 text-red-700 rounded-lg font-semibold hover:bg-red-200 transition-colors"
				>
					 <?php esc_html_e( 'Clear Filters', 'etheme' ); ?>
				</a>
			</div>
			<?php endif; ?>

			<!-- Spacer on mobile so price range thumbs do not overlap the sticky Apply button -->
			<div class="md:hidden h-24 flex-shrink-0" aria-hidden="true"></div>

			<div class="filters-apply-wrap md:hidden sticky bottom-0 bg-white pt-4 pb-2 flex-shrink-0 z-10">
				<button type="submit" class="w-full bg-gray-800 text-white p-4 rounded-lg font-semibold hover:bg-gray-700 transition-colors">
					<?php esc_html_e( 'Apply Filters', 'etheme' ); ?>
				</button>
			</div>
		</form>
			</div>
		</div>
	</div>
	<?php
}
