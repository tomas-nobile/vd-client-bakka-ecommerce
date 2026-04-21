<?php
/**
 * Checkout helper functions.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get checkout field groups.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @return array
 */
function etheme_checkout_get_field_groups( $checkout ) {
	if ( ! $checkout || ! method_exists( $checkout, 'get_checkout_fields' ) ) {
		return array();
	}

	return $checkout->get_checkout_fields();
}

/**
 * Get field definition by group and key.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @param string      $group    Field group key.
 * @param string      $key      Field key.
 * @return array
 */
function etheme_checkout_get_field_definition( $checkout, $group, $key ) {
	$groups = etheme_checkout_get_field_groups( $checkout );

	if ( empty( $groups[ $group ][ $key ] ) || ! is_array( $groups[ $group ][ $key ] ) ) {
		return array();
	}

	return $groups[ $group ][ $key ];
}

/**
 * Resolve checkout field value with customer fallback.
 *
 * @param WC_Checkout $checkout        Checkout instance.
 * @param string      $field_key       Field key.
 * @param string      $customer_getter Customer getter method.
 * @return string
 */
function etheme_checkout_get_field_value( $checkout, $field_key, $customer_getter = '' ) {
	if ( isset( $_POST[ $field_key ] ) ) {
		return wc_clean( wp_unslash( $_POST[ $field_key ] ) );
	}

	$value = $checkout ? $checkout->get_value( $field_key ) : '';
	if ( '' !== $value ) {
		return $value;
	}

	if ( ! WC()->customer || '' === $customer_getter ) {
		return '';
	}

	return method_exists( WC()->customer, $customer_getter ) ? (string) WC()->customer->{$customer_getter}() : '';
}

/**
 * Render a WooCommerce checkout field with Tailwind-friendly classes.
 *
 * @param WC_Checkout $checkout        Checkout instance.
 * @param string      $group           Field group.
 * @param string      $field_key       Field key.
 * @param array       $overrides       Optional field overrides.
 * @param string      $customer_getter Optional customer getter.
 * @return void
 */
function etheme_checkout_render_field( $checkout, $group, $field_key, $overrides = array(), $customer_getter = '' ) {
	$field_args = etheme_checkout_get_field_definition( $checkout, $group, $field_key );
	if ( empty( $field_args ) ) {
		return;
	}

	$field_args = wp_parse_args( $overrides, $field_args );
	$value      = etheme_checkout_get_field_value( $checkout, $field_key, $customer_getter );

	if ( isset( $overrides['value'] ) ) {
		$value = $overrides['value'];
	}

	woocommerce_form_field( $field_key, $field_args, $value );
}

/**
 * Ensure shipping is calculated so packages have rates (e.g. using store base when no address).
 *
 * @return void
 */
function etheme_checkout_ensure_shipping_rates() {
	if ( ! WC()->cart->needs_shipping() ) {
		return;
	}

	$packages = WC()->shipping()->get_packages();
	$has_rates = false;
	foreach ( $packages as $package ) {
		if ( ! empty( $package['rates'] ) && is_array( $package['rates'] ) ) {
			$has_rates = true;
			break;
		}
	}

	if ( $has_rates ) {
		return;
	}

	$customer = WC()->customer;
	if ( ! $customer ) {
		return;
	}

	$base_country  = WC()->countries->get_base_country();
	$base_state      = WC()->countries->get_base_state();
	$base_postcode   = WC()->countries->get_base_postcode();

	$customer->set_shipping_country( $base_country );
	$customer->set_shipping_state( $base_state );
	$customer->set_shipping_postcode( $base_postcode ? $base_postcode : '' );
	$customer->set_shipping_city( '' );
	$customer->set_shipping_address( '' );
	$customer->set_shipping_address_2( '' );

	WC()->cart->calculate_shipping();
	WC()->cart->calculate_totals();

	// Second pass: many AR installs leave "State" empty in store address — zones then return no rates.
	$packages = WC()->shipping()->get_packages();
	$has_rates_after = false;
	foreach ( $packages as $package ) {
		if ( ! empty( $package['rates'] ) && is_array( $package['rates'] ) ) {
			$has_rates_after = true;
			break;
		}
	}

	if ( $has_rates_after ) {
		return;
	}

	if ( 'AR' === $base_country && ( '' === (string) $base_state || null === $base_state ) ) {
		$fallback_state = apply_filters( 'etheme_checkout_fallback_shipping_state_ar', 'C', $customer );
		$customer->set_shipping_state( is_string( $fallback_state ) ? $fallback_state : 'C' );
		if ( '' === (string) $customer->get_shipping_postcode() ) {
			$customer->set_shipping_postcode( apply_filters( 'etheme_checkout_fallback_shipping_postcode_ar', 'C1425', $customer ) );
		}
		WC()->cart->calculate_shipping();
		WC()->cart->calculate_totals();
	}
}

/**
 * Get all available shipping rates grouped by package index.
 *
 * @return array
 */
function etheme_checkout_get_shipping_rates() {
	etheme_checkout_ensure_shipping_rates();

	$packages = WC()->shipping()->get_packages();
	$rates    = array();

	foreach ( $packages as $package_index => $package ) {
		if ( empty( $package['rates'] ) || ! is_array( $package['rates'] ) ) {
			continue;
		}

		$rates[ $package_index ] = array_values( $package['rates'] );
	}

	return $rates;
}

/**
 * Get chosen shipping methods from session.
 *
 * @return array
 */
function etheme_checkout_get_chosen_shipping_methods() {
	$chosen = WC()->session ? WC()->session->get( 'chosen_shipping_methods', array() ) : array();
	return is_array( $chosen ) ? $chosen : array();
}

/**
 * Get available payment gateways for current checkout.
 *
 * @return array
 */
function etheme_checkout_get_available_gateways() {
	if ( ! WC()->payment_gateways() ) {
		return array();
	}

	return WC()->payment_gateways()->get_available_payment_gateways();
}

/**
 * Map a gateway id to a visual variant.
 *
 * @param string $gateway_id Gateway id.
 * @return string
 */
function etheme_checkout_get_gateway_variant( $gateway_id ) {
	$id = strtolower( (string) $gateway_id );

	if ( false !== strpos( $id, 'mercadopago' ) || false !== strpos( $id, 'mercado_pago' ) ) {
		return 'mercadopago';
	}

	if ( false !== strpos( $id, 'card' ) || false !== strpos( $id, 'stripe' ) ) {
		return 'card';
	}

	return 'default';
}

/**
 * Build short, plain description for a gateway tile.
 *
 * @param WC_Payment_Gateway $gateway Gateway instance.
 * @return string
 */
function etheme_checkout_get_gateway_short_description( $gateway ) {
	$description = trim( wp_strip_all_tags( (string) $gateway->get_description() ) );
	if ( '' !== $description ) {
		return wp_trim_words( $description, 18, '...' );
	}

	return __( 'Secure checkout with encrypted payment processing.', 'etheme' );
}

/**
 * Get selected shipping method label and value.
 *
 * @param array $shipping_rates  Rates grouped by package.
 * @param array $chosen_methods  Chosen rate ids.
 * @return array
 */
function etheme_checkout_get_selected_shipping_rate( $shipping_rates, $chosen_methods ) {
	foreach ( $shipping_rates as $package_index => $package_rates ) {
		$selected_id = isset( $chosen_methods[ $package_index ] ) ? $chosen_methods[ $package_index ] : '';
		foreach ( $package_rates as $rate ) {
			if ( $rate->get_id() === $selected_id ) {
				return array(
					'label' => $rate->get_label(),
					'cost'  => $rate->get_cost(),
				);
			}
		}
	}

	return array(
		'label' => __( 'Shipping', 'etheme' ),
		'cost'  => WC()->cart->get_shipping_total(),
	);
}

/**
 * Resolve terms page URL.
 *
 * @return string
 */
function etheme_checkout_get_terms_url() {
	$terms_url = wc_get_page_permalink( 'terms' );
	return $terms_url ? $terms_url : '#';
}

/**
 * Resolve privacy policy URL.
 *
 * @return string
 */
function etheme_checkout_get_privacy_url() {
	$privacy_url = get_privacy_policy_url();
	return $privacy_url ? $privacy_url : '#';
}

/**
 * URL for “Contáctanos” in region guard (checkout). Prefers página /contacto si existe.
 *
 * @return string
 */
function etheme_checkout_get_region_contact_url() {
	$url = home_url( '/' );
	if ( function_exists( 'etheme_get_theme_page_slug' ) ) {
		$slug = etheme_get_theme_page_slug( 'contacto' );
		$page = $slug ? get_page_by_path( $slug ) : null;
		if ( $page instanceof WP_Post ) {
			$url = get_permalink( $page );
		}
	}
	return apply_filters( 'etheme_checkout_region_contact_url', $url );
}

/**
 * URL de WhatsApp para coordinación fuera de zona. Configurar vía filtro.
 *
 * @return string
 */
function etheme_checkout_get_region_whatsapp_url() {
	return apply_filters(
		'etheme_checkout_region_whatsapp_url',
		'https://wa.me/'
	);
}

/**
 * Botones Contáctanos + WhatsApp (modal o cartel de región).
 *
 * @param string $contact_url URL página de contacto.
 * @param string $wa_url      URL WhatsApp (wa.me).
 * @param string $context     'alert' | 'modal' — clase BEM opcional.
 * @return void
 */
function etheme_checkout_render_region_cta_buttons( $contact_url, $wa_url, $context = 'modal' ) {
	$modifier = 'alert' === $context ? ' checkout-region-cta--alert' : '';
	?>
	<div class="checkout-region-cta<?php echo esc_attr( $modifier ); ?>">
		<a
			class="checkout-region-cta__btn checkout-region-cta__btn--contact"
			href="<?php echo esc_url( $contact_url ); ?>"
		>
			<?php esc_html_e( 'Contáctanos', 'etheme' ); ?>
		</a>
		<a
			class="checkout-region-cta__btn checkout-region-cta__btn--whatsapp"
			href="<?php echo esc_url( $wa_url ); ?>"
			target="_blank"
			rel="noopener noreferrer"
		>
			<svg class="checkout-region-cta__wa-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">
				<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.435 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
			</svg>
			<?php esc_html_e( 'WhatsApp', 'etheme' ); ?>
		</a>
	</div>
	<?php
}
