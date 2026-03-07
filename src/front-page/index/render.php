<?php
// front-page-index.
/**
 * Front Page Index - Main orchestrator for the home page
 *
 * Architecture: one main block (etheme/front-page-index) with internal
 * component sections. Each component has its own PHP file and handles
 * its own data queries and rendering. This file orchestrates the loading
 * and calling of each section.
 *
 * Sections: Hero, Popular Products, Categories, Reviews, Blog, Newsletter.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/front-page/includes/front-page-index.helpers.php';

$components_dir = get_template_directory() . '/src/front-page/components/';
$components     = array(
	'hero',
	'popular-products-card',
	'popular-products',
	'categories',
	'reviews',
	'blog-card',
	'blog',
	'newsletter',
);

foreach ( $components as $component ) {
	require_once $components_dir . $component . '.php';
}

$defaults = array(
	'heroTitle'           => __( 'Descubrí Muebles con Estilo para Cada Espacio', 'etheme' ),
	'heroSubtitle'        => __( 'Los Mejores Muebles', 'etheme' ),
	'heroDescription'     => __( 'Transformá tu hogar con nuestra colección exclusiva de muebles diseñados para combinar estilo, confort y funcionalidad.', 'etheme' ),
	'heroCtaText'         => __( 'Comprar Ahora', 'etheme' ),
	'heroCtaUrl'          => '/tienda',
	'heroImageId'         => 0,
	'heroDiscountNumber'  => '50',
	'heroDiscountLabel'   => __( 'OFF', 'etheme' ),
	'heroDiscountSublabel' => __( 'En todos los productos', 'etheme' ),
	'productsOrderBy'     => 'total_sales',
	'productsPerCategory' => 6,
	'categoriesMode'      => 'all',
	'categoriesInclude'   => array(),
	'categoriesExclude'   => array(),
	'reviewsCount'        => 6,
	'reviewsOrderBy'      => 'date',
	'blogCount'           => 3,
	'blogCategories'      => array(),
	'newsletterTitle'     => __( 'Suscripción al Newsletter', 'etheme' ),
	'newsletterSubtitle'  => __( 'Recibí las Últimas Novedades en tu Correo', 'etheme' ),
	'newsletterButtonText' => __( 'Suscribirse', 'etheme' ),
);

$attributes = wp_parse_args( $attributes, $defaults );
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	etheme_render_home_hero( $attributes );
	etheme_render_home_popular_products( $attributes );
	etheme_render_home_categories( $attributes );
	etheme_render_home_reviews( $attributes );
	etheme_render_home_blog( $attributes );
	etheme_render_home_newsletter( $attributes );
	?>
</div>
