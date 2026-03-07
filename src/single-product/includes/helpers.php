<?php
/**
 * Single Product Block - Helper Functions
 *
 * Utility functions used across single product components.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get all gallery images for a product (including featured image)
 *
 * @param WC_Product $product WooCommerce product object.
 * @return array Array of image IDs.
 */
function etheme_get_product_gallery_images( $product ) {
	$images = array();
	
	// Add featured image first
	$featured_image_id = $product->get_image_id();
	if ( $featured_image_id ) {
		$images[] = $featured_image_id;
	}
	
	// Add gallery images
	$gallery_image_ids = $product->get_gallery_image_ids();
	if ( ! empty( $gallery_image_ids ) ) {
		$images = array_merge( $images, $gallery_image_ids );
	}
	
	return array_unique( $images );
}

/**
 * Check if a product is considered "new".
 *
 * @param WC_Product $product WooCommerce product object.
 * @param int        $days    Number of days since creation.
 * @return bool True if product is new.
 */
function etheme_is_product_new( $product, $days = 30 ) {
	$created = $product->get_date_created();

	if ( ! $created ) {
		return false;
	}

	return ( time() - $created->getTimestamp() ) <= ( $days * DAY_IN_SECONDS );
}

/**
 * Get column span class for gallery mosaic.
 *
 * @param int $index Zero-based index.
 * @return string Tailwind class for column span.
 */
function etheme_get_gallery_mosaic_span( $index ) {
	return ( ( $index + 1 ) % 3 === 0 ) ? 'col-span-2' : 'col-span-1';
}

/**
 * Render rating stars.
 *
 * @param float $rating Average rating.
 * @param int   $max    Max stars.
 * @return void
 */
function etheme_render_rating_stars( $rating, $max = 5 ) {
	$filled = (int) round( $rating );
	$filled = max( 0, min( $filled, $max ) );
	?>
	<div class="flex gap-3">
		<?php for ( $i = 1; $i <= $max; $i++ ) : ?>
			<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
				<path d="M19.9479 7.24277C19.8169 6.83779 19.4577 6.55016 19.0328 6.51186L13.2602 5.9877L10.9776 0.645006C10.8093 0.253455 10.426 0 10.0001 0C9.57422 0 9.19091 0.253455 9.0226 0.645921L6.73998 5.9877L0.966514 6.51186C0.542309 6.55107 0.184023 6.83779 0.0523365 7.24277C-0.0793503 7.64775 0.0422654 8.09194 0.363166 8.37195L4.72653 12.1986L3.43987 17.8664C3.34573 18.2831 3.50747 18.7139 3.85325 18.9638C4.0391 19.0981 4.25655 19.1664 4.47582 19.1664C4.66488 19.1664 4.85242 19.1155 5.02073 19.0148L10.0001 16.0388L14.9776 19.0148C15.3419 19.2339 15.801 19.2139 16.146 18.9638C16.492 18.7131 16.6536 18.2822 16.5594 17.8664L15.2728 12.1986L19.6361 8.37271C19.957 8.09194 20.0796 7.64851 19.9479 7.24277Z" fill="<?php echo $i <= $filled ? '#FC8964' : '#FED5C8'; ?>" />
			</svg>
		<?php endfor; ?>
	</div>
	<?php
}

/**
 * Check if product has content for a specific tab
 *
 * @param WC_Product $product WooCommerce product object.
 * @param string     $tab     Tab identifier: 'description', 'additional_information', 'reviews'.
 * @return bool True if tab has content.
 */
function etheme_product_has_tab_content( $product, $tab ) {
	switch ( $tab ) {
		case 'description':
			return ! empty( $product->get_description() );
			
		case 'additional_information':
			// Check if product has attributes to display
			$attributes = $product->get_attributes();
			if ( empty( $attributes ) ) {
				return false;
			}
			// Check if any attribute is visible on product page
			foreach ( $attributes as $attribute ) {
				if ( $attribute->get_visible() ) {
					return true;
				}
			}
			return false;
			
		case 'reviews':
			// Check if reviews are enabled and product has reviews or allows new ones
			return comments_open( $product->get_id() ) || $product->get_review_count() > 0;
			
		default:
			return false;
	}
}

/**
 * Get stock status text and class for a product
 *
 * @param WC_Product $product WooCommerce product object.
 * @return array Array with 'text' and 'class' keys.
 */
function etheme_get_stock_status_info( $product ) {
	$stock_status = $product->get_stock_status();
	
	switch ( $stock_status ) {
		case 'instock':
			return array(
				'text'  => __( 'In Stock', 'etheme' ),
				'class' => 'text-green-600 bg-green-100',
			);
		case 'outofstock':
			return array(
				'text'  => __( 'Out of Stock', 'etheme' ),
				'class' => 'text-red-600 bg-red-100',
			);
		case 'onbackorder':
			return array(
				'text'  => __( 'Available on Backorder', 'etheme' ),
				'class' => 'text-yellow-600 bg-yellow-100',
			);
		default:
			return array(
				'text'  => '',
				'class' => '',
			);
	}
}

/**
 * Get variation data for JavaScript
 *
 * @param WC_Product_Variable $product Variable product object.
 * @return array Variation data array.
 */
function etheme_get_variation_data( $product ) {
	if ( ! $product->is_type( 'variable' ) ) {
		return array();
	}
	
	$variations = array();
	$available_variations = $product->get_available_variations();
	
	foreach ( $available_variations as $variation ) {
		$variation_obj = wc_get_product( $variation['variation_id'] );
		if ( ! $variation_obj ) {
			continue;
		}
		
		$variations[] = array(
			'variation_id'   => $variation['variation_id'],
			'attributes'     => $variation['attributes'],
			'price_html'     => $variation_obj->get_price_html(),
			'is_in_stock'    => $variation_obj->is_in_stock(),
			'stock_quantity' => $variation_obj->get_stock_quantity(),
			'stock_status'   => $variation_obj->get_stock_status(),
			'image'          => array(
				'src'    => wp_get_attachment_image_url( $variation_obj->get_image_id(), 'woocommerce_single' ),
				'srcset' => wp_get_attachment_image_srcset( $variation_obj->get_image_id(), 'woocommerce_single' ),
				'thumb'  => wp_get_attachment_image_url( $variation_obj->get_image_id(), 'woocommerce_thumbnail' ),
			),
		);
	}
	
	// Get available attributes
	$attributes = array();
	foreach ( $product->get_variation_attributes() as $attribute_name => $options ) {
		$attributes[ $attribute_name ] = $options;
	}
	
	return array(
		'productId'   => $product->get_id(),
		'variations'  => $variations,
		'attributes'  => $attributes,
	);
}
