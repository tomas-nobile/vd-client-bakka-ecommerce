<?php
/**
 * Footer — orchestrator block.
 *
 * Loads component renderers and outputs the full footer markup:
 * brand (logo + social) · navigation · legal links · copyright bar.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content (unused — SSR).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$footer_dir = get_template_directory() . '/src/core/footer/';

foreach ( array( 'footer-brand', 'footer-nav', 'footer-legal', 'footer-bottom' ) as $component ) {
	require_once $footer_dir . 'components/' . $component . '.php';
}
?>

<footer <?php echo get_block_wrapper_attributes( array( 'class' => 'etheme-footer' ) ); ?>>
	<div class="etheme-footer-container">
		<div class="etheme-footer-columns">
			<?php etheme_render_footer_brand(); ?>
			<?php etheme_render_footer_nav(); ?>
			<?php etheme_render_footer_legal(); ?>
		</div>
	</div>
	<?php etheme_render_footer_bottom(); ?>
</footer>
