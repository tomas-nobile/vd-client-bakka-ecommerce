<?php
// home-popular-products.
/**
 * Home Popular Products Component
 *
 * Displays WooCommerce products ordered by popularity (total_sales).
 * Products are grouped by category with tabs (Contrive design: underline style).
 * Cards are rendered via etheme_render_home_popular_product_card (popular-products-card.php).
 *
 * @param array $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_home_popular_products( $attributes ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$order_by     = $attributes['productsOrderBy'];
	$per_category = absint( $attributes['productsPerCategory'] );

	$parent_categories = get_terms( array(
		'taxonomy'   => 'product_cat',
		'hide_empty' => true,
		'parent'     => 0,
		'exclude'    => array( get_option( 'default_product_cat' ) ),
		'orderby'    => 'count',
		'order'      => 'DESC',
	) );

	if ( is_wp_error( $parent_categories ) || empty( $parent_categories ) ) {
		return;
	}

	$all_products = etheme_get_popular_products( 0, $per_category, $order_by );
	if ( empty( $all_products ) ) {
		return;
	}
	?>

	<section class="popular-products-section py-16 md:py-24" aria-labelledby="popular-products-heading" data-aos="fade-up">
		<div class="container mx-auto px-6 md:px-12 lg:px-20">
			<?php etheme_render_products_header(); ?>
			<?php etheme_render_products_tabs( $parent_categories ); ?>
			<?php etheme_render_products_panels( $parent_categories, $all_products, $per_category, $order_by ); ?>
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

function etheme_render_products_tabs( $categories ) {
	?>
	<nav class="pp-tabs-nav" role="tablist" aria-label="<?php esc_attr_e( 'Categorías de productos', 'etheme' ); ?>" data-aos="fade-up">
		<button
			id="pp-tab-all"
			role="tab"
			aria-selected="true"
			aria-controls="products-panel-all"
			class="pp-tab pp-tab--active"
			data-tab="all"
		>
			<?php esc_html_e( 'Todos', 'etheme' ); ?>
		</button>
		<?php foreach ( $categories as $cat ) : ?>
		<button
			id="pp-tab-<?php echo esc_attr( $cat->slug ); ?>"
			role="tab"
			aria-selected="false"
			aria-controls="products-panel-<?php echo esc_attr( $cat->slug ); ?>"
			class="pp-tab"
			data-tab="<?php echo esc_attr( $cat->slug ); ?>"
		>
			<?php echo esc_html( $cat->name ); ?>
		</button>
		<?php endforeach; ?>
	</nav>
	<?php
}

function etheme_render_products_panels( $categories, $all_products, $per_category, $order_by ) {
	?>
	<div id="products-panel-all" role="tabpanel" aria-labelledby="pp-tab-all" class="pp-panel pp-panel--active">
		<?php etheme_render_pp_product_grid( $all_products ); ?>
	</div>
	<?php foreach ( $categories as $cat ) :
		$cat_products = etheme_get_popular_products( $cat->term_id, $per_category, $order_by );
	?>
	<div
		id="products-panel-<?php echo esc_attr( $cat->slug ); ?>"
		role="tabpanel"
		aria-labelledby="pp-tab-<?php echo esc_attr( $cat->slug ); ?>"
		class="pp-panel"
	>
		<?php if ( ! empty( $cat_products ) ) : ?>
			<?php etheme_render_pp_product_grid( $cat_products ); ?>
		<?php else : ?>
			<p class="text-center py-8 pp-section-subtitle">
				<?php esc_html_e( 'No hay productos en esta categoría.', 'etheme' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
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
