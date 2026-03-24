<?php
/**
 * Product Filter Menu Component
 *
 * Renders a comprehensive filter menu with categories and dynamic filters
 * (color, attribute, price). Contrive-style: widget sections with border
 * dividers, no rounded cards. Mobile: full-height offcanvas drawer.
 *
 * @param array|null $filter_params          Optional filter parameters.
 * @param bool       $is_open                Whether visible by default.
 * @param array|null $sorting_data           Optional sorting data.
 * @param bool       $exclude_parent_categories Whether to hide parent cats.
 * @param bool       $use_parent_children_only  Show only children of active parent.
 * @param array|null $categories_override    Optional categories list.
 * @param array      $exclude_category_ids   Term IDs to exclude.
 * @param array|null $filter_config          Dynamic filter config list.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_filter_menu( $filter_params = null, $is_open = false, $sorting_data = null, $exclude_parent_categories = false, $use_parent_children_only = false, $categories_override = null, $exclude_category_ids = array(), $filter_config = null ) {
	if ( ! $filter_params ) {
		$attribute_taxonomies = etheme_get_archive_attribute_taxonomies();
		$filter_params        = etheme_get_filter_params( $attribute_taxonomies );
	}

	$categories = _etheme_resolve_filter_categories( $categories_override, $use_parent_children_only, $exclude_parent_categories, $exclude_category_ids, $filter_params );
	$has_filters = etheme_has_active_filters( $filter_params );

	$price_range = etheme_get_price_range();
	$min_price   = $price_range['min'];
	$max_price   = $price_range['max'];
	$current_min = $filter_params['min_price'] > 0 ? $filter_params['min_price'] : $min_price;
	$current_max = $filter_params['max_price'] > 0 ? $filter_params['max_price'] : $max_price;
	if ( $current_max < $current_min ) {
		$current_max = $current_min;
	}

	$use_dynamic = ! empty( $filter_config ) && is_array( $filter_config );
	if ( ! $use_dynamic ) {
		$colors = etheme_get_product_colors();
		$sizes  = etheme_get_product_sizes();
	}

	$open_class = $is_open ? ' is-open' : '';
	?>

	<!-- Drawer backdrop (mobile only) -->
	<div id="filters-backdrop" aria-hidden="true"></div>

	<!-- Filter sidebar / offcanvas drawer -->
	<aside
		id="filters-content"
		class="archive-filter-drawer w-full md:w-1/3 lg:w-1/4<?php echo $open_class; ?>"
		aria-label="<?php esc_attr_e( 'Product filters', 'etheme' ); ?>"
	>
		<!-- Mobile header -->
		<div class="filter-drawer-header">
			<p class="filter-drawer-title"><?php esc_html_e( 'Filters', 'etheme' ); ?></p>
			<button type="button" id="close-filters" class="filter-close-btn" aria-label="<?php esc_attr_e( 'Close filters', 'etheme' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none" aria-hidden="true">
					<path d="M1 1l12 12M13 1L1 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
				</svg>
			</button>
		</div>

		<!-- Scrollable form area -->
		<div class="filter-form-scroll">
			<form
				id="filters-form-inner"
				method="GET"
				action="<?php echo esc_url( etheme_get_current_url() ); ?>"
				class="filters-form auto-apply-filters"
			>
				<?php
				etheme_render_preserved_params( $filter_params, array(
					'search'  => true,
					'sort'    => ! $sorting_data,
					'filters' => false,
				) );
				?>

				<!-- Sorting (mobile only) -->
				<?php if ( $sorting_data ) : ?>
				<div class="filter-widget md:hidden">
					<h4 class="filter-widget-title"><?php esc_html_e( 'Sort By', 'etheme' ); ?></h4>
					<?php etheme_render_sorting_select( $sorting_data['current_sort'], true, false ); ?>
				</div>
				<?php endif; ?>

				<!-- Category filter (always rendered if categories exist) -->
				<?php etheme_render_filter_section_categories( $categories, $filter_params ); ?>

				<!-- Dynamic or legacy attribute filters -->
				<?php if ( $use_dynamic ) : ?>
					<?php etheme_render_filter_sections_dynamic( $filter_config, $filter_params, $min_price, $max_price, $current_min, $current_max ); ?>
				<?php else : ?>
					<?php etheme_render_filter_sections_legacy( $colors, $sizes, $filter_params, $min_price, $max_price, $current_min, $current_max ); ?>
				<?php endif; ?>

				<!-- Clear filters link -->
				<?php if ( $has_filters ) : ?>
				<div class="mt-6 pb-2">
					<a
						href="<?php echo esc_url( etheme_get_clear_filters_url( true, true, $filter_params ) ); ?>"
						class="filter-clear-link"
					>
						<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none" aria-hidden="true">
							<path d="M1 1l11 11M12 1L1 12" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
						</svg>
						<?php esc_html_e( 'Clear all filters', 'etheme' ); ?>
					</a>
				</div>
				<?php endif; ?>

			</form><!-- /.filters-form -->
		</div><!-- /.filter-form-scroll -->

		<!-- Sticky apply button (mobile only) -->
		<div class="filter-apply-sticky">
			<button type="submit" form="filters-form-inner" class="filter-apply-btn">
				<?php esc_html_e( 'Apply Filters', 'etheme' ); ?>
			</button>
		</div>

	</aside>
	<?php
}

/**
 * Resolve which categories to show in the filter.
 */
function _etheme_resolve_filter_categories( $categories_override, $use_parent_children_only, $exclude_parent_categories, $exclude_category_ids, $filter_params ) {
	$categories = $categories_override;
	if ( null === $categories_override ) {
		$categories = etheme_get_product_categories();
		if ( $use_parent_children_only ) {
			$active_id  = etheme_get_active_category_id( $filter_params );
			$categories = $active_id ? etheme_get_child_categories_by_parent( $active_id ) : array();
		} elseif ( $exclude_parent_categories ) {
			$categories = etheme_get_child_product_categories();
		}
	}
	if ( empty( $exclude_category_ids ) ) {
		return $categories;
	}
	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		$categories = etheme_get_child_product_categories();
	}
	if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
		$exclude_ids = array_map( 'absint', $exclude_category_ids );
		$categories  = array_values( array_filter( $categories, function ( $cat ) use ( $exclude_ids ) {
			return ! in_array( absint( $cat->term_id ), $exclude_ids, true );
		} ) );
	}
	return $categories;
}

/**
 * Render category filter section.
 */
function etheme_render_filter_section_categories( $categories, $filter_params ) {
	if ( empty( $categories ) || is_wp_error( $categories ) ) {
		return;
	}
	?>
	<div class="filter-widget" data-aos="fade-up">
		<h4 class="filter-widget-title"><?php esc_html_e( 'Category', 'etheme' ); ?></h4>
		<ul class="filter-cat-list">
			<?php foreach ( $categories as $category ) :
				$is_selected = in_array( (int) $category->term_id, $filter_params['categories'], true );
			?>
			<li>
				<label>
					<input
						type="checkbox"
						name="filter_categories[]"
						value="<?php echo esc_attr( $category->term_id ); ?>"
						<?php checked( $is_selected, true ); ?>
						class="cat-filter-cb"
						aria-label="<?php echo esc_attr( $category->name ); ?>"
					>
					<?php echo esc_html( $category->name ); ?>
				</label>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

/**
 * Render dynamic filter sections (color, attribute, price) from config.
 */
function etheme_render_filter_sections_dynamic( $filter_config, $filter_params, $min_price, $max_price, $current_min, $current_max ) {
	foreach ( $filter_config as $item ) {
		if ( ! is_array( $item ) || empty( $item['type'] ) || 'category' === $item['type'] ) {
			continue;
		}
		if ( 'color' === $item['type'] ) {
			etheme_render_filter_section_color( $item, $filter_params );
		} elseif ( 'attribute' === $item['type'] ) {
			etheme_render_filter_section_attribute( $item, $filter_params );
		} elseif ( 'price' === $item['type'] ) {
			etheme_render_filter_section_price( $item, $filter_params, $min_price, $max_price, $current_min, $current_max );
		}
	}
}

/**
 * Render legacy filter sections (colors, sizes, price).
 */
function etheme_render_filter_sections_legacy( $colors, $sizes, $filter_params, $min_price, $max_price, $current_min, $current_max ) {
	if ( ! empty( $colors ) ) {
		etheme_render_filter_section_color_legacy( $colors, $filter_params );
	}
	if ( ! empty( $sizes ) ) {
		etheme_render_filter_section_size_legacy( $sizes, $filter_params );
	}
	etheme_render_filter_section_price( array( 'rangeColor' => '#fb704f' ), $filter_params, $min_price, $max_price, $current_min, $current_max );
}

/**
 * Render color swatch filter section.
 */
function etheme_render_filter_section_color( $item, $filter_params ) {
	$taxonomy  = isset( $item['taxonomy'] ) ? $item['taxonomy'] : 'pa_color';
	$overrides = isset( $item['termOverrides'] ) && is_array( $item['termOverrides'] ) ? $item['termOverrides'] : array();
	$colors    = etheme_get_product_colors_for_taxonomy( $taxonomy, $overrides );
	$selected  = isset( $filter_params['attributes'][ $taxonomy ] ) ? $filter_params['attributes'][ $taxonomy ] : array();
	if ( empty( $colors ) ) {
		return;
	}
	?>
	<div class="filter-widget" data-aos="fade-up">
		<h4 class="filter-widget-title"><?php esc_html_e( 'Color', 'etheme' ); ?></h4>
		<div class="color-swatch-list">
			<?php foreach ( $colors as $color ) :
				$is_sel = in_array( $color['slug'], $selected, true );
			?>
			<label class="color-swatch-label<?php echo $is_sel ? ' is-selected' : ''; ?>" title="<?php echo esc_attr( $color['name'] ); ?>">
				<input
					type="checkbox"
					name="filter_<?php echo esc_attr( $taxonomy ); ?>[]"
					value="<?php echo esc_attr( $color['slug'] ); ?>"
					<?php checked( $is_sel, true ); ?>
					class="color-filter-checkbox"
					aria-label="<?php echo esc_attr( $color['name'] ); ?>"
				>
				<span class="color-dot" style="background-color:<?php echo esc_attr( $color['hex'] ); ?>;"></span>
			</label>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Render legacy color filter (no taxonomy-specific loader).
 */
function etheme_render_filter_section_color_legacy( $colors, $filter_params ) {
	$selected = isset( $filter_params['colors'] ) ? $filter_params['colors'] : array();
	?>
	<div class="filter-widget" data-aos="fade-up">
		<h4 class="filter-widget-title"><?php esc_html_e( 'Color', 'etheme' ); ?></h4>
		<div class="color-swatch-list">
			<?php foreach ( $colors as $color ) :
				$is_sel = in_array( $color['slug'], $selected, true );
			?>
			<label class="color-swatch-label<?php echo $is_sel ? ' is-selected' : ''; ?>" title="<?php echo esc_attr( $color['name'] ); ?>">
				<input
					type="checkbox"
					name="filter_colors[]"
					value="<?php echo esc_attr( $color['slug'] ); ?>"
					<?php checked( $is_sel, true ); ?>
					class="color-filter-checkbox"
					aria-label="<?php echo esc_attr( $color['name'] ); ?>"
				>
				<span class="color-dot" style="background-color:<?php echo esc_attr( $color['hex'] ); ?>;"></span>
			</label>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Render attribute (size/tag) filter section.
 */
function etheme_render_filter_section_attribute( $item, $filter_params ) {
	$taxonomy = isset( $item['taxonomy'] ) ? $item['taxonomy'] : 'pa_size';
	$label    = isset( $item['label'] ) ? $item['label'] : __( 'Attribute', 'etheme' );
	$terms    = etheme_get_product_attribute_terms( $taxonomy );
	$selected = isset( $filter_params['attributes'][ $taxonomy ] ) ? $filter_params['attributes'][ $taxonomy ] : array();
	if ( empty( $terms ) ) {
		return;
	}
	?>
	<div class="filter-widget" data-aos="fade-up">
		<h4 class="filter-widget-title"><?php echo esc_html( $label ); ?></h4>
		<div class="flex flex-wrap gap-2">
			<?php foreach ( $terms as $term ) :
				$is_sel = in_array( $term['slug'], $selected, true );
			?>
			<label class="attr-pill<?php echo $is_sel ? ' is-selected' : ''; ?>">
				<input
					type="checkbox"
					name="filter_<?php echo esc_attr( $taxonomy ); ?>[]"
					value="<?php echo esc_attr( $term['slug'] ); ?>"
					<?php checked( $is_sel, true ); ?>
					class="size-filter-checkbox hidden"
				>
				<?php echo esc_html( $term['name'] ); ?>
			</label>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Render legacy size filter section.
 */
function etheme_render_filter_section_size_legacy( $sizes, $filter_params ) {
	$selected = isset( $filter_params['sizes'] ) ? $filter_params['sizes'] : array();
	?>
	<div class="filter-widget" data-aos="fade-up">
		<h4 class="filter-widget-title"><?php esc_html_e( 'Size', 'etheme' ); ?></h4>
		<div class="flex flex-wrap gap-2">
			<?php foreach ( $sizes as $size ) :
				$is_sel = in_array( $size['slug'], $selected, true );
			?>
			<label class="attr-pill<?php echo $is_sel ? ' is-selected' : ''; ?>">
				<input
					type="checkbox"
					name="filter_sizes[]"
					value="<?php echo esc_attr( $size['slug'] ); ?>"
					<?php checked( $is_sel, true ); ?>
					class="size-filter-checkbox hidden"
				>
				<?php echo esc_html( $size['name'] ); ?>
			</label>
			<?php endforeach; ?>
		</div>
	</div>
	<?php
}

/**
 * Render price range filter section.
 */
function etheme_render_filter_section_price( $item, $filter_params, $min_price, $max_price, $current_min, $current_max ) {
	$range_color = isset( $item['rangeColor'] ) ? $item['rangeColor'] : '#2c5858';
	$span        = max( 1, $max_price - $min_price );
	$fill_left   = ( ( $current_min - $min_price ) / $span ) * 100;
	$fill_width  = ( ( $current_max - $current_min ) / $span ) * 100;
	?>
	<div class="filter-widget filters-price-section" data-aos="fade-up">
		<h4 class="filter-widget-title"><?php esc_html_e( 'Price', 'etheme' ); ?></h4>
		<div class="price-filter-content price-range-wrapper" style="--price-range-color:<?php echo esc_attr( $range_color ); ?>">
			<div class="price-range-slider-container relative">
				<div class="price-range-track relative h-2 bg-gray-200 rounded-none">
					<div class="price-range-fill absolute h-2 rounded-none" style="left:<?php echo esc_attr( $fill_left ); ?>%;width:<?php echo esc_attr( $fill_width ); ?>%;"></div>
				</div>
				<input type="range" name="min_price"
					min="<?php echo esc_attr( $min_price ); ?>"
					max="<?php echo esc_attr( $max_price ); ?>"
					value="<?php echo esc_attr( $current_min ); ?>"
					class="price-range-input price-range-min absolute w-full bg-transparent appearance-none cursor-pointer"
					data-min="<?php echo esc_attr( $min_price ); ?>"
					data-max="<?php echo esc_attr( $max_price ); ?>"
				>
				<input type="range" name="max_price"
					min="<?php echo esc_attr( $min_price ); ?>"
					max="<?php echo esc_attr( $max_price ); ?>"
					value="<?php echo esc_attr( $current_max ); ?>"
					class="price-range-input price-range-max absolute w-full bg-transparent appearance-none cursor-pointer"
					data-min="<?php echo esc_attr( $min_price ); ?>"
					data-max="<?php echo esc_attr( $max_price ); ?>"
				>
			</div>
			<div class="flex items-center justify-between mt-4 gap-2">
				<span class="text-sm font-medium price-min-display" style="color:<?php echo esc_attr( $range_color ); ?>">$<?php echo number_format( $current_min, 0 ); ?></span>
				<span class="text-sm font-medium price-max-display" style="color:<?php echo esc_attr( $range_color ); ?>">$<?php echo number_format( $current_max, 0 ); ?></span>
			</div>
		</div>
	</div>
	<?php
}
