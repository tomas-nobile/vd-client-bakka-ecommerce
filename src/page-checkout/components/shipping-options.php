<?php
/**
 * Shipping options component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render shipping options section.
 *
 * @return void
 */
function etheme_render_checkout_shipping_options() {
	if ( ! WC()->cart->needs_shipping() ) {
		return;
	}

	$shipping_rates = etheme_checkout_get_shipping_rates();
	$chosen_methods = etheme_checkout_get_chosen_shipping_methods();
	?>
	<section class="border border-gray-200 bg-white p-6 md:p-7" aria-labelledby="checkout-shipping-options">
		<h2 id="checkout-shipping-options" class="text-xl font-bold text-gray-900">
			<?php esc_html_e( 'Método de envío', 'etheme' ); ?>
		</h2>
		<p class="mt-1 text-sm text-gray-500">
			<?php esc_html_e( 'Elegí la opción de entrega que mejor te convenga.', 'etheme' ); ?>
		</p>

		<?php if ( empty( $shipping_rates ) ) : ?>
			<p class="mt-5 border border-dashed border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-600">
				<?php esc_html_e( 'Los métodos de envío aparecerán al ingresar una dirección válida.', 'etheme' ); ?>
			</p>
		<?php else : ?>
			<div class="mt-5 space-y-4">
				<?php foreach ( $shipping_rates as $package_index => $package_rates ) : ?>
					<?php foreach ( $package_rates as $rate ) : ?>
						<?php
						$checked = isset( $chosen_methods[ $package_index ] ) && $chosen_methods[ $package_index ] === $rate->get_id();
						?>
						<label
							class="checkout-shipping-option block cursor-pointer border border-gray-200 p-4 transition hover:border-gray-300 <?php echo $checked ? 'is-selected bg-gray-50' : ''; ?>"
							data-shipping-option
						>
							<div class="flex items-center justify-between gap-3">
								<div class="flex items-center gap-3">
									<input
										type="radio"
										class="shipping_method h-4 w-4 border-gray-300 text-gray-900 focus:outline-none focus:ring-0 focus:ring-offset-0"
										name="shipping_method[<?php echo esc_attr( $package_index ); ?>]"
										value="<?php echo esc_attr( $rate->get_id() ); ?>"
										<?php checked( $checked ); ?>
									/>
									<div>
										<p class="text-sm font-semibold text-gray-900"><?php echo esc_html( $rate->get_label() ); ?></p>
										<p class="text-xs text-gray-500"><?php esc_html_e( 'Entrega estimada según tu dirección.', 'etheme' ); ?></p>
									</div>
								</div>
								<p class="text-sm font-semibold text-gray-900">
									<?php echo $rate->get_cost() > 0 ? wp_kses_post( wc_price( $rate->get_cost() ) ) : esc_html__( 'Gratis', 'etheme' ); ?>
								</p>
							</div>
						</label>
					<?php endforeach; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<p class="sr-only" aria-live="polite"><?php esc_html_e( 'Selección de método de envío actualizada', 'etheme' ); ?></p>
	</section>
	<?php
}
