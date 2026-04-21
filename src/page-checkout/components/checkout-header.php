<?php
/**
 * Checkout — indicador de pasos (2 pasos). Sin título/subtítulo: eso vive en el sub-banner.
 * Visual harmonizado con Contrive: secondary active, accent completed, border inactive.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render step number badge HTML with BEM state modifier.
 * JS updates .is-step-active / .is-step-completed via checkout-stepper.js.
 *
 * @param int    $step       Step number.
 * @param string $base_class Initial state class (is-step-active or empty).
 * @return string
 */
function etheme_checkout_step_number_html( $step, $base_class ) {
	return sprintf(
		'<span class="checkout-step-number %s">%d</span>',
		esc_attr( $base_class ),
		absint( $step )
	);
}

/**
 * Render 2-step progress controls (Datos y envío / Pago).
 *
 * @return void
 */
function etheme_render_checkout_header() {
	?>
	<div class="checkout-steps-bar mb-8" data-aos="fade-up">
		<nav class="flex justify-center" aria-label="<?php esc_attr_e( 'Progreso del checkout', 'etheme' ); ?>">
			<ol class="checkout-steps flex items-center justify-center">
				<li class="checkout-step-item flex items-center">
					<button
						type="button"
						class="checkout-step-btn flex items-center gap-2 py-2 pr-4"
						aria-current="step"
						data-step-trigger="1"
					>
						<?php echo etheme_checkout_step_number_html( 1, 'is-step-active' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="hidden sm:inline"><?php esc_html_e( 'Datos y envío', 'etheme' ); ?></span>
					</button>
				</li>

				<li class="checkout-step-connector mx-3" aria-hidden="true"></li>

				<li class="checkout-step-item flex items-center">
					<button
						type="button"
						class="checkout-step-btn flex items-center gap-2 py-2 pl-0"
						aria-disabled="true"
						disabled
						data-step-trigger="2"
					>
						<?php echo etheme_checkout_step_number_html( 2, '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="hidden sm:inline"><?php esc_html_e( 'Pago', 'etheme' ); ?></span>
					</button>
				</li>
			</ol>
		</nav>
	</div>
	<?php
}
