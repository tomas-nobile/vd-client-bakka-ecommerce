<?php
// home-blog.
/**
 * Home Blog Component
 *
 * Displays the "News and Articles" section following the Contrive design.
 * Layout: centred header (h6 + h2), 3-card grid, "Ver más artículos" button.
 *
 * Desktop order: newest post in centre column (CSS order via Tailwind lg:order-*).
 * Mobile order:  first in DOM = first visible (no overriding order class).
 *
 * @param array $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_home_blog( $attributes ) {
	$count      = absint( $attributes['blogCount'] );
	$categories = $attributes['blogCategories'];
	$posts      = etheme_get_home_blog_posts( $count, $categories );

	if ( empty( $posts ) ) {
		return;
	}

	// Desktop column order: DOM [0]=newest → centre (order-2), [1]=second → left (order-1), [2]=third → right (order-3).
	$lg_order = array( 'lg:order-2', 'lg:order-1', 'lg:order-3' );

	$blog_url = get_option( 'page_for_posts' )
		? get_permalink( get_option( 'page_for_posts' ) )
		: home_url( '/blog/' );
	?>

	<section
		class="article-con"
		aria-labelledby="article-heading"
	>
		<div class="container mx-auto px-6 md:px-12 lg:px-20">

			<div class="article_content text-center" data-aos="fade-up">
				<h6><?php esc_html_e( 'Noticias y Artículos', 'etheme' ); ?></h6>
				<h2 id="article-heading"><?php esc_html_e( 'Nuestras Últimas Entradas', 'etheme' ); ?></h2>
			</div>

			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 article-cards-row" data-aos="fade-up">
				<?php foreach ( $posts as $index => $post ) :
					$order_class = isset( $lg_order[ $index ] ) ? $lg_order[ $index ] : '';
					?>
					<div class="article-card-col <?php echo esc_attr( $order_class ); ?>">
						<?php etheme_render_home_blog_card( $post ); ?>
					</div>
				<?php endforeach; ?>
			</div>

			<div class="article-con__more text-center mt-14" data-aos="fade-up">
				<a
					href="<?php echo esc_url( $blog_url ); ?>"
					class="primary_btn"
				>
					<?php esc_html_e( 'Ver más artículos', 'etheme' ); ?>
				</a>
			</div>

		</div>
	</section>

	<?php
}
