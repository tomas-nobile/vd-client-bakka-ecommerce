<?php
/**
 * Email helpers — logo, site name, contact notification trigger.
 *
 * @package bakka
 * @see specs/22.transactional-emails.md
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get the URL of the email logo.
 *
 * Prefers PNG at assets/images/logo-email.png; falls back to SVG if the PNG
 * isn't present (PNG is required for Gmail/Outlook/Apple Mail — see spec 22).
 *
 * @return string
 */
function etheme_get_email_logo_url() {
	$png_rel  = '/assets/images/logo-email.png';
	$png_path = get_template_directory() . $png_rel;
	if ( file_exists( $png_path ) ) {
		return get_theme_file_uri( $png_rel );
	}

	// TODO: reemplazar por PNG cuando esté disponible — SVG no renderiza en Gmail/Outlook/Apple Mail.
	$svg_rel  = '/assets/images/logo.svg';
	$svg_path = get_template_directory() . $svg_rel;
	if ( file_exists( $svg_path ) ) {
		return get_theme_file_uri( $svg_rel );
	}

	return '';
}

/**
 * Get the site name used in emails (header, subjects).
 *
 * @return string
 */
function etheme_get_email_site_name() {
	$config = etheme_get_core_config();
	if ( ! empty( $config['site']['name'] ) ) {
		return (string) $config['site']['name'];
	}
	return (string) get_bloginfo( 'name' );
}

/**
 * Get the "from" name for outgoing mail.
 *
 * @return string
 */
function etheme_get_email_from_name() {
	$config = etheme_get_core_config();
	if ( ! empty( $config['email']['fromName'] ) ) {
		return (string) $config['email']['fromName'];
	}
	return etheme_get_email_site_name();
}

/**
 * Get the "from" address for outgoing mail.
 *
 * @return string
 */
function etheme_get_email_from_address() {
	$config = etheme_get_core_config();
	if ( ! empty( $config['email']['fromAddress'] ) && is_email( $config['email']['fromAddress'] ) ) {
		return (string) $config['email']['fromAddress'];
	}
	return (string) get_option( 'admin_email' );
}

/**
 * Get the reply-to address for outgoing mail.
 *
 * @return string
 */
function etheme_get_email_reply_to() {
	$config = etheme_get_core_config();
	if ( ! empty( $config['email']['replyTo'] ) && is_email( $config['email']['replyTo'] ) ) {
		return (string) $config['email']['replyTo'];
	}
	return etheme_get_email_from_address();
}

/**
 * Get the admin recipient address for admin notifications (A3).
 *
 * @return string
 */
function etheme_get_email_admin_recipient() {
	$config = etheme_get_core_config();
	if ( ! empty( $config['email']['adminRecipient'] ) && is_email( $config['email']['adminRecipient'] ) ) {
		return (string) $config['email']['adminRecipient'];
	}
	return (string) get_option( 'admin_email' );
}

/**
 * Render an email template file with data to a string.
 *
 * @param string $template Absolute path to the template file.
 * @param array  $data     Variables to extract for the template.
 * @return string
 */
function etheme_render_email( $template, $data = array() ) {
	if ( ! file_exists( $template ) ) {
		return '';
	}

	ob_start();
	if ( is_array( $data ) ) {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $data, EXTR_SKIP );
	}
	include $template;
	return (string) ob_get_clean();
}

/**
 * Send the contact-form notification email (A3) to the admin.
 *
 * Preferred path: trigger the WC_Email class so the mail is sent with the
 * theme's header/footer/styles. Fallback: wp_mail() with the stand-alone
 * template (when WooCommerce isn't active).
 *
 * @param array $data {
 *     @type string $name    Full name (already sanitized).
 *     @type string $email   Email (already sanitized).
 *     @type string $phone   Phone (already sanitized, may be empty).
 *     @type string $message Message (already sanitized).
 * }
 * @return bool True on success, false otherwise.
 */
function etheme_send_contact_notification( $data ) {
	$name    = isset( $data['name'] ) ? (string) $data['name'] : '';
	$email   = isset( $data['email'] ) ? sanitize_email( (string) $data['email'] ) : '';
	$phone   = isset( $data['phone'] ) ? (string) $data['phone'] : '';
	$message = isset( $data['message'] ) ? (string) $data['message'] : '';

	if ( '' === $name || ! is_email( $email ) || '' === $message ) {
		return false;
	}

	$payload = array(
		'name'    => $name,
		'email'   => $email,
		'phone'   => $phone,
		'message' => $message,
		'date'    => current_time( 'mysql' ),
	);

	if ( function_exists( 'WC' ) && WC() && WC()->mailer() ) {
		$mailer = WC()->mailer();
		$emails = $mailer->get_emails();
		if ( isset( $emails['Etheme_Contact_Message_Email'] ) ) {
			$emails['Etheme_Contact_Message_Email']->trigger( $payload );
			return true;
		}
	}

	// Fallback: wp_mail() without WC mailer.
	$to      = etheme_get_email_admin_recipient();
	$subject = sprintf(
		/* translators: 1: site name, 2: sender name */
		__( '[%1$s] Nuevo mensaje de contacto de %2$s', 'etheme' ),
		etheme_get_email_site_name(),
		$name
	);

	$template_html  = get_template_directory() . '/src/core/emails/contact-message-email.php';
	$template_plain = get_template_directory() . '/src/core/emails/contact-message-email-plain.php';

	$body = etheme_render_email(
		$template_html,
		array(
			'email_heading' => __( 'Nuevo mensaje desde el formulario de contacto', 'etheme' ),
			'data'          => $payload,
		)
	);

	if ( '' === $body ) {
		return false;
	}

	$from_name    = etheme_get_email_from_name();
	$from_address = etheme_get_email_from_address();

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		'From: ' . $from_name . ' <' . $from_address . '>',
		'Reply-To: ' . $name . ' <' . $email . '>',
	);

	$sent = wp_mail( $to, $subject, $body, $headers );

	if ( ! $sent ) {
		error_log( 'etheme_send_contact_notification: wp_mail() returned false for ' . $email );
	}

	return (bool) $sent;
}
