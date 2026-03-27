<?php
/**
 * Image Modal Component
 *
 * Renders the fullscreen image modal with zoom and navigation.
 *
 * @param array $gallery_images Array of image IDs.
 * @return void
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function etheme_render_image_modal( $gallery_images ) {
	if ( empty( $gallery_images ) ) {
		return;
	}
	
	$has_multiple_images = count( $gallery_images ) > 1;
	?>
	
	<!-- Image Modal Backdrop -->
	<div id="image-modal"
		 class="fixed inset-0 z-50 hidden"
		 role="dialog"
		 aria-modal="true"
		 aria-label="<?php esc_attr_e( 'Galería de imágenes del producto', 'etheme' ); ?>">
		
		<!-- Backdrop -->
		<div class="modal-backdrop absolute inset-0 bg-black/90" data-close-modal></div>
		
		<!-- Modal Content -->
		<div class="modal-content relative w-full h-full flex items-center justify-center p-4">
			
			<!-- Close Button -->
			<button type="button"
					class="absolute top-4 right-4 z-10 bg-white/10 hover:bg-white/20 text-white rounded-full p-3 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white"
					data-close-modal
					aria-label="<?php esc_attr_e( 'Cerrar galería', 'etheme' ); ?>">
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
				</svg>
			</button>
			
			<!-- Image Counter -->
			<?php if ( $has_multiple_images ) : ?>
			<div class="absolute top-4 left-4 z-10 bg-white/10 text-white px-3 py-1 rounded-full text-sm">
				<span id="modal-image-counter">1</span> / <?php echo count( $gallery_images ); ?>
			</div>
			<?php endif; ?>
			
			<!-- Navigation Arrows -->
			<?php if ( $has_multiple_images ) : ?>
			<button type="button"
					class="absolute left-4 top-1/2 -translate-y-1/2 z-10 bg-white/10 hover:bg-white/20 text-white rounded-full p-3 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white"
					id="modal-prev"
					aria-label="<?php esc_attr_e( 'Imagen anterior', 'etheme' ); ?>">
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
				</svg>
			</button>
			
			<button type="button"
					class="absolute right-4 top-1/2 -translate-y-1/2 z-10 bg-white/10 hover:bg-white/20 text-white rounded-full p-3 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white"
					id="modal-next"
					aria-label="<?php esc_attr_e( 'Siguiente imagen', 'etheme' ); ?>">
				<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
				</svg>
			</button>
			<?php endif; ?>
			
			<!-- Zoom Controls -->
			<div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex gap-2">
				<button type="button"
						class="bg-white/10 hover:bg-white/20 text-white rounded-full p-2 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white"
						id="modal-zoom-out"
						aria-label="<?php esc_attr_e( 'Alejar', 'etheme' ); ?>">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7" />
					</svg>
				</button>
				<button type="button"
						class="bg-white/10 hover:bg-white/20 text-white rounded-full p-2 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white"
						id="modal-zoom-reset"
						aria-label="<?php esc_attr_e( 'Restablecer zoom', 'etheme' ); ?>">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
					</svg>
				</button>
				<button type="button"
						class="bg-white/10 hover:bg-white/20 text-white rounded-full p-2 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white"
						id="modal-zoom-in"
						aria-label="<?php esc_attr_e( 'Acercar', 'etheme' ); ?>">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7" />
					</svg>
				</button>
			</div>
			
			<!-- Image Container -->
			<div class="modal-image-wrapper relative max-w-full max-h-full overflow-hidden"
				 id="modal-image-wrapper">
				<img src=""
					 alt=""
					 class="modal-image max-w-none transition-transform duration-200 cursor-grab active:cursor-grabbing"
					 id="modal-image"
					 draggable="false" />
			</div>
			
		</div>
		
		<!-- Hidden data for JavaScript -->
		<script type="application/json" id="modal-gallery-data">
			<?php
			$images_data = array();
			foreach ( $gallery_images as $image_id ) {
				$images_data[] = array(
					'id'      => $image_id,
					'full'    => wp_get_attachment_image_url( $image_id, 'full' ),
					'large'   => wp_get_attachment_image_url( $image_id, 'woocommerce_single' ),
					'alt'     => get_post_meta( $image_id, '_wp_attachment_image_alt', true ),
				);
			}
			echo wp_json_encode( $images_data );
			?>
		</script>
	</div>
	<?php
}
