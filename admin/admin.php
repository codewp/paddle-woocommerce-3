<?php
defined( 'ABSPATH' ) || exit;

class Paddle_WC_Admin {

	protected $menu;

	protected $assets;

	public function init() {
		$this->register_dependencies();

		$this->menu = new Paddle_WC_Admin_Menu();
		$this->menu->init();

		$this->assets = new Paddle_WC_Admin_Assets();
		$this->assets->init();

		Paddle_WC_Admin_Product_Hooks::init();
	}

	protected function register_dependencies() {
		include_once dirname( __FILE__ ) . '/menu.php';
		include_once dirname( __FILE__ ) . '/assets.php';
		include_once dirname( __FILE__ ) . '/product-hooks.php';
	}

}
