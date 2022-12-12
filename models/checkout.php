<?php

/**
 * Class that registers and handles the intercepts on the WC Checkout page.
 */
class Paddle_WC_Checkout {

	/**
	 * Instance of our settings object.
	 *
	 * @var Paddle_WC_Settings
	 */
	private $settings;

	/**
	 * Paddle_WC_Checkout Constructor.
	 */
	public function __construct($settings) {
		$this->settings = $settings;
	}

	/**
	 * Registers the callbacks (WC hooks) that we need to inject Paddle checkout functionality.
	 */
	public function register_callbacks() {
		$this->register_checkout_actions();
	}

	/**
	 * Registers the callbacks needed to handle the WC checkout.
	 */
	protected function register_checkout_actions() {
		// Inject scripts and CSS we need for checkout
		add_action('wp_enqueue_scripts', array($this, 'on_wp_enqueue_scripts'));

		// Add the place order button target url handler
		add_action('wc_ajax_paddle_checkout', array($this, 'on_ajax_process_checkout'));
		add_action('wc_ajax_nopriv_paddle_checkout', array($this, 'on_ajax_process_checkout'));
		// And handle old-version style
		add_action('wp_ajax_paddle_checkout', array($this, 'on_ajax_process_checkout'));
		add_action('wp_ajax_nopriv_paddle_checkout', array($this, 'on_ajax_process_checkout'));

		// Do the same, but for the order-pay page instead of the checkout page - ie. order already exists
		add_action('wc_ajax_paddle_checkout_pay', array($this, 'on_ajax_process_checkout_pay'));
		add_action('wc_ajax_nopriv_paddle_checkout_pay', array($this, 'on_ajax_process_checkout_pay'));
		add_action('wp_ajax_paddle_checkout_pay', array($this, 'on_ajax_process_checkout_pay'));
		add_action('wp_ajax_nopriv_paddle_checkout_pay', array($this, 'on_ajax_process_checkout_pay'));

		// VAT checkout fields.
		add_action( 'woocommerce_after_order_notes', array( $this, 'vat_checkout_fields' ) );
		add_action( 'woocommerce_checkout_process', array( $this, 'process_vat_checkout_fields' ) );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'save_vat_checkout_fields' ) );
		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_vat_checkout_fields' ) );
	}

	/**
	 * Callback when WP is building the list of scripts for the page.
	 */
	public function on_wp_enqueue_scripts() {
		// Inject standard Paddle checkout JS
		wp_enqueue_script('paddle-checkout', 'https://cdn.paddle.com/paddle/paddle.js');

		// Inject our bootstrap JS to intercept the WC button press and invoke standard JS
		wp_register_script('paddle-bootstrap', plugins_url('../assets/js/paddle-bootstrap.js', __FILE__), array('jquery'),"3.0.1");

		// Use wp_localize_script to write JS config that can't be embedded in the script
		$endpoint = is_wc_endpoint_url('order-pay') ? 'paddle_checkout_pay' : 'paddle_checkout';
		$paddle_data = array(
			'order_url' => $this->get_ajax_endpoint_path($endpoint),
			'vendor' => $this->settings->get('paddle_vendor_id')
		);
		wp_localize_script('paddle-bootstrap', 'paddle_data', $paddle_data);
		wp_enqueue_script('paddle-bootstrap');
	}

	/**
	 * Receives our AJAX callback to process the checkout
	 */
	public function on_ajax_process_checkout() {
		// Invoke our Paddle gateway to call out for the Paddle checkout url and return via JSON
		WC()->checkout()->process_checkout();
	}

	/**
	 * Skip the order creation, and go straight to payment processing
	 */
	public function on_ajax_process_checkout_pay() {
		if (!WC()->session->order_awaiting_payment) {
			wc_add_notice('We were unable to process your order, please try again.', 'error');
			ob_start();
			wc_print_notices();
			$messages = ob_get_contents();
			ob_end_clean();
			echo json_encode(array(
				'result' => 'failure',
				'messages' => $messages,
				'errors' => array('We were unable to process your order, please try again.')
			));
			exit;
		}

		// Need the id of the pre-created order
		$order_id = WC()->session->order_awaiting_payment;
		// Get the paddle payment gateway - the payment_method should be posted as "paddle"
		$available_gateways = WC()->payment_gateways->get_available_payment_gateways();
		$available_gateways['paddle']->process_payment($order_id);
		// The process_payment function will exit, so we don't need to return anything here
	}

	/**
	 * Gets the path to be called to invoke the given AJAX endpoint.
	 *
	 * @param String $endpoint The endpoint the AJAX request will be calling.
	 */
	private function get_ajax_endpoint_path($endpoint) {
		if(version_compare(WOOCOMMERCE_VERSION, '2.4.0', '>=')) {
			// WC AJAX callback (Added in 2.4.0)
			$url = parse_url($_SERVER['REQUEST_URI']);
			parse_str(isset($url['query']) ? $url['query'] : '', $query);
			$query['wc-ajax'] = $endpoint;
			$order_url = $url['path'].'?'.http_build_query($query);
		} else {
			// Older callback (not sure we should care about supporting this old)
			$order_url = admin_url('admin-ajax.php?action='.$endpoint);
		}
		return $order_url;
	}

	public function vat_checkout_fields( $checkout ) {
		echo '<div id="vat_checkout_fields"><h2>' . __('VAT information') . '</h2>';
		echo '<p>' . __( 'If you would like to enter your VAT information then fill out all of the below fields.', 'paddle' ) . '</p>';

		woocommerce_form_field( 'vat_number', array(
			'type'         => 'text',
			'label'        => __( 'VAT number', 'paddle' ),
			'description'  => sprintf( __( 'Read more about %s.', 'paddle' ), '<a href="https://www.paddle.com/help/sell/tax/what-format-should-i-use-for-my-vat-id" target="_blank">' . __( 'VAT number format here', 'paddle' ) . '</a>' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'no',
		), $checkout->get_value( 'vat_number' ) );

		woocommerce_form_field( 'vat_company_name', array(
			'type'         => 'text',
			'label'        => __( 'VAT company name', 'paddle' ),
			'description'  => __( 'Required when you have entered your VAT number.', 'paddle' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'no',
		), $checkout->get_value( 'vat_company_name' ) );

		woocommerce_form_field( 'vat_country', array(
			'type'         => 'text',
			'label'        => __( 'VAT country', 'paddle' ),
			'description'  => __( 'Required when you have entered your VAT number.', 'paddle' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'no',
		), $checkout->get_value( 'vat_country' ) );

		woocommerce_form_field( 'vat_city', array(
			'type'         => 'text',
			'label'        => __( 'VAT city', 'paddle' ),
			'description'  => __( 'Required when you have entered your VAT number.', 'paddle' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'no',
		), $checkout->get_value( 'vat_city' ) );

		woocommerce_form_field( 'vat_street', array(
			'type'         => 'text',
			'label'        => __( 'VAT street', 'paddle' ),
			'description'  => __( 'Required when you have entered your VAT number.', 'paddle' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'no',
		), $checkout->get_value( 'vat_street' ) );

		echo '</div>';
	}

	public function process_vat_checkout_fields() {
		$vat_number = ! empty( $_POST['vat_number'] ) ? sanitize_text_field( trim( $_POST['vat_number'] ) ) : '';
		if ( empty( $vat_number ) ) {
			return;
		}

		$vat_company_name = ! empty( $_POST['vat_company_name'] ) ? sanitize_text_field( trim( $_POST['vat_company_name'] ) ) : '';
		$vat_country = ! empty( $_POST['vat_country'] ) ? sanitize_text_field( trim( $_POST['vat_country'] ) ) : '';
		$vat_city = ! empty( $_POST['vat_city'] ) ? sanitize_text_field( trim( $_POST['vat_city'] ) ) : '';
		$vat_street = ! empty( $_POST['vat_street'] ) ? sanitize_text_field( trim( $_POST['vat_street'] ) ) : '';

		if (
			empty( $vat_company_name ) || empty( $vat_country ) ||
			empty( $vat_city ) || empty( $vat_street )
		) {
			wc_add_notice( __( 'The following fields are required when passing vat number: vat company name, vat country, vat city, vat street.' ), 'error' );
		}
	}

	public function save_vat_checkout_fields( $order ) {
		if ( ! $order ) {
            return;
        }

        $vat_number = ! empty( $_POST['vat_number'] ) ? sanitize_text_field( trim( $_POST['vat_number'] ) ) : '';
		$vat_company_name = ! empty( $_POST['vat_company_name'] ) ? sanitize_text_field( trim( $_POST['vat_company_name'] ) ) : '';
		$vat_country = ! empty( $_POST['vat_country'] ) ? sanitize_text_field( trim( $_POST['vat_country'] ) ) : '';
		$vat_city = ! empty( $_POST['vat_city'] ) ? sanitize_text_field( trim( $_POST['vat_city'] ) ) : '';
		$vat_street = ! empty( $_POST['vat_street'] ) ? sanitize_text_field( trim( $_POST['vat_street'] ) ) : '';

        $order->add_meta_data( 'vat_number', $vat_number, true );
		$order->add_meta_data( 'vat_company_name', $vat_company_name, true );
		$order->add_meta_data( 'vat_country', $vat_country, true );
		$order->add_meta_data( 'vat_city', $vat_city, true );
		$order->add_meta_data( 'vat_street', $vat_street, true );
	}

	public function display_vat_checkout_fields( $order ) {
		echo '<p><strong>' . __( 'VAT number', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_number' ) ) . '</p>';
		echo '<p><strong>' . __( 'VAT company name', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_company_name' ) ) . '</p>';
		echo '<p><strong>' . __( 'VAT country', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_country' ) ) . '</p>';
		echo '<p><strong>' . __( 'VAT city', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_city' ) ) . '</p>';
		echo '<p><strong>' . __( 'VAT street', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_street' ) ) . '</p>';
	}

}
