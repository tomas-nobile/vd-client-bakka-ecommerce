<?php
// information-page.
/**
 * Information Page — block render.
 *
 * Loads config data by pageKey, then renders sub-banner and legal content.
 * Used by three page templates: privacy, terms, commerce-conditions.
 *
 * @param array    $attributes Block attributes (pageKey).
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/information-page/includes/helpers.php';
require_once get_template_directory() . '/src/core/components/sub-banner.php';
require_once get_template_directory() . '/src/information-page/components/info-content.php';

$allowed_keys = array( 'privacy', 'terms', 'commerceConditions' );
$raw_key      = isset( $attributes['pageKey'] ) ? (string) $attributes['pageKey'] : 'privacy';
$page_key     = in_array( $raw_key, $allowed_keys, true ) ? $raw_key : 'privacy';
$data     = etheme_get_legal_page_data( $page_key );
?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => 'information-page-block' ) ); ?>>
	<?php
	$banner_title = isset( $data['title'] ) ? $data['title'] : '';
	$crumb_label = isset( $data['breadcrumbLabel'] ) && '' !== $data['breadcrumbLabel']
		? $data['breadcrumbLabel']
		: $banner_title;

	etheme_render_sub_banner(
		array(
			'title'       => $banner_title,
			'subtitle'    => isset( $data['subtitle'] ) ? $data['subtitle'] : '',
			'breadcrumbs' => array(
				array(
					'label' => __( 'Inicio', 'etheme' ),
					'url'   => home_url( '/' ),
				),
				array(
					'label' => $crumb_label,
				),
			),
		)
	);
	etheme_render_info_content( $data );
	?>
</div>
