<?php
/**
 * Order Received — Order Not Found (state B) component.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'etheme_render_order_not_found' ) ) {
	function etheme_render_order_not_found() {
		$home_url = home_url( '/' );
		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : $home_url;
		?>
		<section class="order-received-notfound text-center" data-aos="fade-up">
			<h1 class="order-received-notfound__title">
				<?php esc_html_e( 'No pudimos encontrar la orden solicitada.', 'etheme' ); ?>
			</h1>
			<p class="order-received-notfound__text">
				<?php esc_html_e( 'Verificá el enlace que recibiste por email o contactanos si creés que es un error.', 'etheme' ); ?>
			</p>
			<div class="order-received-notfound__actions">
				<a href="<?php echo esc_url( $home_url ); ?>" class="order-received-actions__btn order-received-actions__btn--primary">
					<?php esc_html_e( 'Volver al inicio', 'etheme' ); ?>
				</a>
				<a href="<?php echo esc_url( $shop_url ); ?>" class="order-received-actions__btn order-received-actions__btn--secondary">
					<?php esc_html_e( 'Ir a la tienda', 'etheme' ); ?>
				</a>
			</div>
		</section>
		<?php
	}
}
