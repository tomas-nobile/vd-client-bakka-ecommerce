<?php
// core/sub-banner.
/**
 * Sub Banner Component (page header / hero strip)
 *
 * Reusable page-level banner rendered below the site header.
 * Shows a title, optional subtitle, and an auto-generated breadcrumb trail.
 *
 * Usage example (from any page render.php):
 *
 *   require_once get_template_directory() . '/src/core/components/sub-banner.php';
 *
 *   etheme_render_sub_banner( array(
 *       'title'       => __( 'Posteos', 'etheme' ),
 *       'subtitle'    => __( 'Seguinos en redes y descubrí nuestros últimos posteos.', 'etheme' ),
 *       'breadcrumbs' => array(
 *           array( 'label' => __( 'Home', 'etheme' ), 'url' => home_url( '/' ) ),
 *           array( 'label' => __( 'Posteos', 'etheme' ) ),   // last item: no url = current page
 *       ),
 *   ) );
 *
 * Breadcrumb examples:
 *   Blog:              HOME / BLOG
 *   Shop > Category:   HOME / SHOP / {Category Name}
 *   Contact:           HOME / CONTACTO
 *
 * @package Etheme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the reusable sub-banner (page header strip).
 *
 * @param array $args {
 *   @type string  $title       Main heading text (required).
 *   @type string  $subtitle    Short description below the title (optional).
 *   @type array[] $breadcrumbs Array of items: [ 'label' => string, 'url' => string (optional) ].
 *                              The last item is treated as the current page (no link).
 * }
 */
function etheme_render_sub_banner( array $args = array() ) {
	$defaults = array(
		'title'       => '',
		'subtitle'    => '',
		'breadcrumbs' => array(),
	);

	$args = wp_parse_args( $args, $defaults );

	$title       = $args['title'];
	$subtitle    = $args['subtitle'];
	$breadcrumbs = is_array( $args['breadcrumbs'] ) ? $args['breadcrumbs'] : array();

	if ( empty( $title ) ) {
		return;
	}
	?>

	<section class="sub-banner bg-[#fff3f0] relative overflow-hidden" aria-label="<?php echo esc_attr( $title ); ?>">
		<div class="sub-banner__inner">
			<div class="container mx-auto px-6 md:px-12 lg:px-20 py-12 md:py-16 lg:py-20">

				<?php if ( ! empty( $breadcrumbs ) ) : ?>
					<nav class="sub-banner__breadcrumbs mb-3 text-xs tracking-[0.18em] uppercase text-[#6a6a6a]" aria-label="<?php esc_attr_e( 'Breadcrumb', 'etheme' ); ?>">
						<ol class="sub-banner__breadcrumb-list">
							<?php foreach ( $breadcrumbs as $i => $crumb ) :
								$is_last  = ( $i === array_key_last( $breadcrumbs ) );
								$crumb_url = isset( $crumb['url'] ) ? $crumb['url'] : '';
								?>
								<li class="sub-banner__breadcrumb-item<?php echo $is_last ? ' sub-banner__breadcrumb-item--current' : ''; ?>">
									<?php if ( ! $is_last && $crumb_url ) : ?>
										<a href="<?php echo esc_url( $crumb_url ); ?>" class="sub-banner__breadcrumb-link">
											<?php echo esc_html( strtoupper( $crumb['label'] ) ); ?>
										</a>
										<span class="sub-banner__breadcrumb-sep" aria-hidden="true">/</span>
									<?php else : ?>
										<span class="sub-banner__breadcrumb-current" aria-current="page">
											<?php echo esc_html( strtoupper( $crumb['label'] ) ); ?>
										</span>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ol>
					</nav>
				<?php endif; ?>

				<h1 class="sub-banner__title text-3xl md:text-4xl lg:text-5xl font-bold text-[#000000] leading-tight mb-3">
					<?php echo esc_html( $title ); ?>
				</h1>

				<?php if ( $subtitle ) : ?>
					<p class="sub-banner__subtitle text-[0.95rem] text-[#6a6a6a] max-w-xl leading-relaxed">
						<?php echo esc_html( $subtitle ); ?>
					</p>
				<?php endif; ?>

			</div>
		</div>
	</section>

	<?php
}
