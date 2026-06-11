<?php
/**
 * Footer bottom bar — dynamic copyright line.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_footer_bottom() {
	$year      = gmdate( 'Y' );
	$site_name = get_bloginfo( 'name' );
	$logo_url  = get_template_directory_uri() . '/assets/images/vitamina-digital-logo.webp';
	?>
	<div class="etheme-footer-bottom">
		<div class="etheme-footer-container etheme-footer-bottom__inner">
			<p class="etheme-footer-copyright">
				<?php
				echo esc_html(
					sprintf(
						/* translators: 1: current year, 2: site name */
						__( '© %1$s %2$s', 'etheme' ),
						$year,
						$site_name
					)
				);
				?>
			</p>
			<p class="etheme-footer-credit">
				<span class="etheme-footer-credit__text"><?php echo esc_html__( 'Creado por', 'etheme' ); ?></span>
				<a class="etheme-footer-credit__link" href="<?php echo esc_url( 'https://vitaminadigital.tech/' ); ?>" target="_blank" rel="noopener nofollow">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr__( 'Vitamina Digital Tech', 'etheme' ); ?>" loading="lazy" />
				</a>
			</p>
		</div>
	</div>
	<?php
}
