<?php
/**
 * Page Checkout Index - Main orchestrator for checkout page (2-step flow).
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

require_once get_template_directory() . '/src/page-checkout/includes/helpers.php';
require_once get_template_directory() . '/src/core/components/sub-banner.php';

$components_dir = get_template_directory() . '/src/page-checkout/components/';
$components     = array(
	'checkout-header',
	'contact-information',
	'shipping-address',
	'billing-address',
	'shipping-options',
	'order-notes',
	'order-summary',
	'payment-options',
	'place-order',
	'return-to-cart',
	'empty-checkout',
);

foreach ( $components as $component ) {
	$file = $components_dir . $component . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

$defaults   = array(
	'showOrderNotes'       => true,
	'showReturnToCart'     => true,
	'stickySummaryDesktop' => true,
	'bannerTitle'          => __( 'Finalizar compra', 'etheme' ),
);
$attributes = wp_parse_args( $attributes, $defaults );

$checkout = WC()->checkout();
$cart     = WC()->cart;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout ) {
	return;
}

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'Debes iniciar sesión para finalizar la compra.', 'woocommerce' ) ) );
	return;
}
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'page-checkout-block bg-white' ) ); ?>>
	<script>
		(function(){
			document.body.classList.add('etheme-checkout-page');
			function removePaymentPlaceOrder() {
				var payment = document.getElementById('payment');
				if (!payment) return;
				var placeOrder = payment.querySelector('.form-row.place-order') || payment.querySelector('.place-order');
				if (placeOrder) placeOrder.remove();
				payment.querySelectorAll('.woocommerce-terms-and-conditions-wrapper, .woocommerce-privacy-policy-text').forEach(function(el){ el.remove(); });
				payment.querySelectorAll('button#place_order, button.place_order, .payment_box button[type="submit"]').forEach(function(el){ el.remove(); });
			}
			if (document.readyState === 'loading') {
				document.addEventListener('DOMContentLoaded', removePaymentPlaceOrder);
			} else {
				removePaymentPlaceOrder();
			}
			setTimeout(removePaymentPlaceOrder, 500);
			setTimeout(removePaymentPlaceOrder, 1500);
		})();
	</script>

	<?php
	$checkout_banner_item_count = ( $cart && ! $cart->is_empty() ) ? $cart->get_cart_contents_count() : 0;
	$checkout_banner_subtitle    = sprintf(
		/* translators: %d: number of products (units) in the cart */
		_n(
			'%d producto en tu pedido',
			'%d productos en tu pedido',
			$checkout_banner_item_count,
			'etheme'
		),
		$checkout_banner_item_count
	);
	etheme_render_sub_banner(
		array(
			'title'    => $attributes['bannerTitle'],
			'subtitle' => $checkout_banner_subtitle,
		)
	);
	?>

	<div class="mx-auto max-w-7xl px-4 py-8 md:px-6 md:py-12 lg:px-8 lg:py-16">
		<?php wc_print_notices(); ?>

		<?php
		if ( $cart->is_empty() ) {
			if ( function_exists( 'etheme_render_checkout_empty_state' ) ) {
				etheme_render_checkout_empty_state();
			}
		} else {
			?>
			<?php if ( function_exists( 'etheme_render_checkout_header' ) ) : ?>
				<?php etheme_render_checkout_header(); ?>
			<?php endif; ?>

			<form
				name="checkout"
				method="post"
				class="checkout woocommerce-checkout"
				action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
				enctype="multipart/form-data"
				aria-label="<?php esc_attr_e( 'Formulario de compra', 'etheme' ); ?>"
			>
				<!-- ===== PASO 1: Datos y envío ===== -->
				<div
					id="checkout-step-panel-1"
					data-checkout-step="1"
					aria-label="<?php esc_attr_e( 'Paso 1: Datos y envío', 'etheme' ); ?>"
				>
					<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

					<div class="space-y-6">
						<?php if ( function_exists( 'etheme_render_checkout_contact_information' ) ) : ?>
							<?php etheme_render_checkout_contact_information( $checkout ); ?>
						<?php endif; ?>

						<?php if ( function_exists( 'etheme_render_checkout_shipping_address' ) ) : ?>
							<?php etheme_render_checkout_shipping_address( $checkout ); ?>
						<?php endif; ?>

						<?php if ( function_exists( 'etheme_render_checkout_billing_address' ) ) : ?>
							<?php etheme_render_checkout_billing_address( $checkout ); ?>
						<?php endif; ?>

						<?php if ( function_exists( 'etheme_render_checkout_shipping_options' ) ) : ?>
							<?php etheme_render_checkout_shipping_options(); ?>
						<?php endif; ?>

						<?php if ( $attributes['showOrderNotes'] && function_exists( 'etheme_render_checkout_order_notes' ) ) : ?>
							<?php etheme_render_checkout_order_notes( $checkout ); ?>
						<?php endif; ?>
					</div>

					<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>

					<div class="mt-8" data-aos="fade-up" data-aos-delay="100">
						<button
							type="button"
							id="checkout-btn-continue"
							class="checkout-btn-continue w-full bg-gray-900 px-6 py-4 text-center text-sm font-bold uppercase tracking-wide text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-50"
							disabled
							aria-disabled="true"
						>
							<?php esc_html_e( 'Continuar al pago', 'etheme' ); ?>
						</button>
					</div>
				</div>

				<!-- ===== PASO 2: Pago ===== -->
				<div
					id="checkout-step-panel-2"
					data-checkout-step="2"
					hidden
					aria-hidden="true"
					aria-label="<?php esc_attr_e( 'Paso 2: Pago', 'etheme' ); ?>"
				>
					<div class="mb-6">
						<button
							type="button"
							id="checkout-btn-back"
							class="inline-flex items-center gap-2 text-sm text-gray-500 underline transition hover:text-gray-800"
						>
							<svg class="h-4 w-4" viewBox="0 0 20 20" fill="none" aria-hidden="true">
								<path d="M12 5L7 10L12 15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
							</svg>
							<?php esc_html_e( 'Volver a datos y envío', 'etheme' ); ?>
						</button>
					</div>

					<div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(360px,420px)]">
						<div class="space-y-6">
							<?php if ( function_exists( 'etheme_render_checkout_payment_options' ) ) : ?>
								<?php etheme_render_checkout_payment_options(); ?>
							<?php endif; ?>
						</div>

						<aside class="space-y-6 <?php echo $attributes['stickySummaryDesktop'] ? 'lg:sticky lg:top-24 h-fit' : ''; ?>">
							<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

							<?php if ( function_exists( 'etheme_render_checkout_order_summary' ) ) : ?>
								<?php etheme_render_checkout_order_summary(); ?>
							<?php endif; ?>

							<?php if ( function_exists( 'etheme_render_checkout_place_order' ) ) : ?>
								<?php etheme_render_checkout_place_order(); ?>
							<?php endif; ?>

							<?php if ( $attributes['showReturnToCart'] && function_exists( 'etheme_render_checkout_return_to_cart' ) ) : ?>
								<?php etheme_render_checkout_return_to_cart(); ?>
							<?php endif; ?>

							<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
						</aside>
					</div>
				</div>
			</form>
			<?php
		}
		?>
	</div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
