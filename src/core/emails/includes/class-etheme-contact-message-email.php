<?php
/**
 * Contact form notification email (A3) — WC_Email class.
 *
 * Registers an entry under WooCommerce → Settings → Emails so the admin can
 * customize recipient / subject / enable-disable from the UI.
 *
 * @package bakka
 * @see specs/22.transactional-emails.md
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

if ( class_exists( 'Etheme_Contact_Message_Email' ) ) {
	return;
}

class Etheme_Contact_Message_Email extends WC_Email {

	/**
	 * Sender payload (set on trigger()).
	 *
	 * @var array
	 */
	public $contact_data = array();

	public function __construct() {
		$this->id             = 'etheme_contact_message';
		$this->customer_email = false;
		$this->title          = __( 'Mensaje de formulario de contacto', 'etheme' );
		$this->description    = __( 'Notificación enviada al admin cuando alguien envía el formulario de contacto del sitio.', 'etheme' );

		$this->template_html  = 'contact-message-email.php';
		$this->template_plain = 'contact-message-email-plain.php';

		// Resolve templates from theme's src/core/emails directory (outside woocommerce/emails).
		$this->template_base = get_template_directory() . '/src/core/emails/';

		$this->placeholders = array(
			'{site_name}'   => etheme_get_email_site_name(),
			'{sender_name}' => '',
		);

		parent::__construct();

		$this->recipient = etheme_get_email_admin_recipient();
	}

	public function get_default_subject() {
		return __( '[{site_name}] Nuevo mensaje de contacto de {sender_name}', 'etheme' );
	}

	public function get_default_heading() {
		return __( 'Nuevo mensaje desde el formulario de contacto', 'etheme' );
	}

	/**
	 * Trigger the email.
	 *
	 * @param array $data Sender payload.
	 */
	public function trigger( $data ) {
		if ( ! is_array( $data ) ) {
			return;
		}

		$this->contact_data = $data;

		$sender_name = isset( $data['name'] ) ? (string) $data['name'] : '';
		$this->placeholders['{site_name}']   = etheme_get_email_site_name();
		$this->placeholders['{sender_name}'] = $sender_name;

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}

		$sender_email = isset( $data['email'] ) ? sanitize_email( (string) $data['email'] ) : '';
		if ( $sender_email && is_email( $sender_email ) ) {
			add_filter( 'woocommerce_email_headers', array( $this, 'add_reply_to_header' ), 10, 3 );
		}

		$sent = $this->send(
			$this->get_recipient(),
			$this->get_subject(),
			$this->get_content(),
			$this->get_headers(),
			$this->get_attachments()
		);

		remove_filter( 'woocommerce_email_headers', array( $this, 'add_reply_to_header' ), 10 );

		if ( ! $sent ) {
			error_log( 'Etheme_Contact_Message_Email: send failed for ' . $sender_email );
		}
	}

	/**
	 * Add Reply-To header pointing to the sender's email.
	 *
	 * @param string $headers Existing headers string.
	 * @param string $object  Email id.
	 * @param mixed  $object2 Extra.
	 * @return string
	 */
	public function add_reply_to_header( $headers, $object = '', $object2 = null ) {
		if ( $this->id !== $object ) {
			return $headers;
		}
		$sender_email = isset( $this->contact_data['email'] ) ? sanitize_email( (string) $this->contact_data['email'] ) : '';
		$sender_name  = isset( $this->contact_data['name'] ) ? (string) $this->contact_data['name'] : '';
		if ( ! is_email( $sender_email ) ) {
			return $headers;
		}
		$reply_to = $sender_name ? $sender_name . ' <' . $sender_email . '>' : $sender_email;
		$headers .= "Reply-To: " . $reply_to . "\r\n";
		return $headers;
	}

	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'email_heading' => $this->get_heading(),
				'data'          => $this->contact_data,
				'sent_to_admin' => true,
				'plain_text'    => false,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
	}

	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'email_heading' => $this->get_heading(),
				'data'          => $this->contact_data,
				'sent_to_admin' => true,
				'plain_text'    => true,
				'email'         => $this,
			),
			'',
			$this->template_base
		);
	}

	public function init_form_fields() {
		$this->form_fields = array(
			'enabled'    => array(
				'title'   => __( 'Activar/Desactivar', 'etheme' ),
				'type'    => 'checkbox',
				'label'   => __( 'Activar esta notificación', 'etheme' ),
				'default' => 'yes',
			),
			'recipient'  => array(
				'title'       => __( 'Destinatario(s)', 'etheme' ),
				'type'        => 'text',
				'description' => sprintf(
					/* translators: %s: admin email */
					__( 'Separar con coma. Por defecto %s.', 'etheme' ),
					'<code>' . esc_html( etheme_get_email_admin_recipient() ) . '</code>'
				),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
			),
			'subject'    => array(
				'title'       => __( 'Asunto', 'etheme' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => __( 'Placeholders disponibles: {site_name}, {sender_name}.', 'etheme' ),
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'    => array(
				'title'       => __( 'Heading', 'etheme' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => __( 'Heading del cuerpo del mail.', 'etheme' ),
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Formato', 'etheme' ),
				'type'        => 'select',
				'description' => __( 'Elegir formato HTML o texto plano.', 'etheme' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}
}
