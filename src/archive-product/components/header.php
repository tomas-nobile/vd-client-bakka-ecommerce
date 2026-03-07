<?php
/**
 * Product Archive Header Component
 *
 * Renders the archive header with title, results count, sorting and top categories.
 *
 * @param array $filter_params Filter parameters from request.
 * @param int   $total_products Total products found.
 * @param array $attributes Block attributes.
 * @param array|null $sorting_data Optional sorting data for rendering.
 * @param bool       $show_parent_category_bar Whether to show parent categories bar.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_archive_header( $filter_params, $total_products, $attributes, $sorting_data = null, $show_parent_category_bar = true ) {
	$title = '';
	if ( ! empty( $filter_params['search'] ) ) {
		$title = $filter_params['search'];
	} elseif ( is_product_category() || is_product_tag() ) {
		$title = single_term_title( '', false );
	} else {
		$title = esc_html__( 'SHOP', 'etheme' );
	}
	
	$parent_categories = etheme_get_parent_product_categories();
	$active_parent_id = etheme_get_active_parent_category_id( $filter_params );
	$show_parent_category_bar = (bool) $show_parent_category_bar;
	$shop_url = wc_get_page_permalink( 'shop' );
	$parent_label = '';
	$parent_url = '';
	if ( $active_parent_id ) {
		$parent_term = get_term( $active_parent_id, 'product_cat' );
		if ( $parent_term && ! is_wp_error( $parent_term ) ) {
			$parent_label = $parent_term->name;
			$parent_url = get_term_link( $parent_term );
		}
	}
	?>
	
	<section class="bg-black text-white w-full px-4 md:px-[10vw] lg:px-[15vw] py-[40px] lg:py-[60px] mt-[-30px]">
		<div class="flex flex-col gap-6">
			<div class="flex flex-col md:flex-row md:items-center gap-4 md:gap-0 justify-between flex-wrap">
				<div>
					<?php if ( $shop_url && is_product_category() && $parent_label && ! is_wp_error( $parent_url ) ) : ?>
					<p class="text-white/60 text-xs font-medium mb-3">
						<a href="<?php echo esc_url( $shop_url ); ?>" class="hover:underline">
							<?php echo esc_html__( 'Shop', 'etheme' ); ?>
						</a>
						<span class="mx-1">/</span>
						<a href="<?php echo esc_url( $parent_url ); ?>" class="hover:underline">
							<?php echo esc_html( $parent_label ); ?>
						</a>
					</p>
					<?php endif; ?>
					<h1 class="text-[50px] sm:text-[60px] font-semibold leading-tight">
						<?php echo esc_html( $title ); ?>
					</h1>
					<?php if ( ! empty( $filter_params['search'] ) ) : ?>
					<p class="text-white/50 text-sm mt-1"><?php esc_html_e( 'Resultados de busqueda', 'etheme' ); ?></p>
					<?php endif; ?>
				</div>
			</div>
			
			<?php if ( $show_parent_category_bar && ! empty( $parent_categories ) && ! is_wp_error( $parent_categories ) ) : ?>
			<div class="relative">
				<div class="archive-category-scroll flex gap-2 overflow-x-auto pb-2">
					<?php foreach ( $parent_categories as $category ) : ?>
						<?php
						$is_active = $active_parent_id && ( $active_parent_id === absint( $category->term_id ) );
						$term_link = get_term_link( $category );
						if ( is_wp_error( $term_link ) ) {
							continue;
						}
						?>
						<a
							href="<?php echo esc_url( $term_link ); ?>"
							class="inline-flex items-center px-4 py-2 rounded-md text-sm font-semibold border transition-colors no-underline <?php echo $is_active ? 'bg-white text-black border-white' : 'border-white/40 text-white hover:bg-white/10'; ?>"
						>
							<?php echo esc_html( $category->name ); ?>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
			<?php endif; ?>
		</div>
	</section>
	<?php
}
