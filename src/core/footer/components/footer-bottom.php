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
	?>
	<div class="etheme-footer-bottom">
		<div class="etheme-footer-container">
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
		</div>
	</div>
	<?php
}
