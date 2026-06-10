<?php
/**
 * Announcement bar — content component.
 *
 * Outputs a seamless full-width marquee: the message is repeated several times
 * inside a "group", and the group is duplicated so a -50% translate loops with
 * no visible seam or empty gap. The visible marquee is hidden from assistive
 * tech; a single off-screen copy (`__static`) is the accessible/reduced-motion
 * text.
 *
 * @param array $attributes Block attributes (message, bgColor).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_announcement_bar( $attributes = array() ) {
	$message  = isset( $attributes['message'] ) ? trim( (string) $attributes['message'] ) : '';
	$bg_color = isset( $attributes['bgColor'] ) ? (string) $attributes['bgColor'] : '#ebb55f';

	if ( '' === $message ) {
		return;
	}

	// Split the message into phrases on the middot so every phrase is separated
	// by the same diamond with the same spacing (instead of a plain "·").
	$phrases = array_filter( array_map( 'trim', explode( '·', $message ) ), 'strlen' );
	if ( empty( $phrases ) ) {
		$phrases = array( $message );
	}

	// Copies per group — enough that one group overflows ultra-wide viewports
	// so there is never empty space, while the duplicate group keeps it seamless.
	$repeat = 4;

	$build_group = static function () use ( $phrases, $repeat ) {
		ob_start();
		for ( $i = 0; $i < $repeat; $i++ ) {
			foreach ( $phrases as $phrase ) {
				?>
				<span class="etheme-announcement-bar__item"><?php echo esc_html( $phrase ); ?></span>
				<span class="etheme-announcement-bar__sep" aria-hidden="true"></span>
				<?php
			}
		}
		return ob_get_clean();
	};

	$group = $build_group();

	$wrapper = get_block_wrapper_attributes(
		array(
			'class' => 'etheme-announcement-bar',
			'style' => '--etheme-announcement-bg:' . esc_attr( $bg_color ) . ';',
		)
	);
	?>
	<div <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<div class="etheme-announcement-bar__viewport" aria-hidden="true">
			<div class="etheme-announcement-bar__track">
				<div class="etheme-announcement-bar__group"><?php echo $group; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
				<div class="etheme-announcement-bar__group"><?php echo $group; // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			</div>
		</div>

		<p class="etheme-announcement-bar__static"><?php echo esc_html( $message ); ?></p>

		<button
			type="button"
			class="etheme-announcement-bar__close"
			aria-label="<?php echo esc_attr__( 'Cerrar barra de anuncios', 'etheme' ); ?>"
		>
			<span aria-hidden="true">&times;</span>
		</button>
	</div>
	<?php
}
