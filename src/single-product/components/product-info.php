<?php
/**
 * Product Info Component
 *
 * Renders product title, price, reviews and short description.
 *
 * @param WC_Product $product    WooCommerce product object.
 * @param array      $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_product_info( $product, $attributes ) {
	unset( $attributes );

	$review_count = (int) $product->get_review_count();
	$average_rating = (float) $product->get_average_rating();
	$badge_text = '';

	if ( etheme_is_product_new( $product ) ) {
		$badge_text = __( 'Nuevo', 'etheme' );
	} elseif ( $product->is_featured() ) {
		$badge_text = __( 'Destacado', 'etheme' );
	}
	?>
	
	<div class="product-info">
		<div class="max-w-xl">
			
			<!-- Badge + Reviews -->
			<div class="flex items-center justify-between flex-wrap gap-4 mb-4">
				<?php if ( $badge_text ) : ?>
				<span class="inline-block bg-[#2b5756] rounded-full px-3 py-1 text-center uppercase text-white text-[10px] font-semibold tracking-[0.2em]">
					<?php echo esc_html( $badge_text ); ?>
				</span>
				<?php endif; ?>
				
				<div class="flex items-center gap-4 flex-wrap">
					<p class="text-[10px] uppercase tracking-[0.2em] text-gray-500 font-semibold">
						<?php echo esc_html( sprintf( _n( '%d reseña', '%d reseñas', $review_count, 'etheme' ), $review_count ) ); ?>
					</p>
					<?php etheme_render_rating_stars( $average_rating ); ?>
				</div>
			</div>
			
			<!-- Product Title -->
			<h1 class="text-gray-900 font-semibold text-3xl md:text-4xl mb-3 tracking-tight">
				<?php echo esc_html( $product->get_name() ); ?>
			</h1>
			
			<!-- Price -->
			<div class="product-price text-xl md:text-2xl text-gray-700 font-medium mb-2" id="product-price">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>

			<?php
			// Show "precio sin impuestos nacionales" as: precio / 1.21
			// We base it on WooCommerce "price including tax" to match the request formula.
			$tax_factor = 1.21;
			$price_including_tax = (float) wc_get_price_including_tax( $product, array( 'qty' => 1 ) );
			$price_without_national_taxes = ( $tax_factor > 0 ) ? ( $price_including_tax / $tax_factor ) : 0;
			?>
			<?php if ( $price_without_national_taxes > 0 ) : ?>
				<div class="product-price-without-national-taxes text-sm text-gray-600 mb-6">
					<?php
					echo esc_html__( 'Precio sin impuestos nacionales: ', 'etheme' ) . wp_kses_post( wc_price( $price_without_national_taxes ) );
					?>
				</div>
			<?php endif; ?>
			
			<!-- Short Description -->
			<?php if ( $product->get_short_description() ) : ?>
			<div class="product-short-description text-md text-gray-700 mb-6 prose prose-sm max-w-none">
				<?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
			</div>
			<?php endif; ?>
			
			<!-- Product meta removed as requested -->
			
		</div>
	</div>
	<?php
}
