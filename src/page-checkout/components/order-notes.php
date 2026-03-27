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
	<section class="border border-gray-200 bg-white p-6 md:p-7" aria-labelledby="checkout-order-notes" data-aos="fade-up" data-aos-delay="150">
		<h2 id="checkout-order-notes" class="text-xl font-bold text-gray-900">
			<?php esc_html_e( 'Notas del pedido', 'etheme' ); ?>
		</h2>

		<div class="mt-5">
			<?php
			etheme_checkout_render_field(
				$checkout,
				'order',
				'order_comments',
				array(
					'label'       => __( '', 'etheme' ),
					'placeholder' => __( 'Notas sobre tu pedido, por ejemplo, notas especiales para la entrega. (opcional)', 'etheme' ),
					'class'       => array( 'form-row-wide', 'etheme-field' ),
					'label_class' => array( 'mb-2', 'block', 'text-sm', 'font-semibold', 'text-gray-900' ),
					'input_class' => array( 'w-full', 'border', 'border-gray-300', 'px-4', 'py-3', 'text-sm', 'text-gray-900', 'focus:border-gray-900', 'focus:outline-none' ),
					'custom_attributes' => array(
						'style' => 'font-size:0.875rem;line-height:1.25rem;',
					),
				)
			);
			?>
		</div>
	</section>
	<?php
}
