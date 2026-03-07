<?php
/**
 * Contact information component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render contact information section.
 *
 * Logged-in users: email is auto-filled and read-only.
 * Guests: email is editable; we show a note that they can create an account after purchase.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @return void
 */
function etheme_render_checkout_contact_information( $checkout ) {
	$is_logged_in = is_user_logged_in();

	$field_overrides = array(
		'label'       => __( 'Email', 'etheme' ),
		'required'    => true,
		'class'       => array( 'form-row-wide', 'etheme-field' ),
		'label_class' => array( 'mb-2', 'block', 'text-sm', 'font-semibold', 'text-gray-900' ),
		'input_class' => array( 'w-full', 'rounded-md', 'border', 'border-gray-300', 'px-4', 'py-3', 'text-sm', 'text-gray-900', 'focus:border-gray-900', 'focus:outline-none' ),
	);

	if ( $is_logged_in ) {
		$field_overrides['custom_attributes'] = array( 'readonly' => 'readonly' );
		$field_overrides['input_class'][]     = 'bg-gray-50 cursor-not-allowed';
		$current_user                        = wp_get_current_user();
		if ( ! empty( $current_user->user_email ) ) {
			$field_overrides['value'] = $current_user->user_email;
		}
	}
	?>
	<section class="rounded-2xl border border-gray-200 bg-white p-6 md:p-7" aria-labelledby="checkout-contact-information">
		<h2 id="checkout-contact-information" class="text-xl font-bold text-gray-900">
			<?php esc_html_e( 'Contact Information', 'etheme' ); ?>
		</h2>
		<p class="mt-1 text-sm text-gray-500">
			<?php esc_html_e( 'We will send order updates and receipt details to this email.', 'etheme' ); ?>
		</p>
		<?php if ( ! $is_logged_in ) : ?>
			<p class="mt-1 text-sm text-gray-500">
				<?php esc_html_e( 'An account will be created for you after completing your purchase to track orders and save your details.', 'etheme' ); ?>
			</p>
		<?php endif; ?>

		<div class="mt-5">
			<?php etheme_checkout_render_field( $checkout, 'billing', 'billing_email', $field_overrides, 'get_billing_email' ); ?>
			<p class="sr-only" aria-live="polite"><?php esc_html_e( 'Email validation status', 'etheme' ); ?></p>
		</div>
	</section>
	<?php
}
