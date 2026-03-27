<?php
/**
 * Dashboard sidebar navigation.
 * Renders WooCommerce account menu items with active-state detection.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the active WC account endpoint key (defaults to 'dashboard').
 *
 * @return string
 */
function etheme_get_current_account_endpoint() {
	if ( ! function_exists( 'is_wc_endpoint_url' ) || ! WC()->query ) {
		return 'dashboard';
	}
	global $wp;
	foreach ( WC()->query->get_query_vars() as $key => $var ) {
		if ( isset( $wp->query_vars[ $var ] ) ) {
			return $key;
		}
	}
	return 'dashboard';
}

/**
 * @param string $key Menu item endpoint key.
 * @return bool
 */
function etheme_is_nav_item_active( $key ) {
	$current = etheme_get_current_account_endpoint();
	return $key === $current;
}

/**
 * @param string $key   Endpoint key.
 * @param string $label Display label.
 * @param string $url   Item URL.
 */
function etheme_render_account_nav_item( $key, $label, $url ) {
	$is_active = etheme_is_nav_item_active( $key );
	$classes   = array( 'account-nav__item' );
	if ( $is_active ) {
		$classes[] = 'account-nav__item--active';
	}
	if ( 'customer-logout' === $key ) {
		$classes[] = 'account-nav__item--logout';
	}
	$aria = $is_active ? ' aria-current="page"' : '';
	?>
	<li class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
		<a href="<?php echo esc_url( $url ); ?>"<?php echo $aria; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> class="account-nav__link">
			<?php echo esc_html( $label ); ?>
		</a>
	</li>
	<?php
}

/**
 * Renders the full sidebar navigation.
 */
function etheme_render_account_dashboard_nav() {
	if ( ! function_exists( 'wc_get_account_menu_items' ) ) {
		return;
	}
	$menu_items = wc_get_account_menu_items();
	?>
	<nav class="account-nav" aria-label="<?php esc_attr_e( 'My account', 'etheme' ); ?>">
		<button
			class="account-nav__toggle"
			aria-expanded="false"
			aria-controls="account-nav-panel"
			type="button"
		>
			<span class="account-nav__toggle-label"><?php esc_html_e( 'Account menu', 'etheme' ); ?></span>
			<span class="account-nav__toggle-chevron" aria-hidden="true"></span>
		</button>
		<ul id="account-nav-panel" class="account-nav__list" role="list">
			<?php foreach ( $menu_items as $key => $label ) : ?>
				<?php etheme_render_account_nav_item( $key, $label, wc_get_account_endpoint_url( $key ) ); ?>
			<?php endforeach; ?>
		</ul>
	</nav>
	<?php
}
