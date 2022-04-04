<?php
/*
 * Plugin Name: Paddle
 * Plugin URI: https://github.com/hasinhayder/paddle-woocommerce-3
 * Description: Paddle Payment Gateway for WooCommerce
 * Version: 4.0.0
 * Author: Paddle.com (Improvements by ThemeBucket)
 * Author URI: https://github.com/hasinhayder
 */

defined('ABSPATH') or die("Plugin must be run as part of wordpress");

if (!class_exists('Paddle_WC')) :

/**
 * Main Paddle_WC Class.
 *
 * @class Paddle_WC
 * @version	3.0.0
 */
final class Paddle_WC {

	public $version = '4.0.0';

	/**
	 * Instance of our settings object.
	 *
	 * @var Paddle_WC_Settings
	 */
	private $settings;

	/**
	 * Instance of our checkout handler.
	 *
	 * @var Paddle_WC_Checkout
	 */
	private $checkout;

	/**
	 * The gateway that handles the payments and the admin setup.
	 *
	 * @var Paddle_WC_Gateway
	 */
	private $gateway;

	/**
	 * The single instance of the class.
	 *
	 * @var Paddle_WC
	 */
	private static $_instance = null;

	/**
	 * The webhooks that handles Paddle webhook requests.
	 *
	 * @var Paddle_WC_Webhooks
	 */
	public $webhooks;

	/**
	 * Logger.
	 *
	 * @var WC_Logger
	 */
	public $log;

	public $log_enabled = true;

	public $subscriptions;

	/**
	 * Main Paddle_WC Instance.
	 * Ensures only one instance of WooCommerce is loaded or can be loaded.
	 *
	 * @static
	 * @return Paddle_WC - Main instance.
	 */
	public static function instance() {
		if (is_null(self::$_instance)) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Paddle_WC Constructor.
	 */
	public function __construct() {
		$this->register_init_callback();
	}

	/**
	 * Registers the init callback for when WP is done loading plugins.
	 */
	private function register_init_callback() {
		add_action('plugins_loaded', array($this, 'on_wp_plugins_loaded'));
	}

	/**
	 * Callback called during plugin load to setup the Paddle_WC.
	 */
	public function on_wp_plugins_loaded() {
		// Don't load extension if WooCommerce is not active
		if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

			include_once dirname( __FILE__ ) . '/helpers/utils.php';
			include_once dirname( __FILE__ ) . '/helpers/install.php';
			include_once dirname( __FILE__ ) . '/helpers/notice.php';
			include_once dirname( __FILE__ ) . '/helpers/order.php';
			include_once dirname( __FILE__ ) . '/models/db.php';
			include_once dirname( __FILE__ ) . '/models/db-subscriptions.php';
			include_once dirname( __FILE__ ) . '/models/menu.php';
			include_once dirname( __FILE__ ) . '/models/api.php';
			include_once dirname( __FILE__ ) . '/models/checkout.php';
			include_once dirname( __FILE__ ) . '/models/gateway.php';
			include_once dirname( __FILE__ ) . '/models/settings.php';
			include_once dirname( __FILE__ ) . '/models/webhooks.php';

			// Register the Paddle gateway with WC
			add_filter('woocommerce_payment_gateways', array($this, 'on_register_woocommerce_gateways'));

			$this->log = wc_get_logger();

			// Add the checkout scripts and actions, if enabled
			$this->settings = new Paddle_WC_Settings();
			if($this->settings->get('enabled') == 'yes') {

				// Setup checkout object and register intercepts to render page content
				$this->checkout = new Paddle_WC_Checkout($this->settings);
				$this->checkout->register_callbacks();

			}

			$this->subscriptions = new Paddle_DB_Subscriptions();

			$this->menu = new Paddle_Menu();
			$this->menu->init();

			// Always setup the gateway as its needed to change admin settings
			$this->gateway = new Paddle_WC_Gateway($this->settings);
			$this->gateway->register_callbacks();

			$this->webhooks = new Paddle_WC_Webhooks();
			$this->webhooks->init();
		}
	}

	/**
	 * Callback called during plugin load to setup the Paddle_WC.
	 */
	public function on_register_woocommerce_gateways($methods) {
		$methods[] = 'Paddle_WC_Gateway';
		return $methods;
	}
}

endif;

function paddle_wc() {
	return Paddle_WC::instance();
}

$GLOBALS['paddle_wc'] = paddle_wc();
