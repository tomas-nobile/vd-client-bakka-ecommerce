<?php
/**
 * Footer legal — links to informational pages (privacy, terms, commerce conditions, contact).
 *
 * URLs resolved via etheme_get_theme_page_url() from src/core/includes/theme-pages.php.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_footer_legal() {
	$links = etheme_get_footer_legal_links();

	if ( empty( $links ) ) {
		return;
	}
	?>
	<div class="etheme-footer-col etheme-footer-col--legal">
		<span class="etheme-footer-col__title"><?php esc_html_e( 'Información', 'etheme' ); ?></span>
		<ul class="etheme-footer-legal-list" role="list">
			<?php foreach ( $links as $link ) : ?>
				<li class="etheme-footer-legal-list__item">
					<a href="<?php echo esc_url( $link['url'] ); ?>" class="etheme-footer-legal-list__link">
						<?php echo esc_html( $link['label'] ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php
}

function etheme_get_footer_legal_links() {
	return array(
		array(
			'url'   => etheme_get_theme_page_url( 'privacy_policy' ),
			'label' => __( 'Política de Privacidad', 'etheme' ),
		),
		array(
			'url'   => etheme_get_theme_page_url( 'terms' ),
			'label' => __( 'Términos y Condiciones', 'etheme' ),
		),
		array(
			'url'   => etheme_get_theme_page_url( 'commerce_conditions' ),
			'label' => __( 'Condiciones de Compra', 'etheme' ),
		),
		array(
			'url'   => etheme_get_theme_page_url( 'contacto' ),
			'label' => __( 'Contacto', 'etheme' ),
		),
	);
}
