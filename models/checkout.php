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

		// Order thank you message.
		add_filter( 'woocommerce_thankyou_order_received_text', array( $this, 'thankyou_order_received_text' ), 99, 2 );
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
			'vendor' => $this->settings->get('paddle_vendor_id'),
			'sandbox_enabled' => $this->settings->get('sandbox_enabled', 'no'),
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
		echo '<div id="vat_checkout_fields"><h2>' . __( 'VAT information (Optional)', 'paddle' ) . '</h2>';
		echo '<p>' . __( 'If you would like to enter your VAT information then fill out all of the below fields.', 'paddle' ) . '</p>';
		echo '<p><a href="https://www.paddle.com/help/sell/tax/how-paddle-handles-vat-on-your-behalf" target="_blank">' . __( 'How Paddle handles VAT on your behalf', 'paddle' ) . '</a></p>';

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
			'description'  => __( 'Required if VAT number is set.', 'paddle' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'no',
		), $checkout->get_value( 'vat_company_name' ) );

		woocommerce_form_field( 'vat_country', array(
			'type'         => 'country',
			'label'        => __( 'VAT country', 'paddle' ),
			'description'  => __( 'Required if VAT number is set.', 'paddle' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'country',
		), $checkout->get_value( 'vat_country' ) );

		woocommerce_form_field( 'vat_city', array(
			'type'         => 'text',
			'label'        => __( 'VAT city', 'paddle' ),
			'description'  => __( 'Required if VAT number is set.', 'paddle' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'no',
		), $checkout->get_value( 'vat_city' ) );

		woocommerce_form_field( 'vat_street', array(
			'type'         => 'text',
			'label'        => __( 'VAT street', 'paddle' ),
			'description'  => __( 'Required if VAT number is set.', 'paddle' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'no',
		), $checkout->get_value( 'vat_street' ) );

		woocommerce_form_field( 'vat_postcode', array(
			'type'         => 'text',
			'label'        => __( 'VAT postcode', 'paddle' ),
			'description'  => sprintf( __( 'This field is required if vat number is set and the vat country requires postcode. See the %s for countries requiring this field.', 'paddle' ), '<a href="https://developer.paddle.com/reference/platform-parameters/supported-countries#countries-requiring-postcode" target="_blank">' . __( 'Supported countries', 'paddle' ) . '</a>' ),
			'required'     => false,
			'class'        => array( 'form-row-wide' ),
			'autocomplete' => 'no',
		), $checkout->get_value( 'vat_postcode' ) );

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
		$vat_postcode = ! empty( $_POST['vat_postcode'] ) ? sanitize_text_field( trim( $_POST['vat_postcode'] ) ) : '';

		if (
			empty( $vat_company_name ) || empty( $vat_country ) ||
			empty( $vat_city ) || empty( $vat_street )
		) {
			wc_add_notice( __( 'The following fields are required when passing vat number: vat company name, vat country, vat city, vat street.' ), 'error' );
		}

		if ( empty( $vat_postcode ) && in_array( $vat_country, array( 'AU', 'CA', 'FR', 'DE', 'IN', 'IT', 'NL', 'ES', 'GB', 'US' ) ) ) {
			wc_add_notice( __( 'The vat postcode is required for your vat country.' ), 'error' );
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
		$vat_postcode = ! empty( $_POST['vat_postcode'] ) ? sanitize_text_field( trim( $_POST['vat_postcode'] ) ) : '';

		if ( ! empty( $vat_number ) ) {
			$order->add_meta_data( 'vat_number', $vat_number, true );
		}

		if ( ! empty( $vat_company_name ) ) {
			$order->add_meta_data( 'vat_company_name', $vat_company_name, true );
		}

		if ( ! empty( $vat_country ) ) {
			$order->add_meta_data( 'vat_country', $vat_country, true );
		}

		if ( ! empty( $vat_city ) ) {
			$order->add_meta_data( 'vat_city', $vat_city, true );
		}

		if ( ! empty( $vat_street ) ) {
			$order->add_meta_data( 'vat_street', $vat_street, true );
		}

		if ( ! empty( $vat_postcode ) ) {
			$order->add_meta_data( 'vat_postcode', $vat_postcode, true );
		}
	}

	public function display_vat_checkout_fields( $order ) {
		echo '<p><strong>' . __( 'VAT number', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_number' ) ) . '</p>';
		echo '<p><strong>' . __( 'VAT company name', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_company_name' ) ) . '</p>';
		echo '<p><strong>' . __( 'VAT country', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_country' ) ) . '</p>';
		echo '<p><strong>' . __( 'VAT city', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_city' ) ) . '</p>';
		echo '<p><strong>' . __( 'VAT street', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_street' ) ) . '</p>';
		echo '<p><strong>' . __( 'VAT postcode', 'paddle' ) . ':</strong> ' . esc_html( $order->get_meta( 'vat_postcode' ) ) . '</p>';
	}

	public function thankyou_order_received_text( $message, $order ) {
		if ( ! $order ) {
			return $message;
		}

		$items = $order->get_items();
		if ( empty( $items ) ) {
			return $message;
		}

		foreach ( $items as $item ) {
			$product = $item->get_product();
			if ( ! $product->get_meta( '_paddle_one_off_purchase', true ) ) {
				return sprintf( __( 'Thank you. Your order has been received and you are subscribed. You can cancel your subscription at any time from %s.', 'paddle' ), '<a href="' . esc_url( wc_get_account_endpoint_url( 'paddle-subscriptions' ) ) . '" target="_blank"><strong>' . __( 'your account', 'paddle' ) . '</strong></a>' );
			}
		}

		return $message;
	}

}
