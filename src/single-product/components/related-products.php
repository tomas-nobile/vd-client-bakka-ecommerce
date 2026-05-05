<?php
/**
 * Related Products Component
 *
 * Renders related products grid using the shared core card component.
 * Cards use pp-feature-box with full-link overlay for accessibility.
 * Fade-up animation is handled by initFadeUp (view.js) via [data-aos].
 *
 * @param WC_Product $product Current product.
 * @param int        $limit   Number of products to show.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_related_products( $product, $limit = 4 ) {
	$related_ids = wc_get_related_products( $product->get_id(), $limit );

	if ( empty( $related_ids ) ) {
		return;
	}

	$related_products = wc_get_products( array(
		'include' => $related_ids,
		'limit'   => $limit,
		'orderby' => 'post__in',
		'status'  => 'publish',
	) );

	if ( empty( $related_products ) ) {
		return;
	}

	if ( ! function_exists( 'etheme_render_home_popular_product_card' ) ) {
		require_once get_template_directory() . '/src/core/components/product-card.php';
	}
	?>

	<div class="related-products pt-10 mt-12">

		<h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-600 mb-6">
			<?php esc_html_e( 'Productos relacionados', 'etheme' ); ?>
		</h2>

		<div class="flex flex-wrap -mx-2 md:-mx-4 min-w-0">
			<?php
			$index = 0;
			foreach ( $related_products as $related_product ) :
				$aos_delay = ( $index % 4 ) * 80;
			?>
				<div class="w-1/2 lg:w-1/4 px-2 md:px-4 min-w-0 box-border mb-4"
					 data-aos="fade-up"
					 data-aos-delay="<?php echo esc_attr( $aos_delay ); ?>">
					<?php etheme_render_home_popular_product_card( $related_product, true ); ?>
				</div>
			<?php
				$index++;
			endforeach;
			?>
		</div>

	</div>
	<?php
}
