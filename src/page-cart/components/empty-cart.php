<?php
/**
 * Empty Cart Component
 *
 * Renders the empty cart state: message, primary CTA to shop, secondary link home.
 * Visual language matches checkout / cart (gray-900 CTAs, restrained typography).
 *
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_empty_cart() {
	$shop_url = wc_get_page_permalink( 'shop' );
	$home_url = home_url( '/' );
	?>
	<div class="empty-cart" data-aos="fade-up">
		<div class="empty-cart__card mx-auto max-w-xl text-center">
			<div class="empty-cart__visual" aria-hidden="true">
				<svg class="empty-cart__icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M7 8h14l-1.6 8.4c-.2.9-1 1.6-1.9 1.6H10.2c-1 0-1.8-.7-2-1.6L7 8Z" stroke="currentColor" stroke-width="1.65" stroke-linejoin="round"/>
					<path d="M7 8 6.3 5.3C6.1 4.5 5.3 4 4.5 4H3" stroke="currentColor" stroke-width="1.65" stroke-linecap="round"/>
					<circle cx="10.5" cy="20" r="1.15" fill="currentColor"/>
					<circle cx="17" cy="20" r="1.15" fill="currentColor"/>
				</svg>
			</div>

			<h2 class="empty-cart__title">
				<?php esc_html_e( 'Tu carrito está vacío', 'etheme' ); ?>
			</h2>

			<p class="empty-cart__text">
				<?php esc_html_e( 'Todavía no agregaste productos. Explorá la tienda y elegí lo que más te guste.', 'etheme' ); ?>
			</p>

			<div class="empty-cart__actions">
				<a
					href="<?php echo esc_url( $shop_url ); ?>"
					class="empty-cart__btn empty-cart__btn--primary"
				>
					<?php esc_html_e( 'Ir a la tienda', 'etheme' ); ?>
				</a>
				<a
					href="<?php echo esc_url( $home_url ); ?>"
					class="empty-cart__btn empty-cart__btn--secondary"
				>
					<?php esc_html_e( 'Volver al inicio', 'etheme' ); ?>
				</a>
			</div>

			<p class="empty-cart__hint">
				<?php esc_html_e( 'Cuando encuentres algo que te encante, tocá “Agregar al carrito” y lo verás acá.', 'etheme' ); ?>
			</p>
		</div>
	</div>
	<?php
}
