<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_404_content() {
	?>
	<section class="error-page">
		<div class="error-page__inner">
			<h1 class="error-page__code">404</h1>
			<p class="error-page__title">
				<?php esc_html_e( '¡Lo sentimos! La página no fue encontrada', 'etheme' ); ?>
			</p>
			<p class="error-page__description">
				<?php esc_html_e( 'La página que estás buscando no existe o ya no está disponible. Podés volver al inicio y continuar navegando.', 'etheme' ); ?>
			</p>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="error-page__btn">
				<?php esc_html_e( 'Volver al inicio', 'etheme' ); ?>
				<i class="fa fa-arrow-right" aria-hidden="true"></i>
			</a>
		</div>
	</section>
	<?php
}
