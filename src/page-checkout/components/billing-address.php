<?php
/**
 * Billing address component — hidden fields synchronized with shipping by billing-sync.js.
 * All fields rendered as hidden inputs to avoid duplicate selects and Select2 conflicts.
 * country and state are fixed to the value synced from the shipping section.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render billing address fields (all hidden, synchronized with shipping).
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @param string      $country  Fixed country code (default 'AR').
 * @return void
 */
function etheme_render_checkout_billing_address( $checkout, $country = 'AR' ) {
	$cart = WC()->cart;
	if ( $cart && ! $cart->needs_shipping_address() ) {
		?>
		<div id="billing-address-sync" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
			<input type="hidden" name="billing_phone" value="" />
		</div>
		<?php
		return;
	}

	$first_name = etheme_checkout_get_field_value( $checkout, 'billing_first_name', 'get_billing_first_name' );
	$last_name  = etheme_checkout_get_field_value( $checkout, 'billing_last_name', 'get_billing_last_name' );
	$company    = etheme_checkout_get_field_value( $checkout, 'billing_company', 'get_billing_company' );
	$address_1  = etheme_checkout_get_field_value( $checkout, 'billing_address_1', 'get_billing_address_1' );
	$address_2  = etheme_checkout_get_field_value( $checkout, 'billing_address_2', 'get_billing_address_2' );
	$city       = etheme_checkout_get_field_value( $checkout, 'billing_city', 'get_billing_city' );
	$state      = etheme_checkout_get_field_value( $checkout, 'billing_state', 'get_billing_state' );
	$postcode   = etheme_checkout_get_field_value( $checkout, 'billing_postcode', 'get_billing_postcode' );
	?>
	<div id="billing-address-sync" style="position:absolute;left:-9999px;opacity:0;pointer-events:none;" aria-hidden="true">
		<input type="hidden" name="billing_first_name" value="<?php echo esc_attr( $first_name ); ?>" />
		<input type="hidden" name="billing_last_name"  value="<?php echo esc_attr( $last_name ); ?>" />
		<input type="hidden" name="billing_company"    value="<?php echo esc_attr( $company ); ?>" />
		<input type="hidden" name="billing_country"    value="<?php echo esc_attr( $country ); ?>" />
		<input type="hidden" name="billing_address_1"  value="<?php echo esc_attr( $address_1 ); ?>" />
		<input type="hidden" name="billing_address_2"  value="<?php echo esc_attr( $address_2 ); ?>" />
		<input type="hidden" name="billing_city"       value="<?php echo esc_attr( $city ); ?>" />
		<input type="hidden" name="billing_state"      value="<?php echo esc_attr( $state ); ?>" />
		<input type="hidden" name="billing_postcode"   value="<?php echo esc_attr( $postcode ); ?>" />
		<input type="hidden" name="billing_phone"      value="" />
	</div>
	<?php
}
