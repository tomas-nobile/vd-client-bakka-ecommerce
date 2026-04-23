<?php
/**
 * Order Received (Thank You) — main orchestrator.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
	?>
	<div <?php echo get_block_wrapper_attributes(); ?>>
		<p class="py-8 text-center text-gray-500"><?php esc_html_e( 'WooCommerce es requerido para esta página.', 'etheme' ); ?></p>
	</div>
	<?php
	return;
}

$components_dir = get_template_directory() . '/src/order-received/components/';
$components     = array(
	'hero',
	'order-summary',
	'order-items',
	'order-addresses',
	'order-actions',
	'order-not-found',
);

foreach ( $components as $component ) {
	$file = $components_dir . $component . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

$order_id = absint( get_query_var( 'order-received' ) );
$order    = $order_id ? wc_get_order( $order_id ) : false;

$provided_key = isset( $_GET['key'] ) ? sanitize_text_field( wp_unslash( $_GET['key'] ) ) : '';

$has_access = false;
if ( $order instanceof WC_Order ) {
	if ( ! empty( $provided_key ) && hash_equals( (string) $order->get_order_key(), $provided_key ) ) {
		$has_access = true;
	} elseif ( is_user_logged_in() && current_user_can( 'view_order', $order_id ) ) {
		$has_access = true;
	}
}
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'order-received-block bg-white' ) ); ?>>
	<div class="mx-auto max-w-3xl px-4 py-12 md:px-6 md:py-20 lg:py-28">
		<?php
		if ( ! $has_access ) {
			if ( function_exists( 'etheme_render_order_not_found' ) ) {
				etheme_render_order_not_found();
			}
		} else {
			if ( function_exists( 'etheme_render_order_received_hero' ) ) {
				etheme_render_order_received_hero( $order );
			}

			if ( function_exists( 'etheme_render_order_received_summary' ) ) {
				etheme_render_order_received_summary( $order );
			}

			if ( function_exists( 'etheme_render_order_received_items' ) ) {
				etheme_render_order_received_items( $order );
			}

			if ( function_exists( 'etheme_render_order_received_addresses' ) ) {
				etheme_render_order_received_addresses( $order );
			}

			if ( function_exists( 'etheme_render_order_received_actions' ) ) {
				etheme_render_order_received_actions( $order );
			}
		}
		?>
	</div>
</div>
