<?php
/**
 * Shipping address component — unified "Datos y envío" section.
 * Renders all Step 1 fields in a 2-column desktop grid with the spec-defined order:
 *   Row 1: Nombre | Apellido
 *   Row 2: Email | Teléfono (split: código de área + número)
 *   Row 3: Provincia (full width)
 *   Row 4: Código postal (with help icon) | Ciudad
 *   Row 5: Dirección | Apartamento
 *
 * Country is hidden (fixed to $country param, default 'AR').
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
		'input_class' => array( 'w-full', 'border', 'border-gray-300', 'px-4', 'py-3', 'text-gray-900', 'focus:border-gray-900', 'focus:outline-none' ),
	);
}

/**
 * Build field styles that span both columns of .checkout-fields-2col.
 *
 * @return array
 */
function etheme_checkout_get_full_field_styles() {
	return wp_parse_args(
		array( 'class' => array( 'etheme-field', 'form-row-wide', 'etheme-field-full' ) ),
		etheme_checkout_get_address_field_styles()
	);
}

/**
 * Render hidden country input (fixed to $country, not shown to buyer).
 *
 * @param string $country ISO 3166-1 alpha-2 country code.
 * @return void
 */
function etheme_checkout_render_hidden_country( $country ) {
	?>
	<input type="hidden" name="shipping_country" id="shipping_country" value="<?php echo esc_attr( $country ); ?>" />
	<?php
}

/**
 * Build the custom Argentine province options.
 * BA is split into Gran Buenos Aires (allowed) and Interior (not allowed).
 *
 * @return array
 */
function etheme_checkout_get_ar_province_options() {
	return array(
		''            => __( 'Seleccioná tu provincia', 'etheme' ),
		'C'           => __( 'Capital Federal (CABA)', 'etheme' ),
		'BA_GBA'      => __( 'Gran Buenos Aires', 'etheme' ),
		'BA_INTERIOR' => __( 'Interior de la Provincia de Buenos Aires', 'etheme' ),
		'K'           => __( 'Catamarca', 'etheme' ),
		'H'           => __( 'Chaco', 'etheme' ),
		'U'           => __( 'Chubut', 'etheme' ),
		'X'           => __( 'Córdoba', 'etheme' ),
		'W'           => __( 'Corrientes', 'etheme' ),
		'E'           => __( 'Entre Ríos', 'etheme' ),
		'P'           => __( 'Formosa', 'etheme' ),
		'Y'           => __( 'Jujuy', 'etheme' ),
		'L'           => __( 'La Pampa', 'etheme' ),
		'F'           => __( 'La Rioja', 'etheme' ),
		'M'           => __( 'Mendoza', 'etheme' ),
		'N'           => __( 'Misiones', 'etheme' ),
		'Q'           => __( 'Neuquén', 'etheme' ),
		'R'           => __( 'Río Negro', 'etheme' ),
		'A'           => __( 'Salta', 'etheme' ),
		'J'           => __( 'San Juan', 'etheme' ),
		'D'           => __( 'San Luis', 'etheme' ),
		'Z'           => __( 'Santa Cruz', 'etheme' ),
		'S'           => __( 'Santa Fe', 'etheme' ),
		'G'           => __( 'Santiago del Estero', 'etheme' ),
		'V'           => __( 'Tierra del Fuego', 'etheme' ),
		'T'           => __( 'Tucumán', 'etheme' ),
	);
}

/**
 * Render the custom province select (display) + hidden WC state field (shipping or billing).
 * The visible select uses id="checkout-province-display" (managed by checkout-region-guard.js).
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @param string      $context  "shipping" or "billing".
 * @return void
 */
function etheme_checkout_render_province_field( $checkout, $context = 'shipping' ) {
	$context = ( 'billing' === $context ) ? 'billing' : 'shipping';
	$state_key = 'shipping' === $context ? 'shipping_state' : 'billing_state';
	$getter    = 'shipping' === $context ? 'get_shipping_state' : 'get_billing_state';
	$current   = etheme_checkout_get_field_value( $checkout, $state_key, $getter );
	$options   = etheme_checkout_get_ar_province_options();
	?>
	<p class="form-row etheme-field etheme-field-full validate-required" id="checkout_province_display_field">
		<label for="checkout-province-display">
			<?php esc_html_e( 'Provincia', 'etheme' ); ?>
			<abbr class="required" title="<?php esc_attr_e( 'requerido', 'etheme' ); ?>">*</abbr>
		</label>
		<select
			id="checkout-province-display"
			name="checkout_province_display"
			autocomplete="address-level1"
			aria-required="true"
		>
			<?php foreach ( $options as $value => $label ) : ?>
				<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>>
					<?php echo esc_html( $label ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<input type="hidden" name="<?php echo esc_attr( $state_key ); ?>" id="<?php echo esc_attr( $state_key ); ?>" value="<?php echo esc_attr( $current ); ?>" />
		<span class="etheme-field-error" aria-live="polite"></span>
	</p>
	<?php
}

/**
 * Render the split phone field (área + número) with hidden billing_phone composition.
 * JS (billing-sync.js) composes both into the billing-address-sync billing_phone field.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @return void
 */
function etheme_checkout_render_split_phone( $checkout ) {
	$current_phone = etheme_checkout_get_field_value( $checkout, 'billing_phone', 'get_billing_phone' );
	?>
	<p class="form-row etheme-field etheme-field-phone-split validate-required" id="checkout_phone_field">
		<label>
			<?php esc_html_e( 'Teléfono', 'etheme' ); ?>
			<abbr class="required" title="<?php esc_attr_e( 'requerido', 'etheme' ); ?>">*</abbr>
		</label>
		<span class="checkout-phone-inputs">
			<input
				type="tel"
				id="checkout_phone_area"
				name="checkout_phone_area"
				class="input-text"
				placeholder="<?php esc_attr_e( 'Cód. área', 'etheme' ); ?>"
				inputmode="numeric"
				pattern="\d+"
				maxlength="4"
				autocomplete="tel-area-code"
				aria-label="<?php esc_attr_e( 'Código de área telefónico', 'etheme' ); ?>"
				value="<?php echo esc_attr( $current_phone ? substr( $current_phone, 0, 4 ) : '' ); ?>"
			/>
			<input
				type="tel"
				id="checkout_phone_number"
				name="checkout_phone_number"
				class="input-text"
				placeholder="<?php esc_attr_e( 'Número', 'etheme' ); ?>"
				inputmode="numeric"
				pattern="\d+"
				maxlength="8"
				autocomplete="tel-local"
				aria-label="<?php esc_attr_e( 'Número de teléfono', 'etheme' ); ?>"
				value="<?php echo esc_attr( $current_phone ? substr( $current_phone, 4 ) : '' ); ?>"
			/>
		</span>
		<span class="checkout-field-hint"><?php esc_html_e( 'Solo números. Ej: 011 · 45678900', 'etheme' ); ?></span>
		<span class="etheme-field-error" aria-live="polite"></span>
	</p>
	<?php
}

/**
 * Render postcode field with SVG help icon and tooltip.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @param string      $context  "shipping" or "billing".
 * @return void
 */
function etheme_checkout_render_postcode_field( $checkout, $context = 'shipping' ) {
	$context = ( 'billing' === $context ) ? 'billing' : 'shipping';
	$pcode_key = 'shipping' === $context ? 'shipping_postcode' : 'billing_postcode';
	$getter    = 'shipping' === $context ? 'get_shipping_postcode' : 'get_billing_postcode';
	$value     = etheme_checkout_get_field_value( $checkout, $pcode_key, $getter );
	$field     = etheme_checkout_get_field_definition( $checkout, $context, $pcode_key );
	$required  = ! empty( $field['required'] );
	$input_id  = 'shipping' === $context ? 'shipping_postcode' : 'billing_postcode';
	$row_id    = 'shipping' === $context ? 'shipping_postcode_field' : 'billing_postcode_field';
	?>
	<p class="form-row etheme-field validate-required validate-postcode-4" id="<?php echo esc_attr( $row_id ); ?>">
		<label for="<?php echo esc_attr( $input_id ); ?>" class="checkout-postcode-label">
			<?php esc_html_e( 'Código postal', 'etheme' ); ?>
			<?php if ( $required ) : ?>
				<abbr class="required" title="<?php esc_attr_e( 'requerido', 'etheme' ); ?>">*</abbr>
			<?php endif; ?>
			<span class="checkout-postcode-help">
				<button
					type="button"
					class="checkout-postcode-help__btn"
					aria-label="<?php esc_attr_e( 'Ayuda: cómo encontrar tu código postal', 'etheme' ); ?>"
					aria-describedby="checkout-postcode-tooltip"
				>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false">
						<circle cx="10" cy="10" r="8.5" stroke="currentColor" stroke-width="1.4"/>
						<path d="M10 9v5" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
						<circle cx="10" cy="7" r="0.8" fill="currentColor"/>
					</svg>
				</button>
				<span id="checkout-postcode-tooltip" class="checkout-postcode-tooltip" role="tooltip">
					<?php esc_html_e( 'Ingresá tu código postal de 4 dígitos. También podés encontrarlo en la parte trasera de tu DNI o en ', 'etheme' ); ?>
					<a href="https://www.correoargentino.com.ar/formularios/cpa" target="_blank" rel="noopener noreferrer">
						<?php esc_html_e( 'Correo Argentino', 'etheme' ); ?>
					</a>.
				</span>
			</span>
		</label>
		<input
			type="text"
			id="<?php echo esc_attr( $input_id ); ?>"
			name="<?php echo esc_attr( $pcode_key ); ?>"
			class="input-text"
			value="<?php echo esc_attr( $value ); ?>"
			inputmode="numeric"
			maxlength="4"
			autocomplete="postal-code"
			aria-required="<?php echo $required ? 'true' : 'false'; ?>"
		/>
		<span class="etheme-field-error" aria-live="polite"></span>
	</p>
	<?php
}

/**
 * Step 1 fields when the cart does not need a shipping address (virtual / no-shipment).
 * Renders visible billing_* fields so WooCommerce receives a complete billing payload.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @param string      $country  Fixed country code (default 'AR').
 * @return void
 */
function etheme_render_checkout_billing_only_step1( $checkout, $country = 'AR' ) {
	$fs  = etheme_checkout_get_address_field_styles();
	$fsf = etheme_checkout_get_full_field_styles();
	?>
	<section class="border border-gray-200 bg-white p-6 md:p-7" aria-labelledby="checkout-billing-step1" data-aos="fade-up">
		<h2 id="checkout-billing-step1" class="text-xl font-bold text-gray-900" data-step-heading>
			<?php esc_html_e( 'Datos de contacto y facturación', 'etheme' ); ?>
		</h2>

		<input type="hidden" name="billing_country" id="billing_country" value="<?php echo esc_attr( $country ); ?>" />

		<div class="checkout-fields-2col mt-6">
			<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_first_name', $fs, 'get_billing_first_name' ); ?>
			<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_last_name', $fs, 'get_billing_last_name' ); ?>

			<?php
			if ( function_exists( 'etheme_render_checkout_contact_information' ) ) {
				etheme_render_checkout_contact_information( $checkout );
			}
			?>
			<?php etheme_checkout_render_split_phone( $checkout ); ?>

			<?php etheme_checkout_render_province_field( $checkout, 'billing' ); ?>

			<?php etheme_checkout_render_postcode_field( $checkout, 'billing' ); ?>
			<?php
			etheme_checkout_render_field(
				$checkout,
				'billing',
				'billing_city',
				array_merge(
					$fs,
					array(
						'label' => __( 'Ciudad', 'etheme' ),
					)
				),
				'get_billing_city'
			);
			?>

			<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_address_1', $fs, 'get_billing_address_1' ); ?>
			<?php
			etheme_checkout_render_field(
				$checkout,
				'billing',
				'billing_address_2',
				array_merge(
					$fs,
					array(
						'label' => __( 'Apartamento', 'etheme' ),
					)
				),
				'get_billing_address_2'
			);
			?>
		</div>
	</section>
	<?php
}

/**
 * Render the unified "Datos y envío" section with all Step 1 fields.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @param string      $country  Fixed country code (default 'AR').
 * @return void
 */
function etheme_render_checkout_shipping_address( $checkout, $country = 'AR' ) {
	if ( ! WC()->cart->needs_shipping_address() ) {
		etheme_render_checkout_billing_only_step1( $checkout, $country );
		return;
	}

	$fs   = etheme_checkout_get_address_field_styles();
	$fsf  = etheme_checkout_get_full_field_styles();
	?>
	<section class="border border-gray-200 bg-white p-6 md:p-7" aria-labelledby="checkout-shipping-address" data-aos="fade-up">
		<h2 id="checkout-shipping-address" class="text-xl font-bold text-gray-900" data-step-heading>
			<?php esc_html_e( 'Datos y envío', 'etheme' ); ?>
		</h2>

		<?php etheme_checkout_render_hidden_country( $country ); ?>

		<div class="checkout-fields-2col mt-6">
			<?php /* Row 1: Nombre | Apellido */ ?>
			<?php etheme_checkout_render_field( $checkout, 'shipping', 'shipping_first_name', $fs, 'get_shipping_first_name' ); ?>
			<?php etheme_checkout_render_field( $checkout, 'shipping', 'shipping_last_name', $fs, 'get_shipping_last_name' ); ?>

			<?php /* Row 2: Email | Teléfono split */ ?>
			<?php
			if ( function_exists( 'etheme_render_checkout_contact_information' ) ) {
				etheme_render_checkout_contact_information( $checkout );
			}
			?>
			<?php etheme_checkout_render_split_phone( $checkout ); ?>

			<?php /* Row 3: Provincia (full width) */ ?>
			<?php etheme_checkout_render_province_field( $checkout ); ?>

			<?php /* Row 4: Código postal | Ciudad */ ?>
			<?php etheme_checkout_render_postcode_field( $checkout ); ?>
			<?php
			etheme_checkout_render_field(
				$checkout,
				'shipping',
				'shipping_city',
				array_merge(
					$fs,
					array(
						'label' => __( 'Ciudad', 'etheme' ),
					)
				),
				'get_shipping_city'
			);
			?>

			<?php /* Row 5: Dirección | Apartamento */ ?>
			<?php etheme_checkout_render_field( $checkout, 'shipping', 'shipping_address_1', $fs, 'get_shipping_address_1' ); ?>
			<?php
			etheme_checkout_render_field(
				$checkout,
				'shipping',
				'shipping_address_2',
				array_merge(
					$fs,
					array(
						'label' => __( 'Apartamento', 'etheme' ),
					)
				),
				'get_shipping_address_2'
			);
			?>
		</div>
	</section>
	<?php
}
