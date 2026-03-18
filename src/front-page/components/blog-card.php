<?php
// front-page/blog-card.
/**
 * Blog Card Component — delegates to core/components/blog-card.php
 *
 * The canonical implementation lives in src/core/components/blog-card.php.
 * This file exists so the front-page block's component list is unchanged.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/core/components/blog-card.php';
