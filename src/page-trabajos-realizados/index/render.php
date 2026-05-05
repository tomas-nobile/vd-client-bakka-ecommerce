<?php
// page-trabajos-realizados-index.
/**
 * Page Posteos Index — Main renderer for /posteos
 *
 * Renders: sub-banner (with optional category filter chips), Instagram-style
 * card grid, shared modal, load-more button.
 * Posts are fetched via etheme_get_social_posts() with offset + category support.
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
	'postsPerPage'   => 15,
	'bannerTitle'    => __( 'Trabajos Realizados', 'etheme' ),
	'bannerSubtitle' => __( 'Explorá nuestros trabajos realizados.', 'etheme' ),
);

$attributes      = wp_parse_args( $attributes, $defaults );
$per_page        = max( 1, absint( $attributes['postsPerPage'] ) );
$active_category = isset( $_GET['categoria'] ) ? sanitize_key( $_GET['categoria'] ) : '';

$posts = etheme_get_social_posts( $per_page, 0, $active_category );

// Total for load-more — scoped to the active category when set.
$count_args = array(
	'post_type'      => 'social_post',
	'posts_per_page' => -1,
	'fields'         => 'ids',
	'post_status'    => 'publish',
	'no_found_rows'  => false,
);
if ( $active_category !== '' ) {
	$count_args['tax_query'] = array(
		array(
			'taxonomy' => 'posteo_category',
			'field'    => 'slug',
			'terms'    => $active_category,
		),
	);
}
$total_query = new WP_Query( $count_args );
$total_posts = $total_query->found_posts;

$ajaxurl  = esc_url( admin_url( 'admin-ajax.php' ) );
$nonce    = wp_create_nonce( 'etheme_posteos_load_more' );
$has_more = $total_posts > $per_page;

// Category filter chips — only rendered when taxonomy has terms.
$terms     = get_terms( array( 'taxonomy' => 'posteo_category', 'hide_empty' => false ) );
$has_terms = ! is_wp_error( $terms ) && ! empty( $terms );
?>

<div
	<?php echo get_block_wrapper_attributes( array(
		'data-ajaxurl'         => $ajaxurl,
		'data-nonce'           => $nonce,
		'data-active-category' => $active_category,
	) ); ?>
>

	<?php
	etheme_render_sub_banner( array(
		'title'         => $attributes['bannerTitle'],
		'subtitle'      => $attributes['bannerSubtitle'],
		'breadcrumbs'   => array(
			array(
				'label' => __( 'Inicio', 'etheme' ),
				'url'   => home_url( '/' ),
			),
			array(
				'label' => $attributes['bannerTitle'],
			),
		),
		'after_content' => $has_terms ? function() use ( $terms, $active_category ) {
			$all_active = ( $active_category === '' );
			echo '<div class="container mx-auto"><div class="posteos-filter" role="tablist" aria-label="' . esc_attr__( 'Filtrar por categoría', 'etheme' ) . '">';
			echo '<button class="posteos-filter__chip posteos-filter__chip--all' . ( $all_active ? ' posteos-filter__chip--active' : '' ) . '" data-category="" role="tab" aria-selected="' . ( $all_active ? 'true' : 'false' ) . '">' . esc_html__( 'Todos', 'etheme' ) . '</button>';
			foreach ( $terms as $term ) {
				$is_active = ( $active_category === $term->slug );
				echo '<button class="posteos-filter__chip' . ( $is_active ? ' posteos-filter__chip--active' : '' ) . '" data-category="' . esc_attr( $term->slug ) . '" role="tab" aria-selected="' . ( $is_active ? 'true' : 'false' ) . '">' . esc_html( $term->name ) . '</button>';
			}
			echo '</div></div>';
		} : null,
	) );
	?>

	<section class="posteos-con" aria-labelledby="posteos-heading">
		<div class="container mx-auto px-6 md:px-12 lg:px-20 py-16">

			<?php if ( empty( $posts ) ) : ?>
				<p class="text-center text-gray-500 py-8">
					<?php esc_html_e( 'No hay trabajos publicados todavía.', 'etheme' ); ?>
				</p>
			<?php else : ?>

				<div
					class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 article-cards-row fp-aos-visible"
					id="posteos-cards-grid"
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
							data-category="<?php echo esc_attr( $active_category ); ?>"
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
