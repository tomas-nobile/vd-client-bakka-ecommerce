<?php
/**
 * Order notes component — collapsible chip using native <details>/<summary>.
 * No JS required; accessible out of the box.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the order notes field overrides (textarea).
 *
 * @return array
 */
function etheme_checkout_get_order_notes_field_args() {
	return array(
		'label'       => __( '', 'etheme' ),
		'placeholder' => __( 'Notas especiales para la entrega (opcional).', 'etheme' ),
		'class'       => array( 'form-row-wide', 'etheme-field' ),
		'label_class' => array( 'sr-only' ),
		'input_class' => array( 'w-full', 'border', 'border-gray-300', 'px-4', 'py-3', 'text-sm', 'text-gray-900', 'focus:border-gray-900', 'focus:outline-none' ),
		'custom_attributes' => array(
			'style' => 'font-size:0.875rem;line-height:1.25rem;',
			'rows'  => '3',
		),
	);
}

/**
 * Render checkout order notes as a collapsible chip.
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
	<details class="checkout-note-toggle" data-aos="fade-up" data-aos-delay="150">
		<summary class="checkout-note-toggle__trigger">
			<svg class="checkout-note-toggle__icon" viewBox="0 0 20 20" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
				<path d="M13.586 3.586a2 2 0 112.828 2.828L7.5 15.328 4 16l.672-3.5 8.914-8.914z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
			</svg>
			<?php esc_html_e( 'Agregar nota al pedido', 'etheme' ); ?>
		</summary>
		<div class="checkout-note-toggle__body">
			<?php
			etheme_checkout_render_field(
				$checkout,
				'order',
				'order_comments',
				etheme_checkout_get_order_notes_field_args()
			);
			?>
		</div>
	</details>
	<?php
}
