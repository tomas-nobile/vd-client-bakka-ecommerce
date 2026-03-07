<?php
/**
 * Order notes component.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render checkout order notes section.
 *
 * @param WC_Checkout $checkout Checkout instance.
 * @return void
 */
function etheme_render_checkout_order_notes( $checkout ) {
	$field = etheme_checkout_get_field_definition( $checkout, 'order', 'order_comments' );
	if ( empty( $field ) ) {
		return;
	}
	?>
	<section class="rounded-2xl border border-gray-200 bg-white p-6 md:p-7" aria-labelledby="checkout-order-notes">
		<h2 id="checkout-order-notes" class="text-xl font-bold text-gray-900">
			<?php esc_html_e( 'Order notes', 'etheme' ); ?>
		</h2>
		<p class="mt-1 text-sm text-gray-500">
			<?php esc_html_e( 'Add a note to your order (optional).', 'etheme' ); ?>
		</p>
		<div class="mt-5">
			<?php
			etheme_checkout_render_field(
				$checkout,
				'order',
				'order_comments',
				array(
					'label'       => __( 'Order notes', 'etheme' ),
					'class'       => array( 'form-row-wide', 'etheme-field' ),
					'label_class' => array( 'mb-2', 'block', 'text-sm', 'font-semibold', 'text-gray-900' ),
					'input_class' => array( 'w-full', 'rounded-md', 'border', 'border-gray-300', 'px-4', 'py-3', 'text-sm', 'text-gray-900', 'focus:border-gray-900', 'focus:outline-none' ),
				)
			);
			?>
		</div>
	</section>
	<?php
}
