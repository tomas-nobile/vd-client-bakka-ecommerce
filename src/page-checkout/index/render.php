<?php
/**
 * Page Checkout Index - Main orchestrator for checkout page.
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
		<p class="py-8 text-center text-gray-500"><?php esc_html_e( 'WooCommerce is required for this page.', 'etheme' ); ?></p>
	</div>
	<?php
	return;
}

require_once get_template_directory() . '/src/page-checkout/includes/helpers.php';

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
	'showOrderNotes'      => true,
	'showReturnToCart'    => true,
	'stickySummaryDesktop' => true,
);
$attributes = wp_parse_args( $attributes, $defaults );

$checkout = WC()->checkout();
$cart     = WC()->cart;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout ) {
	return;
}

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'page-checkout-block bg-white py-8 md:py-12 lg:py-16' ) ); ?>>
	<style id="etheme-checkout-fixes">
		.page-checkout-block #payment .form-row.place-order,
		.page-checkout-block #payment .place-order,
		.page-checkout-block #payment .woocommerce-terms-and-conditions-wrapper,
		.page-checkout-block #payment .woocommerce-privacy-policy-text { display: none !important; }
		.page-checkout-block #payment button#place_order,
		.page-checkout-block #payment button.place_order,
		.page-checkout-block #payment .payment_box button[type="submit"] { display: none !important; }
		body.woocommerce-checkout,
		body.etheme-checkout-page { overflow-x: hidden !important; }
		body.woocommerce-checkout .select2-dropdown,
		body.etheme-checkout-page .select2-dropdown { max-width: calc(100vw - 2rem) !important; }
		body.woocommerce-checkout .select2-results__options,
		body.etheme-checkout-page .select2-results__options { overflow-x: hidden !important; max-width: 100% !important; }
		/* Ocultar cartel "added to cart" y "Have a coupon?" */
		.woocommerce-notice-wrapper .wc-block-components-notice-banner.is-success,
		.woocommerce-form-coupon-toggle .wc-block-components-notice-banner { display: none !important; }
	</style>
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
	<div class="mx-auto max-w-7xl px-4 md:px-6 lg:px-8">
		<?php wc_print_notices(); ?>

		<?php
		if ( $cart->is_empty() ) {
			if ( function_exists( 'etheme_render_checkout_empty_state' ) ) {
				etheme_render_checkout_empty_state();
			}
		} else {
			?>
			<?php if ( function_exists( 'etheme_render_checkout_header' ) ) : ?>
				<?php etheme_render_checkout_header( $cart ); ?>
			<?php endif; ?>

			<form
				name="checkout"
				method="post"
				class="checkout woocommerce-checkout"
				action="<?php echo esc_url( wc_get_checkout_url() ); ?>"
				enctype="multipart/form-data"
				aria-label="<?php esc_attr_e( 'Checkout form', 'etheme' ); ?>"
			>
				<div class="grid gap-10 lg:grid-cols-[minmax(0,1fr)_minmax(360px,420px)]">
					<div class="space-y-8">
						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

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

						<?php if ( function_exists( 'etheme_render_checkout_payment_options' ) ) : ?>
							<?php etheme_render_checkout_payment_options(); ?>
						<?php endif; ?>

						<?php if ( $attributes['showOrderNotes'] && function_exists( 'etheme_render_checkout_order_notes' ) ) : ?>
							<?php etheme_render_checkout_order_notes( $checkout ); ?>
						<?php endif; ?>

						<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
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
			</form>
			<?php
		}
		?>
	</div>
</div>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>
