<?php
/**
 * Navbar brand — logo.
 *
 * Uses WP custom logo when set; falls back to assets/images/logo.webp;
 * final fallback is the site name as text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_navbar_brand() {
	$home_url = esc_url( home_url( '/' ) );
	?>
	<div class="etheme-navbar-brand">
		<?php if ( has_custom_logo() ) : ?>
			<?php echo get_custom_logo(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		<?php else : ?>
			<?php
			$logo_path = get_template_directory() . '/assets/images/logo.webp';
			if ( file_exists( $logo_path ) ) :
				?>
				<a href="<?php echo $home_url; ?>" rel="home" class="etheme-navbar-brand__link">
					<img
						src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo.webp' ) ); ?>"
						alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
						class="etheme-navbar-brand__img"
						loading="eager"
					/>
				</a>
			<?php else : ?>
				<a href="<?php echo $home_url; ?>" rel="home" class="etheme-navbar-brand__link etheme-navbar-brand__link--text">
					<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
				</a>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<?php
}
