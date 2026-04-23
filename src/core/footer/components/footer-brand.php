<?php
/**
 * Footer brand — logo + WhatsApp and Instagram social links.
 *
 * Uses the same logo resolution strategy as the navbar brand:
 * custom_logo → assets/images/logo_sinfondo.webp → site name text.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_footer_brand() {
	$config   = etheme_get_core_config();
	$social   = isset( $config['social'] ) ? $config['social'] : array();
	$wa_url   = ! empty( $social['whatsapp']['url'] ) ? $social['whatsapp']['url'] : '';
	$ig_url   = ! empty( $social['instagram']['url'] ) ? $social['instagram']['url'] : '';
	$home_url = esc_url( home_url( '/' ) );
	?>
	<div class="etheme-footer-col etheme-footer-col--brand">
		<div class="etheme-footer-brand">
			<?php etheme_footer_render_logo( $home_url ); ?>
		</div>

		<?php if ( $wa_url || $ig_url ) : ?>
		<div class="etheme-footer-social">
			<?php if ( $wa_url ) : ?>
				<a
					href="<?php echo esc_url( $wa_url ); ?>"
					class="etheme-footer-social__link etheme-footer-social__link--whatsapp"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="<?php esc_attr_e( 'WhatsApp', 'etheme' ); ?>"
				>
					<?php echo etheme_footer_icon_whatsapp(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="screen-reader-text"><?php esc_html_e( 'WhatsApp', 'etheme' ); ?></span>
				</a>
			<?php endif; ?>
			<?php if ( $ig_url ) : ?>
				<a
					href="<?php echo esc_url( $ig_url ); ?>"
					class="etheme-footer-social__link etheme-footer-social__link--instagram"
					target="_blank"
					rel="noopener noreferrer"
					aria-label="<?php esc_attr_e( 'Instagram', 'etheme' ); ?>"
				>
					<?php echo etheme_footer_icon_instagram(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="screen-reader-text"><?php esc_html_e( 'Instagram', 'etheme' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
		<?php endif; ?>
	</div>
	<?php
}

function etheme_footer_render_logo( $home_url ) {
	if ( has_custom_logo() ) {
		echo get_custom_logo(); // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}

	$logo_path = get_template_directory() . '/assets/images/logo_sinfondo.webp';
	if ( file_exists( $logo_path ) ) {
		?>
		<a href="<?php echo $home_url; ?>" rel="home" class="etheme-footer-brand__link">
			<img
				src="<?php echo esc_url( get_theme_file_uri( 'assets/images/logo_sinfondo.webp' ) ); ?>"
				alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"
				class="etheme-footer-brand__img"
				loading="lazy"
			/>
		</a>
		<?php
		return;
	}

	?>
	<a href="<?php echo $home_url; ?>" rel="home" class="etheme-footer-brand__link etheme-footer-brand__link--text">
		<?php echo esc_html( get_bloginfo( 'name' ) ); ?>
	</a>
	<?php
}

function etheme_footer_icon_whatsapp() {
	return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">'
		. '<path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12c0 1.85.5 3.58 1.37 5.07L2 22l5.07-1.35A9.94 9.94 0 0012 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm4.93 13.57c-.2.57-1.17 1.1-1.6 1.17-.42.07-.95.1-1.53-.1-.35-.12-.8-.28-1.37-.54-2.4-1.04-3.97-3.46-4.09-3.62-.12-.16-.97-1.3-.97-2.47s.62-1.75.84-1.99c.22-.24.48-.3.64-.3h.46c.15 0 .35-.06.55.42.2.5.7 1.72.76 1.84.06.12.1.26.02.42-.08.16-.12.26-.24.4-.12.14-.25.32-.36.43-.12.12-.24.25-.1.49.14.24.62.96 1.33 1.55.92.77 1.7 1.01 1.94 1.13.24.12.38.1.52-.06.14-.16.6-.7.76-.94.16-.24.32-.2.54-.12.22.08 1.4.66 1.64.78.24.12.4.18.46.28.06.1.06.57-.14 1.13z"/>'
		. '</svg>';
}

function etheme_footer_icon_instagram() {
	return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">'
		. '<path fill="currentColor" d="M7.75 2h8.5A5.75 5.75 0 0122 7.75v8.5A5.75 5.75 0 0116.25 22h-8.5A5.75 5.75 0 012 16.25v-8.5A5.75 5.75 0 017.75 2zm0 1.5A4.25 4.25 0 003.5 7.75v8.5A4.25 4.25 0 007.75 20.5h8.5a4.25 4.25 0 004.25-4.25v-8.5A4.25 4.25 0 0016.25 3.5h-8.5zM12 7a5 5 0 110 10A5 5 0 0112 7zm0 1.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7zm5.25-.88a.87.87 0 110 1.75.87.87 0 010-1.75z"/>'
		. '</svg>';
}
