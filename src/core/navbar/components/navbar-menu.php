<?php
/**
 * Navbar menu — wp_nav_menu with Etheme_Navbar_Walker.
 *
 * @param array $attributes Block attributes.
 * @param bool  $is_mobile  Whether rendering inside the mobile panel.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_navbar_menu( $attributes, $is_mobile = false ) {
	$location    = isset( $attributes['menuLocation'] ) ? sanitize_text_field( $attributes['menuLocation'] ) : 'etheme-primary';
	$has_menu    = has_nav_menu( $location );
	$wrapper_cls = $is_mobile ? 'etheme-mobile-nav' : 'etheme-navbar-nav';
	?>
	<div class="<?php echo esc_attr( $wrapper_cls ); ?>">
		<?php if ( $has_menu ) : ?>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => $location,
					'menu_class'     => 'etheme-nav-list',
					'container'      => false,
					'walker'         => new Etheme_Navbar_Walker( $is_mobile ),
					'fallback_cb'    => false,
					'items_wrap'     => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>',
				)
			);
			?>
		<?php else : ?>
			<p class="etheme-navbar-nav__placeholder">
				<?php esc_html_e( 'Asigna un menú a "Navegación principal" en Apariencia → Menús.', 'etheme' ); ?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
