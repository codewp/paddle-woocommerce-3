<?php

defined( 'ABSPATH' ) || exit;

if ( class_exists( 'Paddle_WC_Email_Cancelled_Subscription', false ) ) {
	return;
}

class Paddle_WC_Email_Cancelled_Subscription extends WC_Email {

	public $order;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'cancelled_subscription';
		$this->title          = __( 'Cancelled subscription', 'paddle' );
		$this->description    = __( 'Cancelled Subscription emails are sent when a customer\'s subscription is cancelled (either by a store manager, or the customer).', 'paddle' );
		$this->template_html  = 'emails/admin-cancelled-subscription.php';
		$this->template_plain = 'emails/plain/admin-cancelled-subscription.php';
		$this->template_base  = plugin_dir_path( ASNP_PADDLE_WC_PLUGIN_FILE ) . 'templates/';
		$this->placeholders   = array(
			'{order_date}'              => '',
			'{subscription_number}'     => '',
			'{order_billing_full_name}' => '',
		);

		// Triggers for this email.
		add_action( 'paddle_wc_subscription_cancelled_notification', array( $this, 'trigger' ) );

		// Call parent constructor.
		parent::__construct();

		// Other settings.
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
	}

	/**
	 * Get email subject.
	 *
	 * @return string
	 */
	public function get_default_subject() {
		return __( '[{site_title}]: Subscription #{subscription_number} has been cancelled', 'paddle' );
	}

	/**
	 * Get email heading.
	 *
	 * @return string
	 */
	public function get_default_heading() {
		return __( 'Subscription Cancelled: #{subscription_number}', 'paddle' );
	}

	/**
	 * Trigger the sending of this email.
	 *
	 * @param int            $order_id The order ID.
	 * @param WC_Order|false $order Order object.
	 */
	public function trigger( $id ) {
		if ( ! $id || 0 >= (int) $id ) {
			return;
		}

		$this->setup_locale();

		$subscription = paddle_wc()->subscriptions->get_item( $id );
		if ( ! $subscription ) {
			return;
		}

		$this->object = $subscription;

		$order = wc_get_order( $subscription->order_id );
		if ( is_a( $order, 'WC_Order' ) ) {
			$this->order                                     = $order;
			$this->placeholders['{order_date}']              = wc_format_datetime( $order->get_date_created() );
			$this->placeholders['{subscription_number}']     = absint( $subscription->id );
			$this->placeholders['{order_billing_full_name}'] = $order->get_formatted_billing_full_name();
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * Get content html.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html(
			$this->template_html,
			array(
				'subscription'       => $this->object,
				'order'              => $this->order,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => true,
				'plain_text'         => false,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'subscription'       => $this->object,
				'order'              => $this->order,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => true,
				'plain_text'         => true,
				'email'              => $this,
			),
			'',
			$this->template_base
		);
	}

	/**
	 * Default content to show below main email content.
	 *
	 * @return string
	 */
	public function get_default_additional_content() {
		return __( 'Thanks for reading.', 'paddle' );
	}

	/**
	 * Initialise settings form fields.
	 */
	public function init_form_fields() {
		/* translators: %s: list of placeholders */
		$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'paddle' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );
		$this->form_fields = array(
			'enabled'            => array(
				'title'   => __( 'Enable/Disable', 'paddle' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'paddle' ),
				'default' => 'yes',
			),
			'recipient'          => array(
				'title'       => __( 'Recipient(s)', 'paddle' ),
				'type'        => 'text',
				/* translators: %s: admin email */
				'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'paddle' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
				'placeholder' => '',
				'default'     => '',
				'desc_tip'    => true,
			),
			'subject'            => array(
				'title'       => __( 'Subject', 'paddle' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_subject(),
				'default'     => '',
			),
			'heading'            => array(
				'title'       => __( 'Email heading', 'paddle' ),
				'type'        => 'text',
				'desc_tip'    => true,
				'description' => $placeholder_text,
				'placeholder' => $this->get_default_heading(),
				'default'     => '',
			),
			'additional_content' => array(
				'title'       => __( 'Additional content', 'paddle' ),
				'description' => __( 'Text to appear below the main email content.', 'paddle' ) . ' ' . $placeholder_text,
				'css'         => 'width:400px; height: 75px;',
				'placeholder' => __( 'N/A', 'paddle' ),
				'type'        => 'textarea',
				'default'     => $this->get_default_additional_content(),
				'desc_tip'    => true,
			),
			'email_type'         => array(
				'title'       => __( 'Email type', 'paddle' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'paddle' ),
				'default'     => 'html',
				'class'       => 'email_type wc-enhanced-select',
				'options'     => $this->get_email_type_options(),
				'desc_tip'    => true,
			),
		);
	}

}
