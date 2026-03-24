<?php
// core/sub-banner.
/**
 * Sub Banner Component (page header / hero strip)
 *
 * Reusable page-level banner rendered below the site header.
 * Shows a title, optional subtitle, and an auto-generated breadcrumb trail.
 * Layout mirrors the `sub_banner_con` design from the Contrive reference (blog.html):
 * background image, absolute decorative images, centered content column.
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
 *           array( 'label' => __( 'Posteos', 'etheme' ) ),
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
 * Render absolute-positioned left / right decorative images.
 *
 * @param string $theme_uri Theme directory URI.
 */
function etheme_sub_banner_deco_images( $theme_uri ) {
	?>
	<figure class="subbanner-leftimage image mb-0" aria-hidden="true">
		<img src="<?php echo esc_url( $theme_uri . '/assets/images/update-leftimage.png' ); ?>" class="img-fluid" alt="">
	</figure>
	<figure class="subbanner-rightimage image mb-0" aria-hidden="true">
		<img src="<?php echo esc_url( $theme_uri . '/assets/images/about-rightimage.png' ); ?>" class="img-fluid" alt="">
	</figure>
	<?php
}

/**
 * Render the breadcrumb `.box` inside the content block.
 *
 * Last item has no link and receives `aria-current="page"`.
 * All labels are uppercased per the reference design.
 *
 * @param array $breadcrumbs Array of { label, url? }.
 */
function etheme_sub_banner_breadcrumbs( array $breadcrumbs ) {
	if ( empty( $breadcrumbs ) ) {
		return;
	}
	?>
	<nav aria-label="<?php esc_attr_e( 'Breadcrumb', 'etheme' ); ?>">
		<div class="box">
			<?php foreach ( $breadcrumbs as $i => $crumb ) :
				$is_last   = ( $i === array_key_last( $breadcrumbs ) );
				$crumb_url = isset( $crumb['url'] ) ? $crumb['url'] : '';
				$label     = esc_html( strtoupper( $crumb['label'] ) );
				?>
				<?php if ( ! $is_last && $crumb_url ) : ?>
					<a href="<?php echo esc_url( $crumb_url ); ?>" class="text-decoration-none">
						<span class="mb-0"><?php echo $label; ?></span>
					</a>
					<span class="mb-0 slash" aria-hidden="true">/</span>
				<?php else : ?>
					<span class="mb-0 box_span" aria-current="page"><?php echo $label; ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</nav>
	<?php
}

/**
 * Render the centered content block: heading, subtitle, breadcrumbs.
 *
 * @param string $title
 * @param string $subtitle
 * @param array  $breadcrumbs
 */
function etheme_sub_banner_content_block( $title, $subtitle, array $breadcrumbs ) {
	?>
	<div class="sub_banner_content" data-aos="fade-up">
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $subtitle ) : ?>
			<p class="text-size-18"><?php echo esc_html( $subtitle ); ?></p>
		<?php endif; ?>
		<?php etheme_sub_banner_breadcrumbs( $breadcrumbs ); ?>
	</div>
	<?php
}

/**
 * Render the reusable sub-banner (page header strip).
 *
 * @param array $args {
 *   @type string        $title         Main heading text (required).
 *   @type string        $subtitle      Short description below the title (optional).
 *   @type array[]       $breadcrumbs   Array of items: [ 'label' => string, 'url' => string (optional) ].
 *                                      The last item is treated as the current page (no link).
 *   @type callable|null $after_content Callable rendered immediately after the closing </div class="sub_banner">.
 *                                      Use this slot for content that must appear "glued" below the banner
 *                                      (e.g. category chips) sharing the same dark background.
 * }
 */
function etheme_render_sub_banner( array $args = array() ) {
	$defaults = array(
		'title'         => '',
		'subtitle'      => '',
		'breadcrumbs'   => array(),
		'after_content' => null,
	);

	$args          = wp_parse_args( $args, $defaults );
	$title         = $args['title'];
	$subtitle      = $args['subtitle'];
	$breadcrumbs   = is_array( $args['breadcrumbs'] ) ? $args['breadcrumbs'] : array();
	$after_content = is_callable( $args['after_content'] ) ? $args['after_content'] : null;

	if ( empty( $title ) ) {
		return;
	}

	$theme_uri = get_template_directory_uri();
	$bg_url    = esc_url( $theme_uri . '/assets/images/subbanner-backgroundimage.jpg' );
	?>

	<div class="sub_banner">
		<section
			class="sub_banner_con"
			style="background-image: url('<?php echo $bg_url; ?>');"
			aria-label="<?php echo esc_attr( $title ); ?>"
		>
			<?php etheme_sub_banner_deco_images( $theme_uri ); ?>

			<div class="container mx-auto">
				<div class="sub_banner_row">
					<div class="sub_banner_col">
						<?php etheme_sub_banner_content_block( $title, $subtitle, $breadcrumbs ); ?>
					</div>
				</div>
			</div>
		</section>

		<?php if ( $after_content ) : ?>
			<?php call_user_func( $after_content ); ?>
		<?php endif; ?>
	</div>

	<?php
}
