<?php
// page-posteos-index.
/**
 * Page Posteos Index — Main renderer for /posteos
 *
 * Renders: sub-banner, Instagram-style card grid, shared modal, load-more button.
 * Posts are fetched via etheme_get_social_posts() with offset support.
 * The "Mostrar más" button triggers AJAX (etheme_posteos_load_more action).
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content (unused).
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once get_template_directory() . '/src/core/includes/social-posts.helpers.php';
require_once get_template_directory() . '/src/core/components/blog-card.php';
require_once get_template_directory() . '/src/core/components/blog-card-modal.php';
require_once get_template_directory() . '/src/core/components/sub-banner.php';

$defaults = array(
	'postsPerPage'  => 15,
	'bannerTitle'   => __( 'Posteos', 'etheme' ),
	'bannerSubtitle' => __( 'Seguinos en nuestras redes y descubrí los últimos posteos de Bakka.', 'etheme' ),
);

$attributes  = wp_parse_args( $attributes, $defaults );
$per_page    = max( 1, absint( $attributes['postsPerPage'] ) );
$posts       = etheme_get_social_posts( $per_page, 0 );

// Total published social posts for the load-more logic.
$total_query = new WP_Query( array(
	'post_type'      => 'social_post',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'post_status'    => 'publish',
	'no_found_rows'  => false,
) );
$total_posts = $total_query->found_posts;

$ajaxurl    = esc_url( admin_url( 'admin-ajax.php' ) );
$nonce      = wp_create_nonce( 'etheme_posteos_load_more' );
$has_more   = $total_posts > $per_page;
?>

<div
	<?php echo get_block_wrapper_attributes( array(
		'data-ajaxurl' => $ajaxurl,
		'data-nonce'   => $nonce,
	) ); ?>
>

	<?php
	etheme_render_sub_banner( array(
		'title'       => $attributes['bannerTitle'],
		'subtitle'    => $attributes['bannerSubtitle'],
		'breadcrumbs' => array(
			array(
				'label' => __( 'Home', 'etheme' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => $attributes['bannerTitle'],
			),
		),
	) );
	?>

	<section class="posteos-con" aria-labelledby="posteos-heading">
		<div class="container mx-auto px-6 md:px-12 lg:px-20 py-16">

			<?php if ( empty( $posts ) ) : ?>
				<p class="text-center text-gray-500 py-8">
					<?php esc_html_e( 'No hay posteos publicados todavía.', 'etheme' ); ?>
				</p>
			<?php else : ?>

				<div
					class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 article-cards-row"
					id="posteos-cards-grid"
					data-aos="fade-up"
				>
					<?php foreach ( $posts as $post ) : ?>
						<div class="article-card-col">
							<?php etheme_render_home_blog_card( $post ); ?>
						</div>
					<?php endforeach; ?>
				</div>

				<?php if ( $has_more ) : ?>
					<div class="text-center mt-14" data-aos="fade-up">
						<button
							id="posteos-load-more"
							class="primary_btn"
							type="button"
							data-per-page="<?php echo esc_attr( $per_page ); ?>"
							data-total="<?php echo esc_attr( $total_posts ); ?>"
							aria-live="polite"
						>
							<?php esc_html_e( 'Mostrar más', 'etheme' ); ?>
						</button>
					</div>
				<?php endif; ?>

			<?php endif; ?>

		</div>

		<?php etheme_render_blog_card_modal(); ?>

	</section>

</div>
