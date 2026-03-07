<?php
/**
 * Postal Code Shipping Calculator Component
 *
 * Renders a compact shipping calculator with postal code input only.
 * Country is sent as hidden field (default AR). Easily adaptable for other countries.
 *
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_postal_code_shipping() {
	$default_country = WC()->customer->get_shipping_country() ?: 'AR';
	$current_postcode = WC()->customer->get_shipping_postcode();
	$current_shipping = WC()->cart->get_shipping_total();
	$has_shipping = $current_shipping > 0;
	?>

	<div class="shipping-calculator mb-4" id="shipping-calculator">
		<h3 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
			<svg class="w-5 h-5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
			</svg>
			<?php esc_html_e( 'Calculate shipping', 'etheme' ); ?>
		</h3>

		<form id="shipping-calculator-form">
			<?php wp_nonce_field( 'etheme_calc_shipping', 'shipping_nonce' ); ?>
			<input type="hidden" name="calc_shipping_country" value="<?php echo esc_attr( $default_country ); ?>" />

			<div class="flex gap-2">
				<input type="text"
					   id="calc_shipping_postcode"
					   name="calc_shipping_postcode"
					   value="<?php echo esc_attr( $current_postcode ); ?>"
					   placeholder="<?php esc_attr_e( 'Postal code', 'etheme' ); ?>"
					   class="flex-1 px-3 py-2.5 border border-gray-300 rounded text-sm text-gray-900 focus:ring-1 focus:ring-gray-900 focus:border-gray-900 focus:outline-none"
					   inputmode="text" />
				<button type="submit"
						id="calc-shipping-btn"
						class="px-4 py-2.5 border border-gray-900 text-gray-900 text-xs font-bold uppercase tracking-wider rounded hover:bg-gray-900 hover:text-white transition">
					<span class="button-text"><?php esc_html_e( 'Calculate', 'etheme' ); ?></span>
					<span class="loading-spinner hidden">
						<svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
							<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
							<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
						</svg>
					</span>
				</button>
			</div>
		</form>

		<!-- Shipping Results -->
		<div id="shipping-results" class="mt-3 <?php echo $has_shipping ? '' : 'hidden'; ?>">
			<div id="shipping-options" class="space-y-2">
				<?php
				$packages = WC()->shipping()->get_packages();
				if ( ! empty( $packages ) ) {
					foreach ( $packages as $package ) {
						if ( ! empty( $package['rates'] ) ) {
							foreach ( $package['rates'] as $rate ) {
								etheme_render_shipping_option( $rate );
							}
						}
					}
				}
				?>
			</div>
		</div>

		<!-- Error Message -->
		<div id="shipping-error" class="hidden mt-3 p-2 bg-red-50 border border-red-200 rounded text-xs text-red-700"></div>
	</div>
	<?php
}

/**
 * Render a single shipping option
 *
 * @param WC_Shipping_Rate $rate Shipping rate object.
 */
function etheme_render_shipping_option( $rate ) {
	$rate_id = $rate->get_id();
	$rate_label = $rate->get_label();
	$rate_cost = $rate->get_cost();
	$is_selected = WC()->session->get( 'chosen_shipping_methods' );
	$checked = is_array( $is_selected ) && in_array( $rate_id, $is_selected, true );
	?>
	<label class="flex items-center justify-between p-2.5 border rounded cursor-pointer hover:border-gray-900 transition text-sm <?php echo $checked ? 'border-gray-900 bg-gray-50' : 'border-gray-200'; ?>">
		<div class="flex items-center">
			<input type="radio"
				   name="shipping_method[0]"
				   value="<?php echo esc_attr( $rate_id ); ?>"
				   class="shipping-method-radio h-3.5 w-3.5 text-gray-900 focus:ring-gray-900"
				   <?php checked( $checked ); ?> />
			<span class="ml-2 text-gray-900"><?php echo esc_html( $rate_label ); ?></span>
		</div>
		<span class="font-medium text-gray-900">
			<?php echo $rate_cost > 0 ? wp_kses_post( wc_price( $rate_cost ) ) : esc_html__( 'Free', 'etheme' ); ?>
		</span>
	</label>
	<?php
}
