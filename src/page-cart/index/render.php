<?php
/**
 * Page Cart Index - Main orchestrator for cart page
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
		<p class="text-center text-gray-500 py-8"><?php esc_html_e( 'WooCommerce is required for this page.', 'etheme' ); ?></p>
	</div>
	<?php
	return;
}

require_once get_template_directory() . '/src/page-cart/includes/helpers.php';

$components_dir = get_template_directory() . '/src/page-cart/components/';
$components = array( 'header-cart', 'product-cart', 'postal-code-shipping', 'coupon-form', 'basket-totals', 'checkout-actions', 'empty-cart' );

foreach ( $components as $component ) {
	$file = $components_dir . $component . '.php';
	if ( file_exists( $file ) ) {
		require_once $file;
	}
}

$defaults = array(
	'showShippingCalculator' => true,
	'showCouponForm'         => true,
	'showContinueShopping'   => true,
);
$attributes = wp_parse_args( $attributes, $defaults );

$cart = WC()->cart;
$cart_items = $cart->get_cart();
$is_empty = $cart->is_empty();
$cart_nonce = etheme_get_cart_nonce();
?>

<div <?php echo get_block_wrapper_attributes( array( 'class' => 'page-cart-block py-6 md:py-12 lg:py-24 bg-white lg:bg-gray-50' ) ); ?>
	 data-cart-nonce="<?php echo esc_attr( $cart_nonce ); ?>"
	 data-ajax-url="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">

	<!-- Inline styles -->
	<style>
	.page-cart-block .qty-input{-moz-appearance:textfield!important;-webkit-appearance:none!important;appearance:none!important}
	.page-cart-block .qty-input::-webkit-outer-spin-button,.page-cart-block .qty-input::-webkit-inner-spin-button{-webkit-appearance:none!important;margin:0;display:none!important}
	.page-cart-block .qty-btn{cursor:pointer}
	.page-cart-block .qty-btn:disabled{cursor:not-allowed;opacity:.4}
	</style>

	<div class="container mx-auto px-4">
		<div class="max-w-7xl mx-auto">

			<!-- Cart Header -->
			<?php
			if ( function_exists( 'etheme_render_cart_header' ) ) {
				etheme_render_cart_header( $cart );
			}
			?>

			<?php if ( $is_empty ) : ?>
				<?php
				if ( function_exists( 'etheme_render_empty_cart' ) ) {
					etheme_render_empty_cart();
				}
				?>
			<?php else : ?>

				<!-- 
					Mobile: single column, items first, summary below, floating CTA at bottom.
					Desktop: 2 cols, items left, sticky sidebar right.
				-->
				<div class="cart-content lg:grid lg:grid-cols-[1fr_380px] lg:gap-12">

					<!-- Cart Items -->
					<div class="cart-items-wrapper" id="cart-items-container">
						<?php
						foreach ( $cart_items as $cart_item_key => $cart_item ) {
							if ( function_exists( 'etheme_render_product_cart' ) ) {
								etheme_render_product_cart( $cart_item, $cart_item_key );
							}
						}
						?>
					</div>

					<!-- Sidebar (summary) -->
					<div class="cart-sidebar mt-8 lg:mt-0">
						<div class="lg:sticky lg:top-24">

							<!-- Summary title (visible on mobile too) -->
							<h2 class="text-lg font-bold text-gray-900 mb-4 pb-3 border-b border-gray-200">
								<?php esc_html_e( 'Order Summary', 'etheme' ); ?>
							</h2>

							<!-- Shipping Calculator -->
							<?php
							if ( $attributes['showShippingCalculator'] && function_exists( 'etheme_render_postal_code_shipping' ) ) {
								etheme_render_postal_code_shipping();
							}
							?>

							<!-- Coupon Form -->
							<?php
							if ( $attributes['showCouponForm'] && function_exists( 'etheme_render_coupon_form' ) ) {
								etheme_render_coupon_form( $cart );
							}
							?>

							<!-- Basket Totals -->
							<?php
							if ( function_exists( 'etheme_render_basket_totals' ) ) {
								etheme_render_basket_totals( $cart );
							}
							?>

							<!-- Checkout Actions -->
							<?php
							if ( function_exists( 'etheme_render_checkout_actions' ) ) {
								etheme_render_checkout_actions( $attributes['showContinueShopping'] );
							}
							?>

						</div>
					</div>

				</div>

				<!-- Bottom padding for mobile floating button -->
				<div class="h-20 lg:hidden"></div>

			<?php endif; ?>

		</div>
	</div>

	<?php if ( ! $is_empty ) : ?>
	<!-- Cart interactivity script -->
	<script>
	(function() {
		var debounceTimers = {};
		var container = document.getElementById('cart-items-container');
		if (!container) return;

		container.addEventListener('click', function(e) {
			var btn = e.target.closest('.qty-btn');
			if (btn) { e.preventDefault(); handleQtyClick(btn); return; }
			var removeBtn = e.target.closest('.remove-item');
			if (removeBtn) { e.preventDefault(); handleRemoveClick(removeBtn); }
		});

		function handleQtyClick(btn) {
			var sel = btn.closest('.quantity-selector');
			if (!sel) return;
			var input = sel.querySelector('.qty-input');
			if (!input) return;
			var key = sel.getAttribute('data-cart-item-key');
			var cur = parseInt(input.value, 10) || 1;
			var min = parseInt(sel.getAttribute('data-min'), 10) || 1;
			var ma = sel.getAttribute('data-max');
			var max = (ma && ma !== '') ? parseInt(ma, 10) : 9999;
			var nq = btn.classList.contains('qty-increase') ? cur + 1 : cur - 1;
			if (nq < min) nq = min;
			if (nq > max) nq = max;
			if (nq === cur) return;
			input.value = nq;
			var db = sel.querySelector('.qty-decrease');
			var ib = sel.querySelector('.qty-increase');
			if (db) db.disabled = (nq <= min);
			if (ib) ib.disabled = (nq >= max);
			if (debounceTimers[key]) clearTimeout(debounceTimers[key]);
			debounceTimers[key] = setTimeout(function() { ajaxUpdate(key, nq); }, 500);
		}

		function handleRemoveClick(btn) {
			var key = btn.getAttribute('data-cart-item-key');
			var item = btn.closest('.cart-item');
			if (!item || !key) return;
			item.style.opacity = '0.4'; item.style.pointerEvents = 'none';
			var bl = document.querySelector('.page-cart-block');
			var url = bl ? bl.getAttribute('data-ajax-url') : '/wp-admin/admin-ajax.php';
			var n = bl ? bl.getAttribute('data-cart-nonce') : '';
			var fd = new FormData();
			fd.append('action','etheme_remove_cart_item'); fd.append('cart_item_key',key); fd.append('nonce',n);
			fetch(url,{method:'POST',body:fd}).then(function(r){return r.json()}).then(function(d){
				if(d.success){
					item.style.transition='all 0.3s'; item.style.opacity='0'; item.style.maxHeight=item.offsetHeight+'px';
					setTimeout(function(){item.style.maxHeight='0';item.style.padding='0';item.style.margin='0';item.style.overflow='hidden'},50);
					setTimeout(function(){item.remove();if(!document.querySelector('.cart-item'))window.location.reload()},350);
					if(d.data&&d.data.cart_totals)updateTotals(d.data.cart_totals);
				} else { item.style.opacity='1'; item.style.pointerEvents=''; }
			});
		}

		function ajaxUpdate(key, qty) {
			var bl = document.querySelector('.page-cart-block');
			var url = bl ? bl.getAttribute('data-ajax-url') : '/wp-admin/admin-ajax.php';
			var n = bl ? bl.getAttribute('data-cart-nonce') : '';
			var item = document.querySelector('.cart-item[data-cart-item-key="'+key+'"]');
			if (item) item.style.opacity='0.6';
			var fd = new FormData();
			fd.append('action','etheme_update_cart_item'); fd.append('cart_item_key',key); fd.append('quantity',qty); fd.append('nonce',n);
			fetch(url,{method:'POST',body:fd}).then(function(r){return r.json()}).then(function(d){
				if(d.success){
					var lt=item?item.querySelector('.line-total'):null;
					if(lt&&d.data.line_total_html)lt.innerHTML=d.data.line_total_html;
					if(d.data.cart_totals)updateTotals(d.data.cart_totals);
					var badge=document.querySelector('.cart-badge,.cart-count');
					if(badge&&d.data.cart_count!==undefined)badge.textContent=d.data.cart_count;
				}
			}).catch(function(e){console.error('Cart error:',e)}).finally(function(){if(item)item.style.opacity='1'});
		}

		function updateTotals(t) {
			var s=document.querySelector('.subtotal-value');
			var sh=document.querySelector('.shipping-value');
			var tv=document.querySelector('.total-value');
			if(s&&t.subtotal_html)s.innerHTML=t.subtotal_html;
			if(sh&&t.shipping_html)sh.innerHTML=t.shipping_html;
			if(tv&&t.total_html)tv.innerHTML=t.total_html;
		}
	})();
	</script>
	<?php endif; ?>
</div>
