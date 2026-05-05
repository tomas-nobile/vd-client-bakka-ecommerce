<?php
/**
 * Product Card Component — Core (shared by Home, Archive, Related Products)
 *
 * Canonical pp-feature-box card: image, sale badge, color dots, title, price.
 * Supports full-link overlay mode ($full_card_link = true) used in Archive and
 * Related Products, where the entire card surface is clickable.
 *
 * @param WC_Product $product        WooCommerce product object.
 * @param bool       $full_card_link When true, renders a full-card overlay anchor.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure color-dots helper is available before any card render call.
if ( ! function_exists( 'etheme_get_product_color_dots_with_images' ) ) {
	require_once get_template_directory() . '/src/front-page/includes/front-page-index.helpers.php';
}

if ( ! function_exists( 'etheme_render_home_popular_product_card' ) ) :

	function etheme_render_home_popular_product_card( $product, $full_card_link = false ) {
		$permalink      = $product->get_permalink();
		$color_dots     = etheme_get_product_color_dots_with_images( $product );
		$main_id        = $product->get_image_id();
		$default_src    = $main_id ? wp_get_attachment_image_url( $main_id, 'woocommerce_thumbnail' ) : '';
		$default_srcset = $main_id ? wp_get_attachment_image_srcset( $main_id, 'woocommerce_thumbnail' ) : '';
		?>
		<div class="pp-feature-box<?php echo $full_card_link ? ' pp-feature-box--full-link' : ''; ?>"
			<?php if ( $default_src ) : ?>
				data-default-src="<?php echo esc_url( $default_src ); ?>"
				data-default-srcset="<?php echo esc_attr( $default_srcset ?: '' ); ?>"
			<?php endif; ?>
		>
			<?php if ( $full_card_link ) : ?>
				<a
					href="<?php echo esc_url( $permalink ); ?>"
					class="pp-feature-box__card-link"
					aria-label="<?php echo esc_attr( sprintf( __( 'Ver producto: %s', 'etheme' ), $product->get_name() ) ); ?>"
				></a>
			<?php endif; ?>
			<div class="pp-feature-box__image">
				<?php etheme_render_pp_card_image( $main_id, $product->is_on_sale(), $product->get_name() ); ?>
			</div>
			<div class="pp-feature-box__lower">
				<div class="pp-feature-box__top">
					<?php etheme_render_pp_color_dots( $color_dots ); ?>
				</div>
				<?php if ( $full_card_link ) : ?>
					<h4 class="pp-feature-box__title"><?php echo esc_html( $product->get_name() ); ?></h4>
				<?php else : ?>
					<a href="<?php echo esc_url( $permalink ); ?>" class="pp-feature-box__title-link">
						<h4 class="pp-feature-box__title"><?php echo esc_html( $product->get_name() ); ?></h4>
					</a>
				<?php endif; ?>
				<div class="pp-feature-box__price">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				</div>
			</div>
		</div>
		<?php
	}

endif;

if ( ! function_exists( 'etheme_render_pp_card_image' ) ) :

	function etheme_render_pp_card_image( $thumbnail_id, $on_sale, $alt = '' ) {
		?>
		<figure class="pp-feature-box__figure">
			<?php if ( $on_sale ) : ?>
				<span class="pp-sale-badge"><?php esc_html_e( 'Oferta', 'etheme' ); ?></span>
			<?php endif; ?>
			<?php
			if ( $thumbnail_id ) {
				$attr = array(
					'class'    => 'pp-feature-box__img',
					'loading'  => 'lazy',
					'decoding' => 'async',
				);
				if ( '' !== $alt ) {
					$attr['alt'] = $alt;
				}
				echo wp_get_attachment_image( $thumbnail_id, 'woocommerce_thumbnail', false, $attr );
			} else {
				echo wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'pp-feature-box__img', 'loading' => 'lazy' ) );
			}
			?>
		</figure>
		<?php
	}

endif;

if ( ! function_exists( 'etheme_render_pp_color_dots' ) ) :

	function etheme_render_pp_color_dots( $color_items ) {
		if ( empty( $color_items ) ) {
			return;
		}
		echo '<div class="pp-color-wrap">';
		foreach ( $color_items as $item ) {
			$color  = isset( $item['color'] ) ? $item['color'] : $item;
			$color2 = isset( $item['color2'] ) ? $item['color2'] : null;
			$url    = isset( $item['image_url'] ) ? $item['image_url'] : null;
			$srcset = isset( $item['image_srcset'] ) ? $item['image_srcset'] : null;
			if ( $color2 ) {
				$attrs = sprintf(
					'class="pp-color-dot pp-color-dot--split" style="--dot-color1:%1$s;--dot-color2:%2$s;--dot-color:%1$s;" aria-label="%1$s / %2$s"',
					esc_attr( $color ),
					esc_attr( $color2 )
				);
			} else {
				$attrs = sprintf(
					'class="pp-color-dot" style="--dot-color:%1$s;background-color:%1$s;" aria-label="%2$s"',
					esc_attr( $color ),
					esc_attr( $color )
				);
			}
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

endif;
