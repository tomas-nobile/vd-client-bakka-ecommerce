<?php
/**
 * Billing address component - Hidden fields synchronized with shipping.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render billing address fields (hidden, synchronized with shipping).
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @return void
 */
function etheme_render_checkout_billing_address( $checkout ) {
	$field_styles = array(
		'class'       => array( 'etheme-field', 'form-row-wide' ),
		'input_class' => array( 'etheme-billing-sync' ),
	);

	$country_options = WC()->countries->get_countries();
	$base_country    = WC()->countries->get_base_country();
	$country_value   = etheme_checkout_get_field_value( $checkout, 'billing_country', 'get_billing_country' );
	$country_value   = $country_value ? $country_value : $base_country;
	$state_options   = WC()->countries->get_states( $country_value );
	?>
	<!-- Billing address fields - hidden and synchronized with shipping -->
	<div id="billing-address-sync" style="position: absolute; left: -9999px; opacity: 0; pointer-events: none;" aria-hidden="true">
		<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_first_name', $field_styles, 'get_billing_first_name' ); ?>
		<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_last_name', $field_styles, 'get_billing_last_name' ); ?>
		<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_company', $field_styles, 'get_billing_company' ); ?>
		
		<?php
		etheme_checkout_render_field(
			$checkout,
			'billing',
			'billing_country',
			wp_parse_args(
				array(
					'options' => $country_options,
				),
				$field_styles
			),
			'get_billing_country'
		);
		?>
		
		<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_address_1', $field_styles, 'get_billing_address_1' ); ?>
		<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_address_2', $field_styles, 'get_billing_address_2' ); ?>
		<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_city', $field_styles, 'get_billing_city' ); ?>
		
		<?php
		etheme_checkout_render_field(
			$checkout,
			'billing',
			'billing_state',
			wp_parse_args(
				array(
					'options' => is_array( $state_options ) ? $state_options : array(),
				),
				$field_styles
			),
			'get_billing_state'
		);
		?>
		
		<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_postcode', $field_styles, 'get_billing_postcode' ); ?>
		<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_phone', $field_styles, 'get_billing_phone' ); ?>
	</div>
	<?php
}