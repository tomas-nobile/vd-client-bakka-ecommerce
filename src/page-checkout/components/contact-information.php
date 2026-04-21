<?php
/**
 * Contact information component — email field only, no section wrapper.
 * Rendered inside shipping-address.php's unified form section.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the billing email field (no section wrapper).
 * Uses the same field styles as address fields so heights are consistent.
 * Adds `etheme-field-full` so it spans both columns of .checkout-fields-2col.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @return void
 */
function etheme_render_checkout_contact_information( $checkout ) {
	$field_styles = etheme_checkout_get_address_field_styles();

	$field_overrides = wp_parse_args(
		array(
			'label'    => __( 'Email', 'etheme' ),
			'required' => true,
			'class'    => array( 'form-row-wide', 'etheme-field' ),
		),
		$field_styles
	);

	if ( is_user_logged_in() ) {
		$field_overrides['custom_attributes'] = array( 'readonly' => 'readonly' );
		$field_overrides['input_class'][]     = 'bg-gray-50 cursor-not-allowed';
		$current_user                         = wp_get_current_user();
		if ( ! empty( $current_user->user_email ) ) {
			$field_overrides['value'] = $current_user->user_email;
		}
	}

	etheme_checkout_render_field( $checkout, 'billing', 'billing_email', $field_overrides, 'get_billing_email' );
}
