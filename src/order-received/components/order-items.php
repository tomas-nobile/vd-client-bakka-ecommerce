<?php
/**
 * Order Received — Order Items component.
 *
 * @param WC_Order $order Order instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'etheme_render_order_received_items' ) ) {
	function etheme_render_order_received_items( $order ) {
		if ( ! $order instanceof WC_Order ) {
			return;
		}

		$items = $order->get_items();
		if ( empty( $items ) ) {
			return;
		}
		?>
		<section class="order-received-items" data-aos="fade-up" data-aos-delay="150">
			<h2 class="order-received-items__title">
				<?php esc_html_e( 'Productos', 'etheme' ); ?>
			</h2>
			<ul class="order-received-items__list">
				<?php foreach ( $items as $item ) : ?>
					<?php
					if ( ! ( $item instanceof WC_Order_Item_Product ) ) {
						continue;
					}
					$product       = $item->get_product();
					$name          = $item->get_name();
					$quantity      = $item->get_quantity();
					$subtotal_html = wc_price( $order->get_line_subtotal( $item, true, true ), array( 'currency' => $order->get_currency() ) );
					$thumbnail     = $product ? $product->get_image( 'woocommerce_thumbnail', array( 'class' => 'order-received-items__thumb-img' ) ) : '';
					?>
					<li class="order-received-items__item">
						<div class="order-received-items__thumb">
							<?php echo wp_kses_post( $thumbnail ); ?>
						</div>
						<div class="order-received-items__body">
							<p class="order-received-items__name"><?php echo esc_html( $name ); ?></p>
							<p class="order-received-items__meta">
								<?php
								printf(
									/* translators: %d: quantity */
									esc_html__( 'Cantidad: %d', 'etheme' ),
									(int) $quantity
								);
								?>
							</p>
						</div>
						<div class="order-received-items__subtotal">
							<?php echo wp_kses_post( $subtotal_html ); ?>
						</div>
					</li>
				<?php endforeach; ?>
			</ul>
		</section>
		<?php
	}
}
