<?php
/**
 * Logo + page title (Contrive login / register / account style).
 *
 * @param array $args {
 *     @type string $heading Visible `<h2>` text.
 * }
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array $args Args.
 */
function etheme_render_account_heading( $args ) {
	$heading = isset( $args['heading'] ) ? $args['heading'] : '';

	$logo_path = get_template_directory() . '/assets/images/logo.png';
	$logo_url  = '';
	if ( file_exists( $logo_path ) ) {
		$logo_url = get_theme_file_uri( 'assets/images/logo.png' );
	}

	$home_url = home_url( '/' );
	?>
	<div class="login-form-title mx-auto mb-10 max-w-4xl text-center" data-aos="fade-up">
		<?php if ( $logo_url ) : ?>
			<a href="<?php echo esc_url( $home_url ); ?>" class="inline-block">
				<figure class="login-page-logo mx-auto mb-6">
					<img src="<?php echo esc_url( $logo_url ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" class="mx-auto h-auto max-w-[200px]" loading="lazy" width="200" height="60" />
				</figure>
			</a>
		<?php endif; ?>
		<?php if ( $heading ) : ?>
			<h2 class="account-page-title m-0 text-black">
				<?php echo esc_html( $heading ); ?>
			</h2>
		<?php endif; ?>
	</div>
	<?php
}
