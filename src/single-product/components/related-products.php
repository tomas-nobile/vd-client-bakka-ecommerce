<?php
/**
 * Related Products Component
 *
 * Renders related products grid using the card component from archive-product.
 *
 * @param WC_Product $product Current product.
 * @param int        $limit   Number of products to show.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_related_products( $product, $limit = 4 ) {
	// Get related products
	$related_ids = wc_get_related_products( $product->get_id(), $limit );
	
	// If no related products, don't render anything
	if ( empty( $related_ids ) ) {
		return;
	}
	
	// Include the card component from archive-product if not already loaded
	$card_component = get_template_directory() . '/src/archive-product/components/card.php';
	if ( file_exists( $card_component ) && ! function_exists( 'etheme_render_product_card' ) ) {
		require_once $card_component;
	}
	
	// Determine grid columns based on count
	$count = count( $related_ids );
	$grid_cols = min( $count, 4 );
	?>
	
	<div class="related-products pt-10 mt-12">
		
		<h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-gray-600 mb-6">
			<?php esc_html_e( 'Related Products', 'etheme' ); ?>
		</h2>
		
		<div class="related-products-grid grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-<?php echo esc_attr( $grid_cols ); ?> gap-6">
			<?php
			foreach ( $related_ids as $related_id ) {
				if ( function_exists( 'etheme_render_product_card' ) ) {
					etheme_render_product_card( $related_id );
				} else {
					// Fallback if card component not available
					etheme_render_simple_product_card( $related_id );
				}
			}
			?>
		</div>
		
	</div>
	<?php
}

/**
 * Simple fallback product card if archive-product card is not available
 *
 * @param int $product_id Product ID.
 */
function etheme_render_simple_product_card( $product_id ) {
	$product = wc_get_product( $product_id );
	
	if ( ! $product ) {
		return;
	}
	
	$permalink = get_permalink( $product_id );
	$title = get_the_title( $product_id );
	$thumbnail_id = get_post_thumbnail_id( $product_id );
	?>
	
	<article class="product-card bg-white rounded-lg shadow-md overflow-hidden transition-transform duration-300 hover:-translate-y-2 hover:shadow-xl">
		<div class="relative aspect-square overflow-hidden bg-gray-100">
			<a href="<?php echo esc_url( $permalink ); ?>" class="block w-full h-full">
				<?php 
				if ( $thumbnail_id ) {
					echo wp_get_attachment_image( 
						$thumbnail_id, 
						'woocommerce_thumbnail', 
						false,
						array( 
							'class' => 'w-full h-full object-cover transition-transform duration-300 hover:scale-105'
						) 
					);
				} else {
					echo wc_placeholder_img( 'woocommerce_thumbnail', array( 
						'class' => 'w-full h-full object-cover'
					) );
				}
				?>
			</a>
			<?php if ( $product->is_on_sale() ) : ?>
			<span class="sale-badge absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg">
				<?php esc_html_e( 'Sale', 'etheme' ); ?>
			</span>
			<?php endif; ?>
		</div>
		
		<div class="p-4">
			<h3 class="text-lg font-semibold mb-2 line-clamp-2 min-h-[3.5rem]">
				<a href="<?php echo esc_url( $permalink ); ?>" class="hover:text-blue-600 transition-colors duration-200">
					<?php echo esc_html( $title ); ?>
				</a>
			</h3>
			
			<div class="mb-4 text-xl font-bold text-red-600">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>
		</div>
	</article>
	<?php
}
