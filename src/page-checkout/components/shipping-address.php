<?php
/**
 * Shipping address component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Build default field classes for checkout address fields (square design).
 *
 * @return array
 */
function etheme_checkout_get_address_field_styles() {
	return array(
		'class'       => array( 'etheme-field', 'form-row-wide' ),
		'label_class' => array( 'mb-2', 'block', 'text-sm', 'font-semibold', 'text-gray-900' ),
		'input_class' => array( 'w-full', 'border', 'border-gray-300', 'px-4', 'py-3', 'text-sm', 'text-gray-900', 'focus:border-gray-900', 'focus:outline-none' ),
	);
}

/**
 * Render shipping address section.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @return void
 */
function etheme_render_checkout_shipping_address( $checkout ) {
	if ( ! WC()->cart->needs_shipping_address() ) {
		return;
	}

	$field_styles    = etheme_checkout_get_address_field_styles();
	$country_options = WC()->countries->get_countries();
	$base_country    = WC()->countries->get_base_country();
	$country_value   = etheme_checkout_get_field_value( $checkout, 'shipping_country', 'get_shipping_country' );
	$country_value   = $country_value ? $country_value : $base_country;
	$state_options   = WC()->countries->get_states( $country_value );
	?>
	<section class="border border-gray-200 bg-white p-6 md:p-7" aria-labelledby="checkout-shipping-address" data-aos="fade-up" data-aos-delay="50">
		<div>
			<h2 id="checkout-shipping-address" class="text-xl font-bold text-gray-900">
				<?php esc_html_e( 'Dirección de envío', 'etheme' ); ?>
			</h2>
			<p class="mt-1 text-sm text-gray-500">
				<?php esc_html_e( 'Usaremos estos datos para calcular el envío e impuestos.', 'etheme' ); ?>
			</p>
		</div>

		<div class="mt-6 grid gap-4 md:grid-cols-2">
			<?php etheme_checkout_render_field( $checkout, 'shipping', 'shipping_first_name', $field_styles, 'get_shipping_first_name' ); ?>
			<?php etheme_checkout_render_field( $checkout, 'shipping', 'shipping_last_name', $field_styles, 'get_shipping_last_name' ); ?>
		</div>

		<div class="mt-4">
			<?php
			etheme_checkout_render_field(
				$checkout,
				'shipping',
				'shipping_country',
				wp_parse_args( array( 'options' => $country_options ), $field_styles ),
				'get_shipping_country'
			);
			?>
		</div>

		<div class="mt-4">
			<?php etheme_checkout_render_field( $checkout, 'shipping', 'shipping_address_1', $field_styles, 'get_shipping_address_1' ); ?>
		</div>

		<div class="mt-4">
			<?php etheme_checkout_render_field( $checkout, 'shipping', 'shipping_address_2', $field_styles, 'get_shipping_address_2' ); ?>
		</div>

		<div class="mt-4 grid gap-4 md:grid-cols-2">
			<?php etheme_checkout_render_field( $checkout, 'shipping', 'shipping_city', $field_styles, 'get_shipping_city' ); ?>
			<?php
			etheme_checkout_render_field(
				$checkout,
				'shipping',
				'shipping_state',
				wp_parse_args(
					array( 'options' => is_array( $state_options ) ? $state_options : array() ),
					$field_styles
				),
				'get_shipping_state'
			);
			?>
		</div>

		<div class="mt-4 grid gap-4 md:grid-cols-2">
			<?php etheme_checkout_render_field( $checkout, 'shipping', 'shipping_postcode', $field_styles, 'get_shipping_postcode' ); ?>
			<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_phone', $field_styles, 'get_billing_phone' ); ?>
		</div>
	</section>
	<?php
}
