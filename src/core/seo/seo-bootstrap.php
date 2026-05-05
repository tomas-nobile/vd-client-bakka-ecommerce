<?php
/**
 * SEO module bootstrap.
 *
 * Loads submodules and registers their hooks. Single entry point required from
 * functions.php. Each submodule exposes its own register() function.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/seo-detector.php';
require_once __DIR__ . '/seo-utils.php';
require_once __DIR__ . '/preconnect.php';
require_once __DIR__ . '/image-optimization.php';
require_once __DIR__ . '/schema-product.php';
require_once __DIR__ . '/schema-faq.php';

etheme_seo_preconnect_register();
etheme_seo_image_optimization_register();
etheme_seo_schema_product_register();
etheme_seo_schema_faq_register();
