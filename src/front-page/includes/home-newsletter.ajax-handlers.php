<?php
// home-newsletter.
/**
 * Newsletter AJAX Handlers
 *
 * Stores subscriber emails in a custom DB table: {prefix}etheme_newsletter.
 * Extension point: apply_filters('etheme_newsletter_after_subscribe', $email)
 * allows hooking external providers (Mailchimp, etc.) in the future.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_register_newsletter_ajax() {
	add_action( 'wp_ajax_etheme_newsletter_subscribe', 'etheme_ajax_newsletter_subscribe' );
	add_action( 'wp_ajax_nopriv_etheme_newsletter_subscribe', 'etheme_ajax_newsletter_subscribe' );
}
add_action( 'init', 'etheme_register_newsletter_ajax' );

function etheme_ajax_newsletter_subscribe() {
	check_ajax_referer( 'etheme_newsletter_nonce', 'nonce' );

	$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array(
			'message' => __( 'Por favor, ingresá un email válido.', 'etheme' ),
		) );
	}

	global $wpdb;
	$table = $wpdb->prefix . 'etheme_newsletter';

	$exists = $wpdb->get_var(
		$wpdb->prepare( "SELECT id FROM {$table} WHERE email = %s", $email )
	);

	if ( $exists ) {
		wp_send_json_error( array(
			'message' => __( 'Este email ya está suscripto.', 'etheme' ),
		) );
	}

	$wpdb->insert( $table, array(
		'email'      => $email,
		'created_at' => current_time( 'mysql' ),
		'status'     => 'active',
	), array( '%s', '%s', '%s' ) );

	if ( $wpdb->insert_id ) {
		/**
		 * Hook for external newsletter providers (Mailchimp, etc.).
		 * To integrate: add_action('etheme_newsletter_after_subscribe', function($email) { ... });
		 */
		do_action( 'etheme_newsletter_after_subscribe', $email );

		wp_send_json_success( array(
			'message' => __( '¡Gracias por suscribirte!', 'etheme' ),
		) );
	}

	wp_send_json_error( array(
		'message' => __( 'Ocurrió un error. Intentá de nuevo.', 'etheme' ),
	) );
}

/**
 * Create the newsletter table on theme activation.
 */
function etheme_create_newsletter_table() {
	global $wpdb;
	$table   = $wpdb->prefix . 'etheme_newsletter';
	$charset = $wpdb->get_charset_collate();

	$sql = "CREATE TABLE IF NOT EXISTS {$table} (
		id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		email varchar(255) NOT NULL,
		status varchar(20) NOT NULL DEFAULT 'active',
		created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY (id),
		UNIQUE KEY email (email)
	) {$charset};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );
}
add_action( 'after_switch_theme', 'etheme_create_newsletter_table' );

/**
 * Ensure table exists (runs once using an option flag).
 */
function etheme_maybe_create_newsletter_table() {
	if ( get_option( 'etheme_newsletter_table_created' ) ) {
		return;
	}
	etheme_create_newsletter_table();
	update_option( 'etheme_newsletter_table_created', '1' );
}
add_action( 'init', 'etheme_maybe_create_newsletter_table' );
