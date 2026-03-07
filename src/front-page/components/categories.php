<?php
// home-categories — diseño Contrive.
/**
 * Home Categories Component
 *
 * Displays WooCommerce product categories as visual circular cards.
 * Layout: Tailwind grid. Visual styling: categories.scss.
 *
 * @param array $attributes Block attributes.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_home_categories( $attributes ) {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	$mode = $attributes['categoriesMode'];
	$ids  = array();

	if ( 'include' === $mode ) {
		$ids = $attributes['categoriesInclude'];
	} elseif ( 'exclude' === $mode ) {
		$ids = $attributes['categoriesExclude'];
	}

	$categories = etheme_get_home_categories( $mode, $ids );
	if ( empty( $categories ) ) {
		return;
	}
	?>

	<section class="categories-con" aria-labelledby="categories-heading">
		<div class="container mx-auto px-6 md:px-12 lg:px-20">

			<div class="categories_content text-center" data-aos="fade-up">
				<h6><?php esc_html_e( 'Categories', 'etheme' ); ?></h6>
				<h2 id="categories-heading"><?php esc_html_e( 'Shop By Categories', 'etheme' ); ?></h2>
			</div>

			<div class="categories_wrapper">
				<div class="flex flex-wrap justify-center gap-8" data-aos="fade-up">
					<?php foreach ( $categories as $cat ) :
						etheme_render_home_category_card( $cat );
					endforeach; ?>
				</div>
			</div>

		</div>
	</section>

	<?php
}

function etheme_render_home_category_card( $cat ) {
	$link         = get_term_link( $cat );
	$thumbnail_id = get_term_meta( $cat->term_id, 'thumbnail_id', true );
	?>

	<div class="categories-box w-full sm:w-[200px] md:w-[206px] lg:w-[255px] flex-shrink-0">
		<div class="image relative">
			<figure class="categories-image mb-0">
				<?php if ( $thumbnail_id ) :
					echo wp_get_attachment_image(
						$thumbnail_id,
						'medium_large',
						false,
						array(
							'class' => 'img-fluid',
							'alt'   => esc_attr( $cat->name ),
						)
					);
				else : ?>
					<div class="img-placeholder" aria-hidden="true"></div>
				<?php endif; ?>
			</figure>

			<a href="<?php echo esc_url( $link ); ?>"
			   class="icon"
			   aria-label="<?php echo esc_attr( sprintf( __( 'Ver categoría %s', 'etheme' ), $cat->name ) ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
					<path d="M5 12h14M12 5l7 7-7 7"/>
				</svg>
			</a>
		</div>

		<h4 class="mb-0"><?php echo esc_html( $cat->name ); ?></h4>
	</div>

	<?php
}
