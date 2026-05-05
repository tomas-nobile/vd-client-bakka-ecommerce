<?php
/**
 * Block WordPress's default user enumeration vectors.
 *
 * Prevents leaking usernames (which feed targeted phishing and brute-force) via:
 *  - REST API: /wp-json/wp/v2/users (and /users/{id})
 *  - Author archives: /?author=N → /author/{username}/
 *  - Sitemap: /wp-sitemap-users-*.xml
 *  - oEmbed: author_name / author_url in the response
 *  - Login errors that distinguish "user not found" from "wrong password"
 *
 * All endpoints remain available to logged-in users with the `list_users`
 * capability so the block editor and admin tooling keep working.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ── REST API: /wp/v2/users ────────────────────────────────────────────────────

/**
 * Hide the /wp/v2/users endpoint from unauthenticated requests.
 */
add_filter( 'rest_endpoints', 'etheme_block_rest_users_endpoint' );
function etheme_block_rest_users_endpoint( $endpoints ) {
	if ( current_user_can( 'list_users' ) ) {
		return $endpoints;
	}

	if ( isset( $endpoints['/wp/v2/users'] ) ) {
		unset( $endpoints['/wp/v2/users'] );
	}
	if ( isset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] ) ) {
		unset( $endpoints['/wp/v2/users/(?P<id>[\d]+)'] );
	}
	return $endpoints;
}

// ── Author archive enumeration: /?author=N ────────────────────────────────────

/**
 * Turn /?author=N into a 404 before WP redirects to /author/{username}/.
 */
add_action( 'template_redirect', 'etheme_block_author_query_enumeration' );
function etheme_block_author_query_enumeration() {
	if ( is_admin() ) {
		return;
	}
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['author'] ) ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}

/**
 * Block the rewritten /author/{username}/ archive too.
 */
add_action( 'template_redirect', 'etheme_block_author_archive' );
function etheme_block_author_archive() {
	if ( is_author() ) {
		global $wp_query;
		$wp_query->set_404();
		status_header( 404 );
		nocache_headers();
	}
}

// ── Sitemap: drop the users provider ──────────────────────────────────────────

add_filter( 'wp_sitemaps_add_provider', 'etheme_remove_users_sitemap_provider', 10, 2 );
function etheme_remove_users_sitemap_provider( $provider, $name ) {
	if ( 'users' === $name ) {
		return false;
	}
	return $provider;
}

// ── oEmbed: strip author identity from the response ───────────────────────────

add_filter( 'oembed_response_data', 'etheme_strip_oembed_author' );
function etheme_strip_oembed_author( $data ) {
	unset( $data['author_name'], $data['author_url'] );
	return $data;
}

// ── Login: uniform error messages ─────────────────────────────────────────────

/**
 * Replace WP's "invalid_username" / "incorrect_password" with a single message
 * so attackers can't tell whether a username exists.
 */
add_filter( 'login_errors', 'etheme_uniform_login_error' );
function etheme_uniform_login_error( $error ) {
	global $errors;

	if ( ! is_wp_error( $errors ) ) {
		return $error;
	}

	$leaky_codes = array( 'invalid_username', 'invalid_email', 'incorrect_password' );
	foreach ( $leaky_codes as $code ) {
		if ( in_array( $code, $errors->get_error_codes(), true ) ) {
			return esc_html__( 'Las credenciales son incorrectas.', 'etheme' );
		}
	}

	return $error;
}
