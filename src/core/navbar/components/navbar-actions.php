<?php
/**
 * Navbar actions — search, cart (with WooCommerce count).
 *
 * @param array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_navbar_actions( $attributes ) {
	$show_search = ! empty( $attributes['showSearch'] );
	$show_cart   = ! empty( $attributes['showCart'] );

	$cart_count = 0;
	if ( $show_cart && function_exists( 'WC' ) && WC()->cart ) {
		$cart_count = (int) WC()->cart->get_cart_contents_count();
	}

	$cart_url = $show_cart && function_exists( 'wc_get_cart_url' )
		? wc_get_cart_url()
		: home_url( '/cart/' );
	?>
	<div class="etheme-navbar-actions">

		<?php if ( $show_search ) : ?>
			<button
				class="etheme-navbar-action etheme-navbar-action--search"
				type="button"
				aria-label="<?php esc_attr_e( 'Abrir buscador', 'etheme' ); ?>"
				aria-controls="etheme-search-modal"
				aria-expanded="false"
			>
				<svg width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
					<circle cx="8.5" cy="8.5" r="6" stroke="currentColor" stroke-width="1.8"/>
					<path d="M13 13l4.5 4.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
				</svg>
			</button>
		<?php endif; ?>

		<?php if ( $show_cart ) : ?>
			<a
				href="<?php echo esc_url( $cart_url ); ?>"
				class="etheme-navbar-action etheme-navbar-action--cart"
				aria-label="<?php esc_attr_e( 'Ver carrito', 'etheme' ); ?>"
			>
				<svg width="20" height="20" viewBox="0 0 22 22" fill="none" aria-hidden="true">
					<path d="M1 1.5h2.8l2.4 10.5H17l2-7.5H5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="9" cy="18.5" r="1.5" fill="currentColor"/>
					<circle cx="16" cy="18.5" r="1.5" fill="currentColor"/>
				</svg>
				<span
					class="etheme-navbar-action__badge<?php echo $cart_count > 0 ? ' etheme-navbar-action__badge--visible' : ''; ?>"
					aria-live="polite"
					aria-atomic="true"
				>
					<?php echo esc_html( $cart_count ); ?>
				</span>
			</a>
			<?php // Inline override: if a cart mutation happened in this session, the cached/stale server-rendered count would flash before the async sync corrects it. Apply the last known count from sessionStorage before paint. ?>
			<script>
			(function(){
				try {
					var v = sessionStorage.getItem('etheme_cart_count');
					if (v === null) return;
					var n = parseInt(v, 10);
					if (isNaN(n)) return;
					var els = document.querySelectorAll('.etheme-navbar-action__badge');
					for (var i = 0; i < els.length; i++) {
						els[i].textContent = String(n);
						if (n > 0) els[i].classList.add('etheme-navbar-action__badge--visible');
						else els[i].classList.remove('etheme-navbar-action__badge--visible');
					}
				} catch (e) {}
			})();
			</script>
		<?php endif; ?>

	</div>
	<?php
}
