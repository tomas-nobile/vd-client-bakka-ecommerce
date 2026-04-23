<?php
/**
 * Contact Map component.
 *
 * Renders the Google Maps iframe section.
 *
 * @param array $attributes Block attributes.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders the Map section.
 *
 * @param array $attributes Block attributes.
 */
function etheme_render_contact_map( array $attributes ): void {
	$map_src = esc_url( $attributes['mapSrc'] );
	if ( empty( $map_src ) ) {
		return;
	}
	?>
	<div class="map-con">
		<div class="container mx-auto px-4">
			<div>
				<iframe
					src="<?php echo $map_src; ?>"
					title="<?php esc_attr_e( 'Mapa de contacto', 'etheme' ); ?>"
					style="border:none;"
					allowfullscreen=""
					loading="lazy"
					referrerpolicy="no-referrer-when-downgrade"
				></iframe>
			</div>
		</div>
	</div>
	<?php
}
