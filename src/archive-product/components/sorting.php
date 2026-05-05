<?php
/**
 * Product Sorting Component
 *
 * Renders a Contrive-style square sorting dropdown for ordering products.
 * Preserves all active filters when changing sort order.
 *
 * @param array|null $filter_params Optional filter parameters.
 * @param string     $current_sort  Current sort option (e.g., 'date-desc').
 * @param bool       $show_sorting  Whether to display the sorting dropdown.
 * @param bool       $is_mobile     Whether this is for mobile (affects visibility).
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_sorting( $filter_params = null, $current_sort = 'date-desc', $show_sorting = true, $is_mobile = false ) {
	if ( ! $show_sorting ) {
		return;
	}

	if ( ! $filter_params ) {
		$filter_params = etheme_get_filter_params();
	}

	$container_classes = $is_mobile ? 'md:hidden' : 'hidden md:block';
	?>
	<div class="sorting-container <?php echo esc_attr( $container_classes ); ?>">
		<form method="GET" action="" class="sorting-form">
			<?php
			etheme_render_preserved_params( $filter_params, array(
				'search'  => true,
				'sort'    => false,
				'filters' => true,
			) );
			?>
			<?php etheme_render_sorting_select( $current_sort, $is_mobile, true ); ?>
		</form>
	</div>
	<?php
}

/**
 * Render the Contrive-style sorting <select> element.
 */
function etheme_render_sorting_select( $current_sort, $is_mobile = false, $auto_submit = true ) {
	$on_change = $auto_submit ? ' onchange="this.form.submit()"' : '';
	?>
	<select
		name="orderby"
		class="shop-sort-select"
		<?php echo $on_change; ?>
		aria-label="<?php esc_attr_e( 'Ordenar productos', 'etheme' ); ?>"
	>
		<?php foreach ( etheme_get_sorting_options() as $option ) : ?>
			<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $current_sort, $option['value'] ); ?>>
				<?php echo esc_html( $option['label'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
