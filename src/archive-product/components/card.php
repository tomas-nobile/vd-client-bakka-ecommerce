<?php
/**
 * Product Card Component
 * 
 * Renders a single product card with image, title, price, and add to cart button.
 *
 * @param int $product_id The product ID to render.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_product_card( $product_id ) {
	$product = wc_get_product( $product_id );
	
	if ( ! $product ) {
		return;
	}
	
	$permalink = get_permalink( $product_id );
	$title = get_the_title( $product_id );
	$thumbnail_id = get_post_thumbnail_id( $product_id );
	?>
	
	<a class="block mb-10 group no-underline min-w-0 max-w-full" href="<?php echo esc_url( $permalink ); ?>">
		<div class="w-full max-w-full min-w-0 bg-coolGray-200 rounded-xl mb-3 relative overflow-hidden border-2 border-transparent group-hover:border-purple-500 transition duration-150">
			<?php if ( $product->is_on_sale() ) : ?>
			<div class="absolute left-5 top-5 uppercase bg-orange-500 py-1 px-3 rounded-full text-white text-xs font-bold text-center z-10">
				<?php esc_html_e( 'Sale', 'etheme' ); ?>
			</div>
			<?php elseif ( $product->is_featured() ) : ?>
			<div class="absolute left-5 top-5 uppercase bg-rhino-600 py-1 px-3 rounded-full text-white text-xs font-bold text-center z-10">
				<?php esc_html_e( 'Featured', 'etheme' ); ?>
			</div>
			<?php endif; ?>
			<?php 
			if ( $thumbnail_id ) {
				echo wp_get_attachment_image( 
					$thumbnail_id, 
					'woocommerce_thumbnail', 
					false,
					array( 
						'class' => 'w-full max-w-full h-auto object-cover'
					) 
				);
			} else {
				echo wc_placeholder_img( 'woocommerce_thumbnail', array( 
					'class' => 'w-full max-w-full h-auto object-cover'
				) );
			}
			?>
		</div>
		<p class="text-rhino-700 font-bold text-center mb-1 no-underline"><?php echo esc_html( $title ); ?></p>
		<p class="text-gray-700 font-semibold text-center no-underline"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
	</a>
	<?php
}

