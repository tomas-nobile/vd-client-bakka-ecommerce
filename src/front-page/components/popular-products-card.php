<?php
// popular-products-card.
/**
 * Popular Products Card Component
 *
 * Renders a single product card for the Popular Products section:
 * product image, dynamic color dots (from WooCommerce attributes/variations),
 * title link, and formatted price.
 *
 * No overlay icons or wishlist heart in this phase.
 *
 * @param WC_Product $product WooCommerce product object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_home_popular_product_card( $product ) {
	$permalink   = $product->get_permalink();
	$color_dots  = etheme_get_product_color_dots_with_images( $product );
	$main_id     = $product->get_image_id();
	$default_src = $main_id ? wp_get_attachment_image_url( $main_id, 'woocommerce_thumbnail' ) : '';
	$default_srcset = $main_id ? wp_get_attachment_image_srcset( $main_id, 'woocommerce_thumbnail' ) : '';
	?>
	<div class="pp-feature-box"
		<?php if ( $default_src ) : ?>
			data-default-src="<?php echo esc_url( $default_src ); ?>"
			data-default-srcset="<?php echo esc_attr( $default_srcset ?: '' ); ?>"
		<?php endif; ?>
	>
		<div class="pp-feature-box__image">
			<?php etheme_render_pp_card_image( $main_id, $product->is_on_sale() ); ?>
		</div>
		<div class="pp-feature-box__lower">
			<div class="pp-feature-box__top">
				<?php etheme_render_pp_color_dots( $color_dots ); ?>
			</div>
			<a href="<?php echo esc_url( $permalink ); ?>" class="pp-feature-box__title-link">
				<h4 class="pp-feature-box__title"><?php echo esc_html( $product->get_name() ); ?></h4>
			</a>
			<div class="pp-feature-box__price">
				<?php echo wp_kses_post( $product->get_price_html() ); ?>
			</div>
		</div>
	</div>
	<?php
}

function etheme_render_pp_card_image( $thumbnail_id, $on_sale ) {
	?>
	<figure class="pp-feature-box__figure">
		<?php if ( $on_sale ) : ?>
			<span class="pp-sale-badge"><?php esc_html_e( 'Oferta', 'etheme' ); ?></span>
		<?php endif; ?>
		<?php if ( $thumbnail_id ) {
			echo wp_get_attachment_image(
				$thumbnail_id,
				'woocommerce_thumbnail',
				false,
				array( 'class' => 'pp-feature-box__img' )
			);
		} else {
			echo wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'pp-feature-box__img' ) );
		} ?>
	</figure>
	<?php
}

function etheme_render_pp_color_dots( $color_items ) {
	if ( empty( $color_items ) ) {
		return;
	}
	echo '<div class="pp-color-wrap">';
	foreach ( $color_items as $item ) {
		$color = isset( $item['color'] ) ? $item['color'] : $item;
		$url   = isset( $item['image_url'] ) ? $item['image_url'] : null;
		$srcset = isset( $item['image_srcset'] ) ? $item['image_srcset'] : null;
		$attrs = sprintf(
			'class="pp-color-dot" style="--dot-color:%1$s;background-color:%1$s;" aria-label="%2$s"',
			esc_attr( $color ),
			esc_attr( $color )
		);
		if ( $url ) {
			$attrs .= ' data-src="' . esc_url( $url ) . '"';
			if ( $srcset ) {
				$attrs .= ' data-srcset="' . esc_attr( $srcset ) . '"';
			}
		}
		echo '<span ' . $attrs . '></span>';
	}
	echo '</div>';
}
