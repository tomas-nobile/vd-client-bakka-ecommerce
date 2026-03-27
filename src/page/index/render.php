<?php
/**
 * Page (My Account) – orchestrator for WooCommerce customer account UI.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	?>
	<div <?php echo get_block_wrapper_attributes( array( 'class' => 'page-account-block' ) ); ?>>
		<p class="text-center text-gray-500 py-8"><?php esc_html_e( 'WooCommerce is required for this block.', 'etheme' ); ?></p>
	</div>
	<?php
	return;
}

$components_dir = get_template_directory() . '/src/page/components/';

foreach ( array( 'account-heading', 'account-shell' ) as $component ) {
	$file = $components_dir . $component . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

$is_logged_in = is_user_logged_in();

$can_register = ( 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ) );

$is_register = ! $is_logged_in && $can_register && isset( $_GET['action'] ) && 'register' === sanitize_text_field( wp_unslash( $_GET['action'] ) );

$is_lost_password = ! $is_logged_in && function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'lost-password' );

$use_wide_box = $is_logged_in || $is_register;

$extra_section_class = $is_register ? ' page-account-block--register-pad' : '';

if ( $is_logged_in ) {
	$user    = wp_get_current_user();
	$heading = sprintf(
		/* translators: %s: user display name */
		__( 'Hello, %s', 'etheme' ),
		esc_html( $user->display_name )
	);
} elseif ( $is_register ) {
	$heading = __( 'Create Your FREE Account', 'etheme' );
} elseif ( $is_lost_password ) {
	$heading = __( 'Lost your password?', 'woocommerce' );
} else {
	$heading = __( 'Welcome Back !', 'etheme' );
}

$wrapper_classes = array(
	'page-account-block',
	'etheme-contrive-account',
	'login-form',
	'flex',
	'items-center',
	'min-h-screen',
	'bg-account-mint',
);

if ( $is_logged_in ) {
	$wrapper_classes[] = 'page-account-block--logged-in';
}

$wrapper_classes = implode( ' ', $wrapper_classes );

?>
<div <?php echo get_block_wrapper_attributes( array( 'class' => $wrapper_classes . $extra_section_class ) ); ?>>
	<div class="container mx-auto w-full px-4 py-10 md:py-14">
		<?php // For logged-in users the greeting lives inside the dashboard content column. ?>
		<?php if ( ! $is_logged_in && function_exists( 'etheme_render_account_heading' ) ) : ?>
			<?php etheme_render_account_heading( array( 'heading' => $heading ) ); ?>
		<?php endif; ?>

		<?php
		if ( function_exists( 'etheme_render_account_shell' ) ) {
			etheme_render_account_shell(
				array(
					'use_wide_box' => $use_wide_box,
					'heading'      => $heading,
				)
			);
		}
		?>
	</div>
</div>
