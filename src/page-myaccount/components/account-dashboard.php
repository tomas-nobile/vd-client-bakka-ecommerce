<?php
/**
 * Account dashboard component for logged-in users.
 *
 * Shows welcome message, quick navigation links to WooCommerce
 * My Account endpoints, and logout action.
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the account dashboard for logged-in users.
 *
 * @return void
 */
function etheme_render_account_dashboard() {
	$current_user = wp_get_current_user();
	$display_name = $current_user->display_name ?: $current_user->user_email;
	$myaccount_url = wc_get_page_permalink( 'myaccount' );
	$menu_items   = etheme_get_account_menu_items( $myaccount_url );
	$logout_url   = wc_logout_url( home_url() );
	?>
	<div class="mx-auto max-w-3xl">

		<!-- Welcome header -->
		<div class="mb-8 md:mb-12">
			<h1 class="text-2xl font-bold text-gray-900 tracking-tight md:text-3xl">
				<?php
				printf(
					/* translators: %s: customer display name */
					esc_html__( 'Hello, %s', 'etheme' ),
					esc_html( $display_name )
				);
				?>
			</h1>
			<p class="mt-2 text-sm text-gray-500">
				<?php esc_html_e( 'Manage your orders, addresses, and account details.', 'etheme' ); ?>
			</p>
		</div>

		<!-- Dashboard grid -->
		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			<?php foreach ( $menu_items as $item ) : ?>
			<a
				href="<?php echo esc_url( $item['url'] ); ?>"
				class="group flex items-start gap-4 rounded-2xl border border-gray-200 bg-white p-5 transition-all hover:border-gray-900 hover:shadow-sm"
			>
				<span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-600 transition-colors group-hover:bg-gray-900 group-hover:text-white">
					<?php echo $item['icon']; ?>
				</span>
				<div>
					<span class="block text-sm font-semibold text-gray-900">
						<?php echo esc_html( $item['label'] ); ?>
					</span>
					<span class="mt-1 block text-xs text-gray-500">
						<?php echo esc_html( $item['description'] ); ?>
					</span>
				</div>
			</a>
			<?php endforeach; ?>
		</div>

		<!-- Logout -->
		<div class="mt-8 text-center">
			<a
				href="<?php echo esc_url( $logout_url ); ?>"
				class="inline-flex items-center gap-2 text-sm font-medium text-gray-500 underline decoration-gray-300 underline-offset-2 hover:text-gray-900 hover:decoration-gray-900"
			>
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
					<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
					<polyline points="16 17 21 12 16 7"/>
					<line x1="21" y1="12" x2="9" y2="12"/>
				</svg>
				<?php esc_html_e( 'Sign out', 'etheme' ); ?>
			</a>
		</div>

	</div>
	<?php
}

/**
 * Get account menu items with icons and descriptions.
 *
 * @param string $base_url My Account page URL.
 * @return array Menu items with url, label, description, and icon.
 */
function etheme_get_account_menu_items( $base_url ) {
	static $items = null;
	if ( null !== $items ) {
		return $items;
	}

	$items = array(
		array(
			'url'         => wc_get_endpoint_url( 'orders', '', $base_url ),
			'label'       => __( 'Orders', 'etheme' ),
			'description' => __( 'Track and manage your orders', 'etheme' ),
			'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/></svg>',
		),
		array(
			'url'         => wc_get_endpoint_url( 'edit-address', '', $base_url ),
			'label'       => __( 'Addresses', 'etheme' ),
			'description' => __( 'Billing and shipping addresses', 'etheme' ),
			'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
		),
		array(
			'url'         => wc_get_endpoint_url( 'edit-account', '', $base_url ),
			'label'       => __( 'Account Details', 'etheme' ),
			'description' => __( 'Update your name and password', 'etheme' ),
			'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		),
		array(
			'url'         => wc_get_endpoint_url( 'downloads', '', $base_url ),
			'label'       => __( 'Downloads', 'etheme' ),
			'description' => __( 'Access your digital purchases', 'etheme' ),
			'icon'        => '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>',
		),
	);

	return $items;
}
