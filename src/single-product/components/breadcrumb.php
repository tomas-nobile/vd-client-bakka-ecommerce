<?php
/**
 * Breadcrumb Component
 *
 * Renders WooCommerce breadcrumb navigation.
 *
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_product_breadcrumb() {
	if ( ! function_exists( 'woocommerce_breadcrumb' ) ) {
		return;
	}
	?>
	
	<nav class="product-breadcrumb mb-6" aria-label="<?php esc_attr_e( 'Migas de pan', 'etheme' ); ?>">
		<?php
		woocommerce_breadcrumb( array(
			'wrap_before' => '<ol class="flex flex-wrap items-center gap-2 text-[10px] uppercase tracking-[0.2em] text-gray-500">',
			'wrap_after'  => '</ol>',
			'before'      => '<li class="breadcrumb-item flex items-center">',
			'after'       => '</li>',
			'delimiter'   => '<svg class="w-4 h-4 mx-1 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" /></svg>',
		) );
		?>
	</nav>
	
	<style>
		.product-breadcrumb a {
			color: inherit;
			text-decoration: none;
			transition: color 0.2s;
		}
		.product-breadcrumb a:hover {
			color: #2563eb;
		}
		.product-breadcrumb .breadcrumb-item:last-child {
			color: #111827;
			font-weight: 500;
		}
	</style>
	<?php
}
