<?php
/**
 * Checkout — indicador de pasos (2 pasos). Sin título/subtítulo: eso vive en el sub-banner.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render step number badge HTML.
 *
 * @param int    $step        Step number.
 * @param string $state_class State-specific classes (active, completed, or inactive).
 * @return string
 */
function etheme_checkout_step_number_html( $step, $state_class ) {
	return sprintf(
		'<span class="checkout-step-number flex h-7 w-7 flex-shrink-0 items-center justify-center border-2 text-xs font-bold %s">%d</span>',
		esc_attr( $state_class ),
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
	<div class="checkout-steps-bar mb-8 border-b border-gray-200 pb-6" data-aos="fade-up">
		<nav class="flex justify-center" aria-label="<?php esc_attr_e( 'Progreso del checkout', 'etheme' ); ?>">
			<ol class="checkout-steps flex items-center justify-center">
				<li class="checkout-step-item flex items-center">
					<button
						type="button"
						class="checkout-step-btn flex items-center gap-2 py-2 pr-4 text-sm font-semibold text-gray-900"
						aria-current="step"
						data-step-trigger="1"
					>
						<?php echo etheme_checkout_step_number_html( 1, 'border-gray-900 bg-gray-900 text-white' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="hidden sm:inline"><?php esc_html_e( 'Datos y envío', 'etheme' ); ?></span>
					</button>
				</li>

				<li class="checkout-step-connector mx-3 h-px w-8 flex-shrink-0 bg-gray-300" aria-hidden="true"></li>

				<li class="checkout-step-item flex items-center">
					<button
						type="button"
						class="checkout-step-btn flex items-center gap-2 py-2 pl-0 text-sm font-semibold text-gray-400"
						aria-disabled="true"
						disabled
						data-step-trigger="2"
					>
						<?php echo etheme_checkout_step_number_html( 2, 'border-gray-300 text-gray-400' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<span class="hidden sm:inline"><?php esc_html_e( 'Pago', 'etheme' ); ?></span>
					</button>
				</li>
			</ol>
		</nav>
	</div>
	<?php
}
