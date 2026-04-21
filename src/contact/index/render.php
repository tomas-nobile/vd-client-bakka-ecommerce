<?php
/**
 * Contact Index — orchestrator block.
 *
 * Loads and calls all contact-page component renderers.
 *
 * @param array    $attributes Block attributes.
 * @param string   $content    Block content.
 * @param WP_Block $block      Block instance.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$components_dir = get_template_directory() . '/src/contact/components/';
$components     = array(
	'contact-info',
	'contact-form',
);

foreach ( $components as $component ) {
	require_once $components_dir . $component . '.php';
}

$defaults = array(
	'infoEyebrow'    => __( 'Contacto', 'etheme' ),
	'infoTitle'      => __( 'Cómo encontrarnos', 'etheme' ),
	'locationTitle'  => __( 'Ubicación', 'etheme' ),
	'locationText'   => __( 'Buenos Aires, Argentina', 'etheme' ),
	'locationUrl'    => 'https://www.google.com/maps/search/?api=1&query=Buenos+Aires%2C+Argentina',
	'phoneTitle'     => __( 'Teléfono', 'etheme' ),
	'phoneLabel'     => '',
	'whatsappUrl'    => '',
	'emailTitle'     => __( 'Email', 'etheme' ),
	'email'          => '',
	'formEyebrow'    => __( 'Escribinos', 'etheme' ),
	'formTitle'      => __( 'Envianos un mensaje', 'etheme' ),
	'formButtonText' => __( 'Enviar', 'etheme' ),
	'formEndpoint'   => '',
);

$core_config    = function_exists( 'etheme_get_core_config' ) ? etheme_get_core_config() : array();
$contact_config = isset( $core_config['contact'] ) && is_array( $core_config['contact'] ) ? $core_config['contact'] : array();
$defaults       = wp_parse_args( $contact_config, $defaults );

// Bloques guardados con atributos viejos (box1/box2/box3).
if ( is_array( $attributes ) ) {
	if ( empty( $attributes['locationText'] ) && ! empty( $attributes['box1Text'] ) ) {
		$attributes['locationText'] = $attributes['box1Text'];
	}
	if ( empty( $attributes['locationUrl'] ) && ! empty( $attributes['box1Url'] ) ) {
		$attributes['locationUrl'] = $attributes['box1Url'];
	}
	if ( empty( $attributes['locationTitle'] ) && ! empty( $attributes['box1Title'] ) ) {
		$attributes['locationTitle'] = $attributes['box1Title'];
	}
	if ( empty( $attributes['phoneLabel'] ) && ! empty( $attributes['box2Phone1'] ) ) {
		$attributes['phoneLabel'] = $attributes['box2Phone1'];
	}
	if ( empty( $attributes['phoneTitle'] ) && ! empty( $attributes['box2Title'] ) ) {
		$attributes['phoneTitle'] = $attributes['box2Title'];
	}
	if ( empty( $attributes['email'] ) && ! empty( $attributes['box3Email1'] ) ) {
		$attributes['email'] = $attributes['box3Email1'];
	}
	if ( empty( $attributes['emailTitle'] ) && ! empty( $attributes['box3Title'] ) ) {
		$attributes['emailTitle'] = $attributes['box3Title'];
	}
}

$attributes = wp_parse_args( $attributes, $defaults );

// Atributos guardados como "" (p. ej. defaults de block.json) no deben pisar config.json.
$coalesce_keys = array(
	'locationText',
	'locationUrl',
	'locationTitle',
	'phoneTitle',
	'phoneLabel',
	'whatsappUrl',
	'emailTitle',
	'email',
	'formEyebrow',
	'formTitle',
	'formButtonText',
	'formEndpoint',
);
foreach ( $coalesce_keys as $key ) {
	if ( isset( $attributes[ $key ] ) && '' === $attributes[ $key ] && isset( $defaults[ $key ] ) && '' !== $defaults[ $key ] ) {
		$attributes[ $key ] = $defaults[ $key ];
	}
}
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php
	etheme_render_contact_info( $attributes );
	etheme_render_contact_form( $attributes );
	?>
</div>
