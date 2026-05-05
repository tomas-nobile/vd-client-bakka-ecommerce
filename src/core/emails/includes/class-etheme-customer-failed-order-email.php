<?php
/**
 * Customer failed order email (C3) — WC_Email class.
 *
 * Registers an entry under WooCommerce → Settings → Emails so the admin can
 * customize subject, heading, and enable/disable from the UI.
 *
 * @package bakka
 * @see specs/22.transactional-emails.md
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Email' ) ) {
	return;
}

if ( class_exists( 'Etheme_Customer_Failed_Order_Email' ) ) {
	return;
}

class Etheme_Customer_Failed_Order_Email extends WC_Email {

	public function __construct() {
		$this->id             = 'etheme_customer_failed_order';
		$this->customer_email = true;
		$this->title          = __( 'Pedido fallido (cliente)', 'etheme' );
		$this->description    = __( 'Notificación enviada al cliente cuando el pago de su pedido no pudo procesarse.', 'etheme' );

		$this->template_html  = 'customer-failed-order.php';
		$this->template_plain = 'plain/customer-failed-order.php';
		$this->template_base  = get_template_directory() . '/woocommerce/emails/';

		$this->placeholders = array(
			'{site_name}'    => '',
			'{order_number}' => '',
			'{order_date}'   => '',
		);

		add_action( 'woocommerce_order_status_failed_notification', array( $this, 'trigger' ), 10, 2 );

		parent::__construct();
	}

	public function get_default_subject() {
		return __( 'Tu pedido #{order_number} no pudo procesarse', 'etheme' );
	}

	public function get_default_heading() {
		return __( 'Hubo un problema con tu pedido', 'etheme' );
	}

	/**
	 * Trigger the email when an order transitions to failed status.
	 *
	 * @param int      $order_id Order ID.
	 * @param WC_Order $order    Order object.
	 */
	public function trigger( $order_id, $order = false ) {
		$this->setup_locale();

		if ( $order_id && ! is_a( $order, 'WC_Order' ) ) {
			$order = wc_get_order( $order_id );
		}

		if ( ! is_a( $order, 'WC_Order' ) ) {
			$this->restore_locale();
			return;
		}

		$this->object    = $order;
		$this->recipient = $order->get_billing_email();

		$this->placeholders['{site_name}']    = $this->get_blogname();
		$this->placeholders['{order_number}'] = $order->get_order_number();
		$this->placeholders['{order_date}']   = wc_format_datetime( $order->get_date_created() );

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send(
				$this->get_recipient(),
				$this->get_subject(),
				$this->get_content(),
				$this->get_headers(),
				$this->get_attachments()
			);
		}

		$this->restore_locale();
	}

	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
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
				'order'         => $this->object,
				'email_heading' => $this->get_heading(),
				'sent_to_admin' => false,
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
			'subject'    => array(
				'title'       => __( 'Asunto', 'etheme' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => __( 'Placeholders disponibles: {site_name}, {order_number}, {order_date}.', 'etheme' ),
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
