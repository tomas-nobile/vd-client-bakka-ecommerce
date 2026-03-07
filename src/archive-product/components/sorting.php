<?php
/**
 * Product Sorting Component
 * 
 * Renders a sorting dropdown for ordering products.
 * Preserves all active filters when changing sort order.
 *
 * @param array|null $filter_params Optional filter parameters. If null, fetches from request.
 * @param string     $current_sort  Current sort option (e.g., 'date-desc', 'price-asc').
 * @param bool       $show_sorting  Whether to display the sorting dropdown.
 * @param bool       $is_mobile     Whether this is for mobile view (affects styling and visibility).
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
	
	// Classes for mobile vs desktop visibility
	$container_classes = $is_mobile 
		? 'md:hidden' 
		: 'hidden md:block';
	
	?>
	
	<div class="sorting-container <?php echo esc_attr( $container_classes ); ?>">
		<form method="GET" action="" class="sorting-form">
			<?php
			// Preserve search and filters when sorting (not sort itself)
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

function etheme_render_sorting_select( $current_sort, $is_mobile = false, $auto_submit = true ) {
	$select_classes = $is_mobile
		? 'rounded-full bg-white border border-coolGray-200 py-2 px-4 text-coolGray-700 text-xs font-medium outline-none w-full appearance-none cursor-pointer'
		: 'rounded-full bg-white border border-coolGray-200 py-2 px-4 text-coolGray-700 text-xs font-medium outline-none appearance-none cursor-pointer';
	$on_change = $auto_submit ? ' onchange="this.form.submit()"' : '';
	?>
	<select name="orderby" class="<?php echo esc_attr( $select_classes ); ?>"<?php echo $on_change; ?>>
		<?php foreach ( etheme_get_sorting_options() as $option ) : ?>
			<option value="<?php echo esc_attr( $option['value'] ); ?>" <?php selected( $current_sort, $option['value'] ); ?>>
				<?php echo esc_html( $option['label'] ); ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php
}
