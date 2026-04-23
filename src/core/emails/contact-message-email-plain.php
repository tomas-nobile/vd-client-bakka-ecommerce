<?php
/**
 * Contact form notification email (A3) — plain text.
 *
 * @param string $email_heading Heading.
 * @param array  $data          Sender data.
 */

defined( 'ABSPATH' ) || exit;

$etheme_name    = isset( $data['name'] ) ? (string) $data['name'] : '';
$etheme_email   = isset( $data['email'] ) ? (string) $data['email'] : '';
$etheme_phone   = isset( $data['phone'] ) ? (string) $data['phone'] : '';
$etheme_message = isset( $data['message'] ) ? (string) $data['message'] : '';
$etheme_date    = isset( $data['date'] ) ? (string) $data['date'] : '';

echo "= " . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";

esc_html_e( 'Recibiste un nuevo mensaje desde el formulario de contacto.', 'etheme' );
echo "\n\n";

esc_html_e( 'Nombre', 'etheme' );
echo ": " . esc_html( $etheme_name ) . "\n";

esc_html_e( 'Email', 'etheme' );
echo ": " . esc_html( $etheme_email ) . "\n";

if ( '' !== $etheme_phone ) {
	esc_html_e( 'Teléfono', 'etheme' );
	echo ": " . esc_html( $etheme_phone ) . "\n";
}

if ( '' !== $etheme_date ) {
	esc_html_e( 'Fecha', 'etheme' );
	echo ": " . esc_html( mysql2date( 'd/m/Y H:i', $etheme_date ) ) . "\n";
}

echo "\n----------\n\n";
esc_html_e( 'Mensaje', 'etheme' );
echo ":\n\n";
echo esc_html( $etheme_message );
echo "\n\n----------\n\n";
esc_html_e( 'Para responder, usá el email del cliente directamente.', 'etheme' );
echo "\n";
