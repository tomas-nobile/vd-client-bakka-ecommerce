<?php
// home-popular-products.
/**
 * Home Popular Products Component
 *
 * Displays 3 WooCommerce products ordered by popularity (total_sales).
 * No category filtering; a single grid is shown.
 * Cards are rendered via etheme_render_home_popular_product_card (popular-products-card.php).
 *
 * @param array $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! defined( 'ETHEME_POPULAR_PRODUCTS_LIMIT' ) ) {
	define( 'ETHEME_POPULAR_PRODUCTS_LIMIT', 3 );
}

function etheme_render_home_popular_products( $attributes ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$order_by = isset( $attributes['productsOrderBy'] ) ? $attributes['productsOrderBy'] : 'total_sales';
	$products = etheme_get_popular_products( 0, ETHEME_POPULAR_PRODUCTS_LIMIT, $order_by );

	if ( empty( $products ) ) {
		return;
	}
	?>

	<section class="popular-products-section py-16 md:py-24" aria-labelledby="popular-products-heading" data-aos="fade-up">
		<div class="container mx-auto px-6 md:px-12 lg:px-20">
			<?php etheme_render_products_header(); ?>
			<?php etheme_render_pp_product_grid( $products ); ?>
		</div>
	</section>

	<?php
}

function etheme_render_products_header() {
	?>
	<div class="text-center mb-10" data-aos="fade-up">
		<p class="pp-section-subtitle">
			<?php esc_html_e( 'Artículos Destacados', 'etheme' ); ?>
		</p>
		<h2 id="popular-products-heading" class="pp-section-title">
			<?php esc_html_e( 'Nuestros Productos Populares', 'etheme' ); ?>
		</h2>
	</div>
	<?php
}

function etheme_render_pp_product_grid( $products ) {
	?>
	<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8" data-aos="fade-up">
		<?php foreach ( $products as $product ) {
			etheme_render_home_popular_product_card( $product );
		} ?>
	</div>
	<?php
}
