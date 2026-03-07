<?php
/**
 * Register form component (email-only) — rendered as a modal.
 *
 * Uses WooCommerce-compatible nonce (woocommerce-register-nonce) and field names
 * so WC_Form_Handler::process_registration() handles account creation natively.
 *
 * WooCommerce setting "Send password setup link" is enabled, so:
 * - No password field is shown.
 * - After registration, WooCommerce sends an email with a password setup link.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the registration modal (email only).
 *
 * @param string $redirect_url URL to redirect after successful registration.
 * @return void
 */
function etheme_render_register_form( $redirect_url = '' ) {
	if ( empty( $redirect_url ) ) {
		$redirect_url = wc_get_page_permalink( 'myaccount' );
	}
	?>
	<!-- Modal overlay -->
	<div
		id="myaccount-register-modal"
		class="myaccount-modal-overlay"
		role="dialog"
		aria-modal="true"
		aria-labelledby="register-heading"
	>
		<!-- Backdrop -->
		<div class="myaccount-modal-backdrop"></div>

		<!-- Panel -->
		<div class="myaccount-modal-panel">

			<!-- Close -->
			<button type="button" class="myaccount-modal-close" aria-label="<?php esc_attr_e( 'Close', 'etheme' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<line x1="18" y1="6" x2="6" y2="18"/>
					<line x1="6" y1="6" x2="18" y2="18"/>
				</svg>
			</button>

			<!-- Header -->
			<div class="mb-6">
				<h2 id="register-heading" class="text-xl font-bold text-gray-900 tracking-tight">
					<?php esc_html_e( 'Create your account', 'etheme' ); ?>
				</h2>
				<p class="mt-2 text-sm text-gray-500 leading-relaxed">
					<?php esc_html_e( 'We only need your email. We\'ll send you a secure link to set your password.', 'etheme' ); ?>
				</p>
			</div>

			<!-- Steps -->
			<div class="mb-6 space-y-3">
				<?php
				$steps = array(
					array(
						'num'  => '1',
						'text' => __( 'Enter your email', 'etheme' ),
					),
					array(
						'num'  => '2',
						'text' => __( 'Check your inbox', 'etheme' ),
					),
					array(
						'num'  => '3',
						'text' => __( 'Set your password from the link', 'etheme' ),
					),
				);
				foreach ( $steps as $step ) :
					?>
					<div class="flex items-center gap-3">
						<span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-100 text-xs font-bold text-gray-600">
							<?php echo esc_html( $step['num'] ); ?>
						</span>
						<span class="text-sm text-gray-600">
							<?php echo esc_html( $step['text'] ); ?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Form -->
			<form method="post" class="space-y-4" aria-label="<?php esc_attr_e( 'Registration form', 'etheme' ); ?>">

				<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
				<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect_url ); ?>">

				<!-- Email -->
				<div>
					<label for="reg_email" class="mb-2 block text-sm font-semibold text-gray-900">
						<?php esc_html_e( 'Email', 'etheme' ); ?>
					</label>
					<input
						type="email"
						name="email"
						id="reg_email"
						class="myaccount-input w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
						placeholder="<?php esc_attr_e( 'tu@email.com', 'etheme' ); ?>"
						autocomplete="email"
						required
						value="<?php echo isset( $_POST['email'] ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>"
					>
				</div>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<!-- Submit -->
				<button
					type="submit"
					name="register"
					value="1"
					class="w-full rounded-lg bg-gray-900 px-6 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2"
				>
					<?php esc_html_e( 'Create account', 'etheme' ); ?>
				</button>

			</form>

		</div>
	</div>
	<?php
}
