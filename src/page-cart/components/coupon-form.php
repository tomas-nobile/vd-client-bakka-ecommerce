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
	$has_coupons     = ! empty( $applied_coupons );
	?>

	<div class="coupon-form mb-6" id="coupon-section">

		<!-- Applied Coupons -->
		<?php if ( $has_coupons ) : ?>
		<div class="applied-coupons mb-3" id="applied-coupons">
			<p class="text-sm font-medium text-gray-700 mb-2">
				<?php esc_html_e( 'Applied Coupons', 'etheme' ); ?>
			</p>
			<div class="space-y-2">
				<?php foreach ( $applied_coupons as $coupon_code ) :
					$discount = $cart->get_coupon_discount_amount( $coupon_code );
				?>
				<div class="coupon-tag flex items-center justify-between bg-green-50 border border-green-200 rounded px-3 py-2"
					 data-coupon="<?php echo esc_attr( $coupon_code ); ?>">
					<div class="flex items-center">
						<svg class="w-4 h-4 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
						</svg>
						<span class="text-sm font-medium text-green-800 uppercase"><?php echo esc_html( $coupon_code ); ?></span>
						<span class="text-sm text-green-600 ml-2">-<?php echo wp_kses_post( wc_price( $discount ) ); ?></span>
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

		<!-- Coupon Trigger -->
		<button type="button"
				id="coupon-trigger"
				class="coupon-trigger flex items-center gap-2 w-full px-4 py-3 border border-gray-200 rounded text-sm font-medium text-gray-600 hover:bg-gray-50 transition"
				aria-expanded="false"
				aria-controls="coupon-form-panel">
			<svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
				<path fill-rule="evenodd" clip-rule="evenodd" d="M3.75 6.75L4.5 6H20.25L21 6.75V10.7812H20.25C19.5769 10.7812 19.0312 11.3269 19.0312 12C19.0312 12.6731 19.5769 13.2188 20.25 13.2188H21V17.25L20.25 18L4.5 18L3.75 17.25V13.2188H4.5C5.1731 13.2188 5.71875 12.6731 5.71875 12C5.71875 11.3269 5.1731 10.7812 4.5 10.7812H3.75V6.75ZM5.25 7.5V9.38602C6.38677 9.71157 7.21875 10.7586 7.21875 12C7.21875 13.2414 6.38677 14.2884 5.25 14.614V16.5L9 16.5L9 7.5H5.25ZM10.5 7.5V16.5L19.5 16.5V14.614C18.3632 14.2884 17.5312 13.2414 17.5312 12C17.5312 10.7586 18.3632 9.71157 19.5 9.38602V7.5H10.5Z"/>
			</svg>
			<span class="flex-1 text-left">
				<?php if ( $has_coupons ) : ?>
					<?php esc_html_e( 'Agregar otro cupón', 'etheme' ); ?>
				<?php else : ?>
					<?php esc_html_e( 'Agregar cupón', 'etheme' ); ?>
				<?php endif; ?>
			</span>
			<svg class="coupon-chevron w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
			</svg>
		</button>

		<!-- Coupon Panel (toggled by JS) -->
		<div id="coupon-form-panel" class="coupon-form-panel hidden mt-3">
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

	</div>
	<?php
}
