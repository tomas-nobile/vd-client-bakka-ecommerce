<?php
/**
 * Account shell: orchestrates logged-in dashboard or guest (login/register/lost-password) card.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main entry point — delegates to dashboard layout or guest card.
 *
 * @param array $args {
 *     @type bool   $use_wide_box Wider column (register).
 *     @type string $heading      Page title to pass into dashboard content column.
 * }
 */
function etheme_render_account_shell( $args ) {
	if ( is_user_logged_in() ) {
		etheme_render_account_shell_logged_in( $args );
		return;
	}
	etheme_render_account_shell_guest( $args );
}

/**
 * Logged-in path: load and render the marketplace-style dashboard layout.
 *
 * @param array $args Forwarded to etheme_render_account_dashboard_layout.
 */
function etheme_render_account_shell_logged_in( $args ) {
	$layout_file = __DIR__ . '/account-dashboard-layout.php';
	if ( file_exists( $layout_file ) ) {
		require_once $layout_file;
	}
	if ( function_exists( 'etheme_render_account_dashboard_layout' ) ) {
		etheme_render_account_dashboard_layout( $args );
	}
}

/**
 * Guest path: centered card with WooCommerce output + optional footer link.
 *
 * @param array $args {
 *     @type bool $use_wide_box Wider column for register page.
 * }
 */
function etheme_render_account_shell_guest( $args ) {
	$use_wide_box = ! empty( $args['use_wide_box'] );
	$can_register = ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) );
	$is_register  = $can_register && isset( $_GET['action'] ) && 'register' === sanitize_text_field( wp_unslash( $_GET['action'] ) );
	$is_lost_pw   = function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'lost-password' );
	$register_url = $can_register ? add_query_arg( 'action', 'register', wc_get_page_permalink( 'myaccount' ) ) : '';
	$login_url    = wc_get_page_permalink( 'myaccount' );

	$box_class = 'login-form-box mx-auto w-full' . ( $use_wide_box ? ' page-account-block__box--wide' : ' max-w-[450px]' );
	?>
	<div class="<?php echo esc_attr( $box_class ); ?>">
		<div class="login-card mb-8 px-4 py-5 md:px-6 md:pt-8 md:pb-8" data-aos="fade-up" data-aos-delay="100">
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

		<?php if ( ! $is_register && ! $is_lost_pw && $can_register && $register_url ) : ?>
			<div class="join-now-outer text-center" data-aos="fade-up" data-aos-delay="150">
				<a href="<?php echo esc_url( $register_url ); ?>">
					<?php esc_html_e( 'Join now, create your FREE account', 'etheme' ); ?>
				</a>
			</div>
		<?php elseif ( $is_register && $login_url ) : ?>
			<div class="join-now-outer text-center" data-aos="fade-up" data-aos-delay="150">
				<a href="<?php echo esc_url( $login_url ); ?>">
					<?php esc_html_e( 'Already have an account?', 'etheme' ); ?>
				</a>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
