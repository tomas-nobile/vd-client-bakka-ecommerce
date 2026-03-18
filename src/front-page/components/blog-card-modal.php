<?php
// front-page/blog-card-modal.
/**
 * Blog Card Modal Component — delegates to core/components/blog-card-modal.php
 *
 * The canonical implementation lives in src/core/components/blog-card-modal.php.
 * The backward-compat alias etheme_render_home_blog_card_modal() is defined here
 * so existing callers (blog.php) continue to work without changes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/core/components/blog-card-modal.php';

/**
 * Backward-compatible alias for etheme_render_blog_card_modal().
 *
 * @deprecated Call etheme_render_blog_card_modal() directly.
 */
if ( ! function_exists( 'etheme_render_home_blog_card_modal' ) ) {
	function etheme_render_home_blog_card_modal() {
		etheme_render_blog_card_modal();
	}
}
