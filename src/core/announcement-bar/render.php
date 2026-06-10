<?php
/**
 * Announcement bar — orchestrator block.
 *
 * Loads the content component and outputs the top bar with a scrolling
 * message and a close button. Rendered above the navbar.
 *
 * @param array    $attributes Block attributes (message, bgColor).
 * @param string   $content    Block content (unused — SSR).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/core/announcement-bar/components/announcement-bar-content.php';

etheme_render_announcement_bar( $attributes );
