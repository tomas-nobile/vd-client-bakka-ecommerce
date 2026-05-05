<?php
/**
 * Order Received — CTA actions component.
 *
 * @param WC_Order $order Order instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'etheme_render_order_received_actions' ) ) {
	function etheme_render_order_received_actions( $order ) {
		$home_url = home_url( '/' );
		$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : $home_url;
		?>
		<section class="order-received-actions" data-aos="fade-up" data-aos-delay="250">
			<a href="<?php echo esc_url( $home_url ); ?>" class="order-received-actions__btn order-received-actions__btn--primary">
				<?php esc_html_e( 'Volver al inicio', 'etheme' ); ?>
			</a>

			<?php if ( ! empty( $shop_url ) ) : ?>
				<a href="<?php echo esc_url( $shop_url ); ?>" class="order-received-actions__link">
					<?php esc_html_e( 'Seguir comprando', 'etheme' ); ?>
				</a>
			<?php endif; ?>
		</section>
		<?php
	}
}
