<?php
/**
 * Email hooks — registers the custom WC_Email class and applies filters to
 * the from name/address, subjects and headings.
 *
 * @package bakka
 * @see specs/22.transactional-emails.md
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/../helpers.php';

/**
 * Register the custom contact-message WC_Email class.
 *
 * @param array $emails Registered email classes.
 * @return array
 */
function etheme_register_email_classes( $emails ) {
	if ( ! class_exists( 'Etheme_Contact_Message_Email' ) ) {
		require_once __DIR__ . '/class-etheme-contact-message-email.php';
	}
	if ( class_exists( 'Etheme_Contact_Message_Email' ) ) {
		$emails['Etheme_Contact_Message_Email'] = new Etheme_Contact_Message_Email();
	}

	if ( ! class_exists( 'Etheme_Customer_Failed_Order_Email' ) ) {
		require_once __DIR__ . '/class-etheme-customer-failed-order-email.php';
	}
	if ( class_exists( 'Etheme_Customer_Failed_Order_Email' ) ) {
		$emails['Etheme_Customer_Failed_Order_Email'] = new Etheme_Customer_Failed_Order_Email();
	}

	return $emails;
}
add_filter( 'woocommerce_email_classes', 'etheme_register_email_classes' );

/**
 * Override the From name for all WC transactional mail.
 *
 * @param string $name  Current from name.
 * @param mixed  $email Email object.
 * @return string
 */
function etheme_filter_email_from_name( $name, $email = null ) {
	$custom = etheme_get_email_from_name();
	return '' !== $custom ? $custom : $name;
}
add_filter( 'woocommerce_email_from_name', 'etheme_filter_email_from_name', 10, 2 );

/**
 * Override the From address for all WC transactional mail.
 *
 * @param string $address Current from address.
 * @param mixed  $email   Email object.
 * @return string
 */
function etheme_filter_email_from_address( $address, $email = null ) {
	$custom = etheme_get_email_from_address();
	return '' !== $custom ? $custom : $address;
}
add_filter( 'woocommerce_email_from_address', 'etheme_filter_email_from_address', 10, 2 );

/**
 * Custom subject: customer processing order (C1).
 *
 * @param string   $subject Default subject.
 * @param WC_Order $order   Order.
 * @return string
 */
function etheme_filter_email_subject_customer_processing( $subject, $order = null ) {
	if ( ! $order instanceof WC_Order ) {
		return $subject;
	}
	return sprintf(
		/* translators: %s: order number */
		__( 'Recibimos tu pedido #%s — ya estamos preparándolo', 'etheme' ),
		$order->get_order_number()
	);
}
add_filter( 'woocommerce_email_subject_customer_processing_order', 'etheme_filter_email_subject_customer_processing', 10, 2 );

/**
 * Custom heading: customer processing order (C1).
 */
function etheme_filter_email_heading_customer_processing( $heading, $order = null ) {
	if ( ! $order instanceof WC_Order ) {
		return $heading;
	}
	return sprintf(
		/* translators: %s: first name */
		__( '¡Gracias por tu compra, %s!', 'etheme' ),
		$order->get_billing_first_name()
	);
}
add_filter( 'woocommerce_email_heading_customer_processing_order', 'etheme_filter_email_heading_customer_processing', 10, 2 );

/**
 * Custom subject: customer completed order (C2).
 */
function etheme_filter_email_subject_customer_completed( $subject, $order = null ) {
	if ( ! $order instanceof WC_Order ) {
		return $subject;
	}
	return sprintf(
		/* translators: %s: order number */
		__( 'Tu pedido #%s fue entregado', 'etheme' ),
		$order->get_order_number()
	);
}
add_filter( 'woocommerce_email_subject_customer_completed_order', 'etheme_filter_email_subject_customer_completed', 10, 2 );

/**
 * Custom heading: customer completed order (C2).
 */
function etheme_filter_email_heading_customer_completed( $heading, $order = null ) {
	if ( ! $order instanceof WC_Order ) {
		return $heading;
	}
	return sprintf(
		/* translators: %s: first name */
		__( 'Tu pedido llegó, %s', 'etheme' ),
		$order->get_billing_first_name()
	);
}
add_filter( 'woocommerce_email_heading_customer_completed_order', 'etheme_filter_email_heading_customer_completed', 10, 2 );

/**
 * Custom subject: customer on-hold order (C4).
 */
function etheme_filter_email_subject_customer_on_hold( $subject, $order = null ) {
	if ( ! $order instanceof WC_Order ) {
		return $subject;
	}
	return sprintf(
		/* translators: %s: order number */
		__( 'Tu pedido #%s está en espera', 'etheme' ),
		$order->get_order_number()
	);
}
add_filter( 'woocommerce_email_subject_customer_on_hold_order', 'etheme_filter_email_subject_customer_on_hold', 10, 2 );

/**
 * Custom heading: customer on-hold order (C4).
 */
function etheme_filter_email_heading_customer_on_hold( $heading, $order = null ) {
	if ( ! $order instanceof WC_Order ) {
		return $heading;
	}
	return sprintf(
		/* translators: %s: first name */
		__( 'Tu pedido está en espera, %s', 'etheme' ),
		$order->get_billing_first_name()
	);
}
add_filter( 'woocommerce_email_heading_customer_on_hold_order', 'etheme_filter_email_heading_customer_on_hold', 10, 2 );

/**
 * Custom subject: admin new order (A1).
 */
function etheme_filter_email_subject_admin_new_order( $subject, $order = null ) {
	if ( ! $order instanceof WC_Order ) {
		return $subject;
	}
	return sprintf(
		/* translators: 1: site, 2: order number, 3: total */
		__( '[%1$s] Nuevo pedido #%2$s — %3$s', 'etheme' ),
		etheme_get_email_site_name(),
		$order->get_order_number(),
		wp_strip_all_tags( html_entity_decode( $order->get_formatted_order_total() ) )
	);
}
add_filter( 'woocommerce_email_subject_new_order', 'etheme_filter_email_subject_admin_new_order', 10, 2 );

/**
 * Custom heading: admin new order (A1).
 */
function etheme_filter_email_heading_admin_new_order( $heading, $order = null ) {
	return __( 'Nuevo pedido recibido', 'etheme' );
}
add_filter( 'woocommerce_email_heading_new_order', 'etheme_filter_email_heading_admin_new_order', 10, 2 );

/**
 * Custom subject: admin failed order (A2).
 */
function etheme_filter_email_subject_admin_failed_order( $subject, $order = null ) {
	if ( ! $order instanceof WC_Order ) {
		return $subject;
	}
	return sprintf(
		/* translators: 1: site, 2: order number */
		__( '[%1$s] Pedido #%2$s falló — acción requerida', 'etheme' ),
		etheme_get_email_site_name(),
		$order->get_order_number()
	);
}
add_filter( 'woocommerce_email_subject_failed_order', 'etheme_filter_email_subject_admin_failed_order', 10, 2 );

/**
 * Custom heading: admin failed order (A2).
 */
function etheme_filter_email_heading_admin_failed_order( $heading, $order = null ) {
	return __( 'Pedido fallido', 'etheme' );
}
add_filter( 'woocommerce_email_heading_failed_order', 'etheme_filter_email_heading_admin_failed_order', 10, 2 );

/**
 * Override the default footer text to something less generic.
 *
 * @param string $text Current text.
 * @return string
 */
function etheme_filter_email_footer_text( $text ) {
	return sprintf(
		/* translators: %s: site name */
		__( 'Recibiste este mail porque interactuaste con %s. No respondas a este mail — para consultas usá los canales de contacto.', 'etheme' ),
		etheme_get_email_site_name()
	);
}
add_filter( 'woocommerce_email_footer_text', 'etheme_filter_email_footer_text' );
