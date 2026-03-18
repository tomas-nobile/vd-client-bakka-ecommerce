<?php
// page-posteos/ajax-handlers.
/**
 * AJAX handlers for the /posteos "Mostrar más" load-more button.
 *
 * Registered via functions.php (both authenticated and public requests).
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_ajax_etheme_posteos_load_more', 'etheme_posteos_load_more_handler' );
add_action( 'wp_ajax_nopriv_etheme_posteos_load_more', 'etheme_posteos_load_more_handler' );

/**
 * Handle "Mostrar más" AJAX request.
 *
 * Expected POST params:
 *   - nonce  (string) WordPress nonce.
 *   - page   (int)    The page number to load (1-indexed; initial render is page 1).
 *
 * Returns JSON: { html: string, has_more: bool }
 */
function etheme_posteos_load_more_handler() {
	check_ajax_referer( 'etheme_posteos_load_more', 'nonce' );

	$page     = max( 1, absint( isset( $_POST['page'] ) ? $_POST['page'] : 1 ) );
	$per_page = 15;
	$offset   = ( $page - 1 ) * $per_page;

	require_once get_template_directory() . '/src/core/includes/social-posts.helpers.php';
	require_once get_template_directory() . '/src/core/components/blog-card.php';

	$posts = etheme_get_social_posts( $per_page, $offset );

	if ( empty( $posts ) ) {
		wp_send_json_success( array(
			'html'     => '',
			'has_more' => false,
		) );
		return;
	}

	ob_start();
	foreach ( $posts as $post ) {
		echo '<div class="article-card-col">';
		etheme_render_home_blog_card( $post );
		echo '</div>';
	}
	$html = ob_get_clean();

	// Determine if there are more posts after this batch.
	$total_query = new WP_Query( array(
		'post_type'      => 'social_post',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'post_status'    => 'publish',
		'no_found_rows'  => false,
	) );
	$total    = $total_query->found_posts;
	$has_more = ( $page * $per_page ) < $total;

	wp_send_json_success( array(
		'html'     => $html,
		'has_more' => $has_more,
	) );
}
