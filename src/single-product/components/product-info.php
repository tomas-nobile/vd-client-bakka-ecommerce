<?php
/**
 * Product Info Component
 *
 * Renders product title, price, SKU, stock status, categories, and tags.
 *
 * @param WC_Product $product    WooCommerce product object.
 * @param array      $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_product_info( $product, $attributes ) {
	$stock_info = etheme_get_stock_status_info( $product );
	$review_count = (int) $product->get_review_count();
	$average_rating = (float) $product->get_average_rating();
	$badge_text = '';

	if ( etheme_is_product_new( $product ) ) {
		$badge_text = __( 'New', 'etheme' );
	} elseif ( $product->is_featured() ) {
		$badge_text = __( 'Featured', 'etheme' );
	}
	?>
	
	<div class="product-info">
		<div class="max-w-xl">
			
			<!-- Badge + Reviews -->
			<div class="flex items-center justify-between flex-wrap gap-4 mb-4">
				<?php if ( $badge_text ) : ?>
				<span class="inline-block bg-black rounded-full px-3 py-1 text-center uppercase text-white text-[10px] font-semibold tracking-[0.2em]">
					<?php echo esc_html( $badge_text ); ?>
				</span>
				<?php endif; ?>
				
				<div class="flex items-center gap-4 flex-wrap">
					<p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-semibold">
						<?php echo esc_html( sprintf( _n( '%d review', '%d reviews', $review_count, 'etheme' ), $review_count ) ); ?>
					</p>
					<?php etheme_render_rating_stars( $average_rating ); ?>
				</div>
			</div>
			
			<!-- Product Title -->
			<h1 class="text-gray-900 font-semibold text-3xl md:text-4xl mb-3 tracking-tight">
				<?php echo esc_html( $product->get_name() ); ?>
			</h1>
			
			<!-- Price -->
			<div class="product-price text-xl md:text-2xl text-gray-700 font-medium mb-6" id="product-price">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>
			
			<!-- Short Description -->
			<?php if ( $product->get_short_description() ) : ?>
			<div class="product-short-description text-sm text-gray-700 mb-6 prose prose-sm max-w-none">
				<?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
			</div>
			<?php endif; ?>
			
			<!-- Stock Status -->
			<?php if ( $stock_info['text'] ) : ?>
			<div class="product-stock mb-6" id="product-stock">
				<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium <?php echo esc_attr( $stock_info['class'] ); ?>">
					<?php if ( $product->is_in_stock() ) : ?>
					<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
						<path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
					</svg>
					<?php else : ?>
					<svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
						<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
					</svg>
					<?php endif; ?>
					<?php echo esc_html( $stock_info['text'] ); ?>
				</span>
			</div>
			<?php endif; ?>
			
			<!-- Product Meta -->
			<div class="product-meta border-t border-gray-200 pt-4 mt-6 space-y-2 text-sm text-gray-500">
				
				<!-- SKU -->
				<?php if ( $attributes['showSku'] && $product->get_sku() ) : ?>
				<div class="product-sku">
					<span class="font-medium text-gray-700"><?php esc_html_e( 'SKU:', 'etheme' ); ?></span>
					<span class="ml-2"><?php echo esc_html( $product->get_sku() ); ?></span>
				</div>
				<?php endif; ?>
				
				<!-- Categories -->
				<?php if ( $attributes['showCategories'] ) : 
					$categories = wc_get_product_category_list( $product->get_id(), ', ', '<span class="posted_in">' . _n( 'Category:', 'Categories:', count( $product->get_category_ids() ), 'etheme' ) . ' ', '</span>' );
					if ( $categories ) :
				?>
				<div class="product-categories">
					<span class="font-medium text-gray-700"><?php echo wp_kses_post( $categories ); ?></span>
				</div>
				<?php 
					endif;
				endif; 
				?>
				
				<!-- Tags -->
				<?php if ( $attributes['showTags'] ) : 
					$tags = wc_get_product_tag_list( $product->get_id(), ', ', '<span class="tagged_as">' . _n( 'Tag:', 'Tags:', count( $product->get_tag_ids() ), 'etheme' ) . ' ', '</span>' );
					if ( $tags ) :
				?>
				<div class="product-tags">
					<span class="font-medium text-gray-700"><?php echo wp_kses_post( $tags ); ?></span>
				</div>
				<?php 
					endif;
				endif; 
				?>
				
			</div>
			
		</div>
	</div>
	<?php
}
