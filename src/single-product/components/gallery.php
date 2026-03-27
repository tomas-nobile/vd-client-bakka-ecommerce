<?php
/**
 * Product Gallery Component
 *
 * Renders the product image gallery with main image and thumbnails.
 *
 * @param WC_Product $product    WooCommerce product object.
 * @param array      $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_product_gallery( $product, $attributes ) {
	$gallery_images = etheme_get_product_gallery_images( $product );
	$show_thumbnails = $attributes['showThumbnails'] && count( $gallery_images ) > 1;
	
	// Get main image (first of gallery)
	$main_image_id = ! empty( $gallery_images ) ? $gallery_images[0] : null;
	?>
	
	<div class="product-gallery" data-gallery-images="<?php echo esc_attr( wp_json_encode( $gallery_images ) ); ?>">
		<div class="gallery-grid flex flex-col-reverse lg:flex-row gap-4">
			<!-- Thumbnails: all images so the one leaving main stays visible -->
			<?php if ( $show_thumbnails ) : ?>
			<div class="gallery-thumbnails flex gap-3 overflow-x-auto lg:overflow-visible lg:flex-col lg:gap-4 lg:w-24" id="gallery-thumbnails">
				<?php foreach ( $gallery_images as $index => $image_id ) : ?>
				<?php $gallery_index = $index + 1; ?>
				<button type="button"
						class="thumbnail-item flex-none aspect-square w-16 md:w-20 overflow-hidden rounded-md bg-gray-100 border transition-all duration-200 hover:border-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-800 <?php echo $index === 0 ? 'border-gray-700' : 'border-transparent'; ?>"
						data-thumbnail
						data-image-id="<?php echo esc_attr( $image_id ); ?>"
						data-index="<?php echo esc_attr( $gallery_index ); ?>"
						data-full-src="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'full' ) ); ?>"
						data-large-src="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'woocommerce_single' ) ); ?>"
						aria-label="<?php echo esc_attr( sprintf( __( 'Ver imagen %d', 'etheme' ), $gallery_index ) ); ?>">
					<?php
					echo wp_get_attachment_image(
						$image_id,
						'woocommerce_thumbnail',
						false,
						array(
							'class' => 'w-full h-full object-cover',
						)
					);
					?>
				</button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>

			<!-- Main Image -->
			<div class="main-image-container relative flex-1 overflow-hidden bg-gray-100 rounded-md cursor-zoom-in"
				 id="product-main-image"
				 data-open-modal>
				<?php
				if ( $main_image_id ) {
					echo wp_get_attachment_image(
						$main_image_id,
						'woocommerce_single',
						false,
						array(
							'class'         => 'main-product-image block w-full h-auto object-cover transition-transform duration-300',
							'id'            => 'main-gallery-image',
							'data-image-id' => $main_image_id,
							'data-full-src' => wp_get_attachment_image_url( $main_image_id, 'full' ),
						)
					);
				} else {
					echo wc_placeholder_img( 'woocommerce_single', array(
						'class' => 'main-product-image block w-full h-auto object-cover',
						'id'    => 'main-gallery-image',
					) );
				}
				?>

				<!-- Zoom hint icon -->
				<div class="absolute bottom-4 right-4 bg-white/80 rounded-full p-2 shadow-md pointer-events-none">
					<svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
					</svg>
				</div>

				<?php if ( $product->is_on_sale() ) : ?>
				<span class="sale-badge absolute top-4 left-4 bg-black text-white text-xs font-semibold px-3 py-1 rounded-full tracking-wide uppercase z-10">
					<?php esc_html_e( 'Oferta', 'etheme' ); ?>
				</span>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
