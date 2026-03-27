<?php
/**
 * Product Tabs Component
 *
 * Renders product tabs (description, additional info, reviews).
 *
 * @param WC_Product $product WooCommerce product object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_product_tabs( $product ) {
	$tabs = etheme_get_product_tab_sections( $product );

	if ( empty( $tabs ) ) {
		return;
	}
	?>
	
	<div class="product-tabs border-t border-b border-gray-200 mt-12" id="product-tabs">
	<?php foreach ( $tabs as $tab_id => $tab ) : ?>
		<?php etheme_render_product_tab_section( $product, $tab_id, $tab, false, $tab_id === array_key_last( $tabs ) ); ?>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Get product tab sections for accordion.
 *
 * @param WC_Product $product WooCommerce product object.
 * @return array
 */
function etheme_get_product_tab_sections( $product ) {
	$tabs = array();
	$long_description = trim( (string) $product->get_description() );

	if ( '' !== $long_description ) {
		$tabs['description'] = array(
			'title'   => __( 'Descripción', 'etheme' ),
			'content' => $long_description,
			'type'    => 'content',
		);
	}

	$tabs['reviews'] = array(
		'title'   => __( 'Reseñas', 'etheme' ),
		'content' => 'reviews',
		'type'    => 'reviews',
	);

	$tabs['shipping_returns'] = array(
		'title'   => __( 'Envíos y devoluciones', 'etheme' ),
		'content' => etheme_get_shipping_returns_content(),
		'type'    => 'content',
	);

	return array_filter( $tabs );
}

/**
 * Render a single accordion section.
 *
 * @param WC_Product $product WooCommerce product object.
 * @param string     $tab_id  Section ID.
 * @param array      $tab     Section data.
 * @param bool       $is_open Whether the section is open.
 * @param bool       $is_last Whether the section is the last one.
 * @return void
 */
function etheme_render_product_tab_section( $product, $tab_id, $tab, $is_open, $is_last ) {
	$panel_id = 'panel-' . $tab_id;
	$border_class = $is_last ? '' : 'border-b border-gray-200';
	?>
	<div class="accordion-item <?php echo esc_attr( $border_class ); ?>" data-accordion-item>
		<div class="py-4 px-0">
			<div class="flex items-center flex-wrap justify-between gap-4 cursor-pointer" data-accordion-trigger>
				<p class="uppercase text-gray-600 font-semibold text-[11px] tracking-[0.2em]">
					<?php echo esc_html( $tab['title'] ); ?>
				</p>
				<span class="inline-block transform <?php echo $is_open ? 'rotate-180' : ''; ?>" data-accordion-icon>
					<svg xmlns="http://www.w3.org/2000/svg" width="24" height="25" viewBox="0 0 24 25" fill="none">
						<path d="M12.2725 16.1666C12.1769 16.1667 12.0822 16.1479 11.9939 16.1113C11.9055 16.0747 11.8253 16.021 11.7578 15.9533L6.21332 10.4092C6.07681 10.2727 6.00012 10.0876 6.00012 9.89454C6.00012 9.70149 6.07681 9.51635 6.21332 9.37984C6.34983 9.24333 6.53497 9.16665 6.72802 9.16665C6.92107 9.16665 7.10621 9.24334 7.24271 9.37984L12.2725 14.4092L17.3023 9.37982C17.4388 9.24332 17.6239 9.16663 17.817 9.16663C18.01 9.16663 18.1952 9.24331 18.3317 9.37982C18.4682 9.51632 18.5449 9.70147 18.5449 9.89452C18.5449 10.0876 18.4682 10.2727 18.3317 10.4092L12.7872 15.9534C12.7197 16.0211 12.6394 16.0748 12.5511 16.1114C12.4628 16.148 12.3681 16.1667 12.2725 16.1666Z" fill="#A0A5B8" />
					</svg>
				</span>
			</div>
			<div class="accordion-content overflow-hidden transition-all duration-500 <?php echo $is_open ? '' : 'h-0'; ?>" data-accordion-content id="<?php echo esc_attr( $panel_id ); ?>">
				<div class="text-gray-600 leading-7 text-sm mt-3">
					<?php if ( $tab['content'] === 'reviews' ) : ?>
						<?php etheme_render_product_reviews( $product ); ?>
					<?php else : ?>
						<?php echo wp_kses_post( wpautop( $tab['content'] ) ); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Shipping and returns content.
 *
 * @return string
 */
function etheme_get_shipping_returns_content() {
	$default = __( 'Los tiempos de envío y las políticas de devolución dependen de tu ubicación y del método de envío seleccionado. Podés devolver los productos elegibles dentro de los 30 días desde la entrega.', 'etheme' );

	return apply_filters( 'etheme_shipping_returns_content', $default );
}

/**
 * Render product attributes table
 *
 * @param WC_Product $product WooCommerce product object.
 */
function etheme_render_product_attributes( $product ) {
	$attributes = $product->get_attributes();
	
	if ( empty( $attributes ) ) {
		return;
	}
	?>
	
	<table class="w-full text-sm">
		<tbody>
			<?php foreach ( $attributes as $attribute ) : 
				if ( ! $attribute->get_visible() ) {
					continue;
				}
				
				$attribute_name = wc_attribute_label( $attribute->get_name() );
				$attribute_values = array();
				
				if ( $attribute->is_taxonomy() ) {
					$terms = wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) );
					$attribute_values = $terms;
				} else {
					$attribute_values = $attribute->get_options();
				}
			?>
			<tr class="border-b border-gray-100">
				<th class="py-3 pr-4 text-left font-medium text-gray-700 w-1/3">
					<?php echo esc_html( $attribute_name ); ?>
				</th>
				<td class="py-3 text-gray-600">
					<?php echo esc_html( implode( ', ', $attribute_values ) ); ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<?php
}

/**
 * Render product reviews
 *
 * @param WC_Product $product WooCommerce product object.
 */
function etheme_render_product_reviews( $product ) {
	$product_id = $product->get_id();
	
	// Use WooCommerce's built-in review template
	if ( function_exists( 'comments_template' ) ) {
		// Set up the global post for comments
		global $post;
		$original_post = $post;
		$post = get_post( $product_id );
		setup_postdata( $post );
		
		comments_template();
		
		// Restore original post
		$post = $original_post;
		if ( $original_post ) {
			setup_postdata( $original_post );
		} else {
			wp_reset_postdata();
		}
	}
}
