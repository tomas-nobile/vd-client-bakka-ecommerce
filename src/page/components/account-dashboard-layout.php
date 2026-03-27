<?php
/**
 * Logged-in dashboard: two-column layout (sidebar nav + WC content area).
 * Sidebar nav is custom; WC's built-in navigation is hidden via CSS.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns the total order count for the current customer (cheap query guard).
 *
 * @return int
 */
function etheme_get_customer_order_count() {
	$user_id = get_current_user_id();
	if ( ! $user_id ) {
		return 0;
	}
	if ( function_exists( 'wc_get_customer_order_count' ) ) {
		return (int) wc_get_customer_order_count( $user_id );
	}
	return 0;
}

/**
 * Returns true when the current page is the dashboard "home" (no sub-endpoint).
 *
 * @return bool
 */
function etheme_is_dashboard_home() {
	return function_exists( 'is_wc_endpoint_url' ) && ! is_wc_endpoint_url();
}

/**
 * Outputs an inline SVG icon for the summary strip.
 *
 * @param string $type Icon key: 'orders' | 'address' | 'account'.
 */
function etheme_render_summary_icon( $type ) {
	$icons = array(
		'orders'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 002 2h14a2 2 0 002-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 01-8 0"/></svg>',
		'address' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>',
		'account' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
	);
	if ( isset( $icons[ $type ] ) ) {
		echo $icons[ $type ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static SVG constants
	}
}

/**
 * Renders the dashboard home summary strip (orders / addresses / account shortcuts).
 */
function etheme_render_dashboard_summary_strip() {
	$order_count = etheme_get_customer_order_count();
	$items       = array(
		'orders'  => array( wc_get_account_endpoint_url( 'orders' ), __( 'Orders', 'etheme' ), $order_count ),
		'address' => array( wc_get_account_endpoint_url( 'edit-address' ), __( 'Addresses', 'etheme' ), 0 ),
		'account' => array( wc_get_account_endpoint_url( 'edit-account' ), __( 'Account details', 'etheme' ), 0 ),
	);
	?>
	<div class="dashboard-summary" data-aos="fade-up">
		<?php foreach ( $items as $icon => $data ) : ?>
			<a href="<?php echo esc_url( $data[0] ); ?>" class="dashboard-summary__item">
				<?php etheme_render_summary_icon( $icon ); ?>
				<span class="dashboard-summary__label">
					<?php echo esc_html( $data[1] ); ?>
					<?php if ( $data[2] > 0 ) : ?>
						<span class="dashboard-summary__badge"><?php echo esc_html( $data[2] ); ?></span>
					<?php endif; ?>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Renders the dashboard greeting header.
 *
 * @param string $heading Greeting text.
 */
function etheme_render_dashboard_greeting( $heading ) {
	if ( ! $heading ) {
		return;
	}
	?>
	<div class="account-dashboard__greeting" data-aos="fade-up">
		<h2 class="account-dashboard__greeting-title"><?php echo esc_html( $heading ); ?></h2>
	</div>
	<?php
}

/**
 * Renders the WooCommerce account output inside the dashboard content card.
 */
function etheme_render_account_dashboard_wc() {
	?>
	<div class="login-card account-dashboard__wc-content px-4 py-5 md:px-6 md:pt-8 md:pb-8" data-aos="fade-up" data-aos-delay="100">
		<?php
		if ( class_exists( 'WC_Shortcode_My_Account' ) && is_callable( array( 'WC_Shortcode_My_Account', 'output' ) ) ) {
			WC_Shortcode_My_Account::output( array() );
		} elseif ( function_exists( 'do_shortcode' ) && shortcode_exists( 'woocommerce_my_account' ) ) {
			echo do_shortcode( '[woocommerce_my_account]' );
		} else {
			echo '<p class="text-center text-gray-500">' . esc_html__( 'My Account is unavailable.', 'etheme' ) . '</p>';
		}
		?>
	</div>
	<?php
}

/**
 * Renders the main content column (greeting + optional summary strip + WC output).
 *
 * @param string $heading Greeting text.
 */
function etheme_render_account_dashboard_content( $heading ) {
	?>
	<div class="account-dashboard__content">
		<?php etheme_render_dashboard_greeting( $heading ); ?>
		<?php if ( etheme_is_dashboard_home() ) : ?>
			<?php etheme_render_dashboard_summary_strip(); ?>
		<?php endif; ?>
		<?php etheme_render_account_dashboard_wc(); ?>
	</div>
	<?php
}

/**
 * Renders the full two-column dashboard layout.
 *
 * @param array $args {
 *     @type string $heading Greeting shown in content column.
 * }
 */
function etheme_render_account_dashboard_layout( $args ) {
	$heading  = isset( $args['heading'] ) ? $args['heading'] : '';
	$nav_file = __DIR__ . '/account-dashboard-nav.php';
	if ( file_exists( $nav_file ) ) {
		require_once $nav_file;
	}
	?>
	<div class="account-dashboard">
		<aside class="account-dashboard__sidebar" data-aos="fade-up">
			<?php if ( function_exists( 'etheme_render_account_dashboard_nav' ) ) : ?>
				<?php etheme_render_account_dashboard_nav(); ?>
			<?php endif; ?>
		</aside>
		<?php etheme_render_account_dashboard_content( $heading ); ?>
	</div>
	<?php
}
