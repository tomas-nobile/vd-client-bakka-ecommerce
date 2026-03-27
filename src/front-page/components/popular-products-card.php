<?php
// popular-products-card.
// Backward-compat wrapper — carga el componente canónico core.
// La definición real de etheme_render_home_popular_product_card y helpers vive en:
//   src/core/components/product-card.php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/core/components/product-card.php';
