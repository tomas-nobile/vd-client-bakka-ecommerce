<?php
/**
 * Add to Cart Component
 *
 * Renders quantity selector and add to cart button.
 *
 * @param WC_Product $product WooCommerce product object.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_add_to_cart( $product ) {
	if ( ! $product->is_purchasable() ) {
		return;
	}
	
	$is_variable = $product->is_type( 'variable' );
	$is_in_stock = $product->is_in_stock();
	$min_qty = $product->get_min_purchase_quantity();
	$max_qty = $product->get_max_purchase_quantity();
	$step = 1;
	
	// For variable products, button starts disabled until variation is selected
	$button_disabled = $is_variable || ! $is_in_stock;
	$button_text = $is_variable ? __( 'Seleccionar opciones', 'etheme' ) : __( 'Agregar al carrito', 'etheme' );
	
	if ( ! $is_in_stock && ! $is_variable ) {
		$button_text = __( 'Sin stock', 'etheme' );
	}

	$form_attr = $is_variable ? ' form="variations-form"' : '';

	$purchase_notices = array(
		array(
			'icon'  => 'assets/icons/lock-alt-svgrepo-com.svg',
			'title' => __( 'Compra protegida', 'etheme' ),
			'text'  => __( 'Tus datos cuidados durante toda la compra.', 'etheme' ),
		),
		array(
			'icon'  => 'assets/icons/social-media/mercadopago-2.svg',
			'title' => __( 'MercadoPago', 'etheme' ),
			'text'  => __( 'Pagos con tarjeta de crédito, débito y saldo en billetera son procesados por MercadoPago.', 'etheme' ),
		),
	);
	?>
	
	<div class="add-to-cart-wrapper mt-8 w-full max-w-full">

		<?php
		// Static color display: simple products, or variable with no variation attributes configured.
		$variation_attributes = $is_variable ? $product->get_variation_attributes() : array();
		if ( ! $is_variable || empty( $variation_attributes ) ) {
			if ( ! function_exists( 'etheme_get_product_color_label_data' ) ) {
				require_once get_template_directory() . '/src/front-page/includes/front-page-index.helpers.php';
			}
			$color_data = etheme_get_product_color_label_data( $product );
			if ( $color_data ) :
				?>
				<div class="product-color-display mb-3">
					<div class="flex items-center w-full py-3 px-4 border border-gray-200 rounded-sm gap-4">
						<span class="text-sm font-medium text-gray-700 leading-none">
							<?php echo esc_html( $color_data['label'] ); ?>
						</span>
						<div class="ml-auto flex items-center gap-3 flex-wrap">
							<?php
							$is_bicolor = ! empty( $color_data['color2'] ) && ! empty( $color_data['values'][0]['color'] );
							if ( $is_bicolor ) :
								$c1 = $color_data['values'][0]['color'];
								$c2 = $color_data['color2'];
								$l1 = $color_data['values'][0]['label'];
								$l2 = $color_data['label2'];
								?>
								<span class="flex items-center gap-1.5 text-sm font-medium text-gray-800">
									<span class="inline-block w-3 h-3 rounded-full flex-shrink-0"
										style="background:linear-gradient(135deg,<?php echo esc_attr( $c1 ); ?> 50%,<?php echo esc_attr( $c2 ); ?> 50%);box-shadow:inset 0 0 0 1px rgba(0,0,0,0.15);"></span>
									<?php echo esc_html( $l1 . ' y ' . $l2 ); ?>
								</span>
							<?php else : ?>
								<?php foreach ( $color_data['values'] as $color_value ) : ?>
									<span class="flex items-center gap-1.5 text-sm font-medium text-gray-800">
										<?php if ( $color_value['color'] ) : ?>
											<span class="inline-block w-3 h-3 rounded-full flex-shrink-0"
												style="background-color:<?php echo esc_attr( $color_value['color'] ); ?>;box-shadow:inset 0 0 0 1px rgba(0,0,0,0.15);"></span>
										<?php endif; ?>
										<?php echo esc_html( $color_value['label'] ); ?>
									</span>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			endif;
		}
		?>

		<?php if ( ! $is_variable ) : ?>
		<form class="cart" method="post" enctype="multipart/form-data" id="add-to-cart-form">
		<?php endif; ?>
		
		<?php if ( $is_in_stock || $is_variable ) : ?>
			
		<!-- Quantity + Add to Cart -->
		<div class="quantity-wrapper flex flex-col items-stretch gap-3 mb-6 w-full max-w-full">
			<div class="w-full">
				<?php
				$stock_qty      = $product->get_stock_quantity();
				$dropdown_limit = 6;
				$options_count  = $max_qty > 0 ? min( $dropdown_limit, $max_qty ) : $dropdown_limit;
				$show_more      = $max_qty <= 0 || $max_qty > $dropdown_limit;
				$stock_label    = ( $stock_qty && $stock_qty > $dropdown_limit )
					? sprintf( '(+%d disponibles)', $stock_qty )
					: '';
				$initial_label  = $min_qty === 1
					? sprintf( '%d unidad', $min_qty )
					: sprintf( '%d unidades', $min_qty );
				?>
				<div class="quantity-ml-selector relative w-full">

					<button type="button"
							class="quantity-ml-trigger flex items-center w-full py-3 px-4 border border-gray-200 rounded-sm bg-white hover:border-gray-400 transition-colors duration-200 cursor-pointer"
							aria-haspopup="listbox"
							aria-expanded="false">
						<span class="text-sm text-gray-500 leading-none"><?php esc_html_e( 'Cantidad:', 'etheme' ); ?>&nbsp;</span>
						<span class="quantity-ml-display font-semibold text-sm text-gray-800 leading-none"><?php echo esc_html( $initial_label ); ?></span>
						<?php if ( $stock_label ) : ?>
							<span class="quantity-ml-stock text-sm text-gray-400 ml-2 leading-none"><?php echo esc_html( $stock_label ); ?></span>
						<?php endif; ?>
						<svg class="quantity-ml-chevron ml-auto flex-shrink-0 text-gray-500 transition-transform duration-200" width="12" height="8" viewBox="0 0 12 8" fill="none" aria-hidden="true">
							<path d="M1 1.5L6 6.5L11 1.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
						</svg>
					</button>

					<div class="quantity-ml-dropdown hidden absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-sm shadow-lg z-20 mt-1 overflow-hidden"
						 role="listbox">
						<?php for ( $i = $min_qty; $i <= $options_count; $i++ ) :
							$opt_label = $i === 1 ? sprintf( '%d unidad', $i ) : sprintf( '%d unidades', $i );
						?>
							<button type="button"
									class="quantity-ml-option w-full text-left px-4 py-3 text-sm text-gray-800 hover:bg-gray-50 transition-colors duration-150"
									data-value="<?php echo esc_attr( $i ); ?>"
									role="option"
									aria-selected="<?php echo $i === $min_qty ? 'true' : 'false'; ?>">
								<?php echo esc_html( $opt_label ); ?>
							</button>
						<?php endfor; ?>

						<?php if ( $show_more ) : ?>
							<div class="h-px bg-gray-100"></div>
							<button type="button"
									class="quantity-ml-option quantity-ml-more w-full text-left px-4 py-3 text-sm text-gray-800 hover:bg-gray-50 transition-colors duration-150"
									data-value="more"
									role="option">
								<?php printf( esc_html__( 'Más de %d unidades', 'etheme' ), $dropdown_limit ); ?>
							</button>
						<?php endif; ?>
					</div>

					<!-- Custom quantity input (shown when "Más de X" is selected) -->
					<div class="quantity-ml-custom hidden items-center gap-3 py-3 px-4 border border-gray-200 rounded-sm bg-white mt-0">
						<span class="text-sm text-gray-500 leading-none whitespace-nowrap"><?php esc_html_e( 'Cantidad:', 'etheme' ); ?></span>
						<input type="number"
							   id="quantity-custom"
							   class="quantity-ml-custom-input quantity-field w-20 text-sm text-gray-800 border border-gray-200 rounded px-2 py-1 focus:outline-none focus:border-gray-400"
							   value="<?php echo esc_attr( $dropdown_limit + 1 ); ?>"
							   min="<?php echo esc_attr( $dropdown_limit + 1 ); ?>"
							   <?php echo $max_qty > 0 ? 'max="' . esc_attr( $max_qty ) . '"' : ''; ?>
							   step="1"
							   inputmode="numeric" />
						<button type="button" class="quantity-ml-custom-cancel text-sm text-blue-600 hover:text-blue-800 ml-auto whitespace-nowrap cursor-pointer">
							<?php esc_html_e( 'Cancelar', 'etheme' ); ?>
						</button>
					</div>

					<!-- Hidden input carries the actual quantity value for form submission -->
					<input type="hidden"<?php echo $form_attr; ?>
						   id="quantity"
						   name="quantity"
						   class="quantity-field"
						   value="<?php echo esc_attr( $min_qty ); ?>"
						   data-min="<?php echo esc_attr( $min_qty ); ?>"
						   data-max="<?php echo esc_attr( $max_qty > 0 ? $max_qty : '' ); ?>"
						   data-limit="<?php echo esc_attr( $dropdown_limit ); ?>" />

				</div>
			</div>
				
				<div class="w-full relative group">
					<button type="submit"<?php echo $form_attr; ?>
							name="add-to-cart"
							value="<?php echo esc_attr( $product->get_id() ); ?>"
							id="add-to-cart-button"
							class="block w-full px-3 py-4 rounded-sm text-center text-white text-sm font-semibold uppercase tracking-widest bg-[#2b5756] hover:opacity-90 transition duration-200 disabled:bg-gray-400 <?php echo $button_disabled ? 'opacity-50 cursor-not-allowed' : ''; ?>"
							<?php echo $button_disabled ? 'disabled' : ''; ?>
							data-add-text="<?php esc_attr_e( 'Agregar al carrito', 'etheme' ); ?>"
							data-adding-text="<?php esc_attr_e( 'Agregando...', 'etheme' ); ?>"
							data-added-text="<?php esc_attr_e( 'Agregado', 'etheme' ); ?>"
							data-out-of-stock-text="<?php esc_attr_e( 'Sin stock', 'etheme' ); ?>">
						<span class="button-text"><?php echo esc_html( $button_text ); ?></span>
					</button>
					<div class="purchase-lock-tooltip pointer-events-none absolute left-0 right-0 -top-2 -translate-y-full hidden">
						<div class="mx-auto w-fit max-w-full bg-white border border-gray-200 text-gray-900 rounded-md px-3 py-2 shadow-sm flex items-center gap-2 text-sm">
							<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600 flex-shrink-0" viewBox="0 0 24 24" fill="none">
								<path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10Z" stroke="currentColor" stroke-width="2"/>
								<path d="M8 8l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								<path d="M16 8l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							<span>Seleccioná tus opciones para continuar.</span>
						</div>
					</div>
				</div>

				<div class="w-full relative group">
					<?php
					$buy_now_form_action = esc_url( add_query_arg( 'bakka_buy_now', '1' ) );
					?>
					<button type="submit"<?php echo $form_attr; ?>
							name="add-to-cart"
							value="<?php echo esc_attr( $product->get_id() ); ?>"
							id="buy-now-button"
							formaction="<?php echo $buy_now_form_action; ?>"
							class="block w-full px-3 py-4 rounded-sm text-center text-white text-sm font-semibold uppercase tracking-widest bg-[#fb704f] hover:opacity-90 transition duration-200 disabled:bg-gray-400 <?php echo $button_disabled ? 'opacity-50 cursor-not-allowed' : ''; ?>"
							<?php echo $button_disabled ? 'disabled' : ''; ?>
							data-add-text="<?php esc_attr_e( 'Comprar ahora', 'etheme' ); ?>">
						<span class="button-text"><?php esc_html_e( 'Comprar ahora', 'etheme' ); ?></span>
					</button>
					<div class="purchase-lock-tooltip pointer-events-none absolute left-0 right-0 -top-2 -translate-y-full hidden">
						<div class="mx-auto w-fit max-w-full bg-white border border-gray-200 text-gray-900 rounded-md px-3 py-2 shadow-sm flex items-center gap-2 text-sm">
							<svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-red-600 flex-shrink-0" viewBox="0 0 24 24" fill="none">
								<path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10Z" stroke="currentColor" stroke-width="2"/>
								<path d="M8 8l8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
								<path d="M16 8l-8 8" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
							</svg>
							<span>Seleccioná tus opciones para continuar.</span>
						</div>
					</div>
				</div>
			</div>

			<?php else : ?>
				<button type="submit"<?php echo $form_attr; ?>
						name="add-to-cart"
						value="<?php echo esc_attr( $product->get_id() ); ?>"
						id="add-to-cart-button"
						class="block w-full px-3 py-4 rounded-sm text-center text-white text-sm font-semibold uppercase tracking-widest bg-[#fb704f] hover:opacity-90 transition duration-200 disabled:bg-gray-400 <?php echo $button_disabled ? 'opacity-50 cursor-not-allowed' : ''; ?>"
						<?php echo $button_disabled ? 'disabled' : ''; ?>
						data-add-text="<?php esc_attr_e( 'Agregar al carrito', 'etheme' ); ?>"
						data-adding-text="<?php esc_attr_e( 'Agregando...', 'etheme' ); ?>"
						data-added-text="<?php esc_attr_e( 'Agregado', 'etheme' ); ?>"
						data-out-of-stock-text="<?php esc_attr_e( 'Sin stock', 'etheme' ); ?>">
					<span class="button-text"><?php echo esc_html( $button_text ); ?></span>
				</button>

			<?php endif; ?>

			<div class="purchase-protection-notices mt-4 flex flex-col gap-2  mt-[40px] sm:mt-[50px]">
				<?php foreach ( $purchase_notices as $notice_index => $notice ) : ?>
					<?php $is_mercadopago = false !== strpos( (string) $notice['icon'], 'mercadopago' ); ?>
					<div class="purchase-protection flex items-start gap-3 mb-2">
						<div class="purchase-protection-icon inline-flex items-center justify-center w-8 h-8 text-gray-900 shrink-0">
							<img
								src="<?php echo esc_url( get_theme_file_uri( $notice['icon'] ) ); ?>"
								alt="<?php echo esc_attr( $notice['title'] ); ?>"
								class="w-8 h-8 block object-contain object-center <?php echo $is_mercadopago ? 'scale-[1.18]' : ''; ?>"
							/>
						</div>
						<div class="flex flex-col justify-start">
							<p class="text-base font-semibold text-gray-900 leading-tight">
								<?php echo esc_html( $notice['title'] ); ?>
							</p>
							<p class="text-sm text-gray-900 leading-tight">
								<?php echo esc_html( $notice['text'] ); ?>
							</p>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			
			<?php if ( ! $is_variable ) : ?>
			<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />
			<?php endif; ?>
		
		<?php if ( ! $is_variable ) : ?>
		</form>
		<?php endif; ?>
		
	</div>
	<?php
}
