<?php
/**
 * Footer navigation — primary menu links (depth 1).
 *
 * Reuses the etheme-primary menu location (same as navbar).
 * Renders nothing if no menu is assigned to avoid empty markup.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_footer_nav() {
	$location = 'etheme-primary';

	if ( ! has_nav_menu( $location ) ) {
		return;
	}
	?>
	<div class="etheme-footer-col etheme-footer-col--nav">
		<span class="etheme-footer-col__title"><?php esc_html_e( 'Navegación', 'etheme' ); ?></span>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => $location,
				'menu_class'     => 'etheme-footer-nav-list',
				'container'      => false,
				'depth'          => 1,
				'fallback_cb'    => false,
				'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
			)
		);
		?>
	</div>
	<?php
}
