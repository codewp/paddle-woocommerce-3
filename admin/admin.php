<?php
defined( 'ABSPATH' ) || exit;

class Paddle_WC_Admin {

	public function init() {
		$this->register_dependencies();

		$this->menu = new Paddle_WC_Admin_Menu();
		$this->menu->init();

		$this->assets = new Paddle_WC_Admin_Assets();
		$this->assets->init();
	}

	protected function register_dependencies() {
		include_once dirname( __FILE__ ) . '/menu.php';
		include_once dirname( __FILE__ ) . '/assets.php';
	}

}