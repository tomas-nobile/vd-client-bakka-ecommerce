<?php
/**
 * Coupon Form Component
 *
 * Renders a coupon code input form with applied coupons list.
 *
 * @param WC_Cart $cart WooCommerce cart object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_coupon_form( $cart ) {
	$applied_coupons = $cart->get_applied_coupons();
	?>
	
	<div class="coupon-form mb-6" id="coupon-section">
		
		<!-- Applied Coupons -->
		<?php if ( ! empty( $applied_coupons ) ) : ?>
		<div class="applied-coupons mb-4" id="applied-coupons">
			<p class="text-sm font-medium text-gray-700 mb-2">
				<?php esc_html_e( 'Applied Coupons', 'etheme' ); ?>
			</p>
			<div class="space-y-2">
				<?php foreach ( $applied_coupons as $coupon_code ) : 
					$coupon = new WC_Coupon( $coupon_code );
					$discount = $cart->get_coupon_discount_amount( $coupon_code );
				?>
				<div class="coupon-tag flex items-center justify-between bg-green-50 border border-green-200 rounded px-3 py-2" 
					 data-coupon="<?php echo esc_attr( $coupon_code ); ?>">
					<div class="flex items-center">
						<svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
						</svg>
						<span class="text-sm font-medium text-green-800 uppercase"><?php echo esc_html( $coupon_code ); ?></span>
						<span class="text-sm text-green-600 ml-2">
							-<?php echo wp_kses_post( wc_price( $discount ) ); ?>
						</span>
					</div>
					<button type="button" 
							class="remove-coupon text-green-600 hover:text-green-800 transition"
							data-coupon="<?php echo esc_attr( $coupon_code ); ?>"
							aria-label="<?php esc_attr_e( 'Remove coupon', 'etheme' ); ?>">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
						</svg>
					</button>
				</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
		
		<!-- Coupon Input Form -->
		<form id="coupon-form" class="flex gap-2">
			<?php wp_nonce_field( 'etheme_apply_coupon', 'coupon_nonce' ); ?>
			<input type="text" 
				   id="coupon_code" 
				   name="coupon_code" 
				   placeholder="<?php esc_attr_e( 'Coupon code', 'etheme' ); ?>"
				   class="flex-1 px-4 py-3 border border-gray-200 rounded text-sm text-gray-900 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 uppercase"
				   autocomplete="off" />
			<button type="submit" 
					id="apply-coupon-btn"
					class="px-6 py-3 bg-white border border-gray-200 text-gray-900 text-sm font-medium rounded hover:bg-gray-50 transition disabled:opacity-50">
				<span class="button-text"><?php esc_html_e( 'Apply', 'etheme' ); ?></span>
				<span class="loading-spinner hidden">
					<svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
						<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
						<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
					</svg>
				</span>
			</button>
		</form>
		
		<!-- Coupon Message -->
		<div id="coupon-message" class="hidden mt-2 text-sm"></div>
	</div>
	<?php
}
