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
	'custom-work',
	'categories',
	'why',
	'reviews',
	'blog-card',
	'blog-card-modal',
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
	'whyEyebrow'          => __( 'Por qué elegirnos', 'etheme' ),
	'whyTitle'            => __( 'Compra con Confianza', 'etheme' ),
	'whyDescription'      => __( 'Nos esforzamos por ofrecer la mejor experiencia de compra con muebles de calidad, precios competitivos y un servicio al cliente excepcional.', 'etheme' ),
	'reviewsCount'        => 6,
	'reviewsOrderBy'      => 'date',
	'blogCount'           => 3,
	'blogCategories'      => array(),
	'blogPostType'        => 'social_post',
	'faqsEyebrow'  => '',
	'faqsTitle'    => __( 'Preguntas frecuentes', 'etheme' ),
	'faqsImageId'  => 0,
);

$attributes = wp_parse_args( $attributes, $defaults );
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	etheme_render_home_hero( $attributes );
	etheme_render_home_popular_products( $attributes );
	etheme_render_home_custom_work();
	etheme_render_home_categories( $attributes );
	etheme_render_home_why( $attributes );
	etheme_render_home_reviews( $attributes );
	etheme_render_home_blog( $attributes );
	etheme_render_home_newsletter( $attributes );
	?>
</div>
