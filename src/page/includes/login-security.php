<?php
/**
 * Server-side login hardening.
 *
 * - Honeypot field injected via woocommerce_login_form action.
 * - Honeypot validated before wp_authenticate_user succeeds.
 * - IP-based rate limiting: 5 failures per IP block for 5 minutes.
 * - Failed attempt counter reset on successful login.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── Honeypot ─────────────────────────────────────────────────────────────────

/**
 * Inject a hidden honeypot field inside the WooCommerce login form.
 * The field is visually hidden and has tabindex=-1 so real users never fill it.
 * PHP injects it so it exists even when JS is disabled.
 */
add_action( 'woocommerce_login_form', 'etheme_login_honeypot_field' );
function etheme_login_honeypot_field() {
	?>
	<div style="display:none!important;visibility:hidden;position:absolute;left:-9999px" aria-hidden="true">
		<input
			type="text"
			name="etheme_hp_website"
			tabindex="-1"
			autocomplete="nope"
			value=""
		/>
	</div>
	<?php
}

/**
 * Reject login if the honeypot field is filled in.
 *
 * Runs at priority 10, before rate-limit check (priority 20).
 *
 * @param WP_User|WP_Error $user
 * @param string           $password
 * @return WP_User|WP_Error
 */
add_filter( 'wp_authenticate_user', 'etheme_login_check_honeypot', 10, 2 );
function etheme_login_check_honeypot( $user, $password ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if ( ! empty( $_POST['etheme_hp_website'] ) ) {
		return new WP_Error(
			'honeypot_triggered',
			esc_html__( 'No se pudo completar el inicio de sesión.', 'etheme' )
		);
	}
	return $user;
}

// ── Rate limiting ─────────────────────────────────────────────────────────────

/**
 * Block login attempt if the IP has failed too many times recently.
 *
 * Threshold: 5 failures → blocked for 5 minutes.
 * Runs at priority 20 (after honeypot check).
 *
 * @param WP_User|WP_Error $user
 * @param string           $password
 * @return WP_User|WP_Error
 */
add_filter( 'wp_authenticate_user', 'etheme_login_rate_limit_check', 20, 2 );
function etheme_login_rate_limit_check( $user, $password ) {
	if ( is_wp_error( $user ) ) {
		return $user;
	}

	$ip  = etheme_login_get_ip();
	$key = 'etheme_login_fails_' . md5( $ip );

	if ( (int) get_transient( $key ) >= 5 ) {
		return new WP_Error(
			'too_many_attempts',
			esc_html__( 'Demasiados intentos fallidos. Esperá 5 minutos antes de intentar nuevamente.', 'etheme' )
		);
	}

	return $user;
}

/**
 * Increment the failure counter for this IP on a failed login attempt.
 *
 * @param string $username The username/email that failed.
 */
add_action( 'wp_login_failed', 'etheme_login_increment_failures' );
function etheme_login_increment_failures( $username ) {
	$ip  = etheme_login_get_ip();
	$key = 'etheme_login_fails_' . md5( $ip );

	$attempts = (int) get_transient( $key );
	set_transient( $key, $attempts + 1, 5 * MINUTE_IN_SECONDS );
}

/**
 * Clear the failure counter for this IP after a successful login.
 *
 * @param string  $user_login
 * @param WP_User $user
 */
add_action( 'wp_login', 'etheme_login_reset_failures', 10, 2 );
function etheme_login_reset_failures( $user_login, $user ) {
	$ip  = etheme_login_get_ip();
	$key = 'etheme_login_fails_' . md5( $ip );
	delete_transient( $key );
}

// ── Helper ────────────────────────────────────────────────────────────────────

/**
 * Return a sanitized client IP address.
 *
 * @return string
 */
function etheme_login_get_ip() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] )
		? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) )
		: '';
	return $ip;
}
