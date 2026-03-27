<?php
/**
 * Single Product Index - Main orchestrator for single product page
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get the global product object
global $product;

// If no product, try to get it from the post
if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
	$product = wc_get_product( get_the_ID() );
}

// If still no product, show error message
if ( ! $product ) {
	?>
	<div <?php echo get_block_wrapper_attributes(); ?>>
		<p class="text-center text-gray-500 py-8"><?php esc_html_e( 'Producto no encontrado.', 'etheme' ); ?></p>
	</div>
	<?php
	return;
}

// Include helper functions
require_once get_template_directory() . '/src/single-product/includes/helpers.php';

// Auto-load components
$components_dir = get_template_directory() . '/src/single-product/components/';
$components = array(
	'breadcrumb',
	'gallery',
	'image-modal',
	'product-info',
	'variations',
	'add-to-cart',
	'tabs',
	'related-products',
);

foreach ( $components as $component ) {
	$component_file = $components_dir . $component . '.php';
	if ( file_exists( $component_file ) ) {
		require_once $component_file;
	}
}

// Extract and validate attributes with defaults
$defaults = array(
	'showThumbnails'      => true,
	'showSku'             => true,
	'showCategories'      => true,
	'showTags'            => true,
	'showRelatedProducts' => true,
	'relatedProductsCount' => 4,
);
$attributes = wp_parse_args( $attributes, $defaults );

// Get gallery images
$gallery_image_ids = etheme_get_product_gallery_images( $product );
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'single-product-block pt-0 pb-12 md:pb-24 lg:pb-32' ) ); ?>>
	<div class="mx-auto max-w-[1200px]">
		<div>
			
			<!-- Breadcrumb Component -->
			<?php
			if ( function_exists( 'etheme_render_product_breadcrumb' ) ) {
				etheme_render_product_breadcrumb();
			}
			?>
			
			<!-- Main Product Content: Zara/Nike-style layout -->
			<div class="grid gap-10 lg:grid-cols-[minmax(0,7fr)_minmax(0,5fr)] mb-10">
				
				<!-- Left Column: Gallery -->
				<div class="product-gallery-column w-full">
					<?php
					if ( function_exists( 'etheme_render_product_gallery' ) ) {
						etheme_render_product_gallery( $product, $attributes );
					}
					?>
				</div>
				
				<!-- Right Column: Product Info, Variations, Add to Cart -->
				<div class="w-full lg:sticky lg:top-24 h-fit">
					<div class="max-w-xl">
						<?php
						if ( function_exists( 'etheme_render_product_info' ) ) {
							etheme_render_product_info( $product, $attributes );
						}

						// Render variations for variable products
						if ( $product->is_type( 'variable' ) && function_exists( 'etheme_render_product_variations' ) ) {
							etheme_render_product_variations( $product );
						}

						if ( function_exists( 'etheme_render_add_to_cart' ) ) {
							etheme_render_add_to_cart( $product );
						}
						?>
					</div>
				</div>
			</div>

			<!-- Product Details -->
			<?php
			if ( function_exists( 'etheme_render_product_tabs' ) ) {
				etheme_render_product_tabs( $product );
			}
			?>

			<!-- Related Products -->
			<?php
			if ( $attributes['showRelatedProducts'] && function_exists( 'etheme_render_related_products' ) ) {
				etheme_render_related_products( $product, $attributes['relatedProductsCount'] );
			}
			?>
			
			<!-- Image Modal (hidden by default) -->
			<?php
			if ( function_exists( 'etheme_render_image_modal' ) ) {
				etheme_render_image_modal( $gallery_image_ids );
			}
			?>
		</div>
	</div>
</div>
