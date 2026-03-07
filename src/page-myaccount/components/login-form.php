<?php
/**
 * Login form component.
 *
 * Uses WooCommerce-compatible nonce (woocommerce-login-nonce) and field names
 * so WC_Form_Handler::process_login() handles authentication natively.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the login form.
 *
 * @param string $redirect_url URL to redirect after successful login.
 * @param bool   $show_register Whether to show the "Create account" toggle link.
 * @return void
 */
function etheme_render_login_form( $redirect_url = '', $show_register = true ) {
	if ( empty( $redirect_url ) ) {
		$redirect_url = wc_get_page_permalink( 'myaccount' );
	}
	?>
	<section class="rounded-2xl border border-gray-200 bg-white p-6 md:p-8" aria-labelledby="login-heading">

		<h1 id="login-heading" class="text-2xl font-bold text-gray-900 tracking-tight">
			<?php esc_html_e( 'Sign In', 'etheme' ); ?>
		</h1>
		<p class="mt-2 text-sm text-gray-500">
			<?php esc_html_e( 'Enter your credentials to access your account.', 'etheme' ); ?>
		</p>

		<form method="post" class="mt-6 space-y-5" aria-label="<?php esc_attr_e( 'Login form', 'etheme' ); ?>">

			<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
			<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect_url ); ?>">

			<!-- Username / Email -->
			<div>
				<label for="username" class="mb-2 block text-sm font-semibold text-gray-900">
					<?php esc_html_e( 'Email or username', 'etheme' ); ?>
				</label>
				<input
					type="text"
					name="username"
					id="username"
					class="myaccount-input w-full rounded-lg border border-gray-300 px-4 py-3 text-sm text-gray-900 placeholder-gray-400 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
					placeholder="<?php esc_attr_e( 'your@email.com', 'etheme' ); ?>"
					autocomplete="username"
					required
					value="<?php echo isset( $_POST['username'] ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>"
				>
			</div>

			<!-- Password -->
			<div>
				<div class="mb-2 flex items-center justify-between">
					<label for="password" class="text-sm font-semibold text-gray-900">
						<?php esc_html_e( 'Password', 'etheme' ); ?>
					</label>
					<a
						href="<?php echo esc_url( wp_lostpassword_url( $redirect_url ) ); ?>"
						class="text-sm font-medium text-gray-600 underline decoration-gray-300 underline-offset-2 hover:text-gray-900 hover:decoration-gray-900"
					>
						<?php esc_html_e( 'Forgot password?', 'etheme' ); ?>
					</a>
				</div>
				<div class="relative">
					<input
						type="password"
						name="password"
						id="password"
						class="myaccount-input w-full rounded-lg border border-gray-300 px-4 py-3 pr-12 text-sm text-gray-900 placeholder-gray-400 focus:border-gray-900 focus:outline-none focus:ring-1 focus:ring-gray-900"
						placeholder="<?php esc_attr_e( 'Enter your password', 'etheme' ); ?>"
						autocomplete="current-password"
						required
					>
					<button
						type="button"
						class="myaccount-password-toggle absolute right-3 top-1/2 -translate-y-1/2 flex items-center border-none bg-transparent p-1 text-gray-500 cursor-pointer hover:text-gray-900"
						aria-label="<?php esc_attr_e( 'Toggle password visibility', 'etheme' ); ?>"
						data-toggle-password="password"
					>
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
							<circle cx="12" cy="12" r="3"/>
						</svg>
					</button>
				</div>
			</div>

			<!-- Remember me -->
			<div class="flex items-center gap-2">
				<input
					type="checkbox"
					name="rememberme"
					id="rememberme"
					value="forever"
					class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900"
				>
				<label for="rememberme" class="text-sm text-gray-600">
					<?php esc_html_e( 'Remember me', 'etheme' ); ?>
				</label>
			</div>

			<!-- Submit -->
			<button
				type="submit"
				name="login"
				value="1"
				class="w-full rounded-lg bg-gray-900 px-6 py-3.5 text-sm font-semibold text-white transition-colors hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-gray-900 focus:ring-offset-2"
			>
				<?php esc_html_e( 'Sign In', 'etheme' ); ?>
			</button>

		</form>

	</section>
	<?php
}
