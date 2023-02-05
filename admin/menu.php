<?php
defined( 'ABSPATH' ) || exit;

class Paddle_WC_Admin_Menu
{

	protected $menus = array();

	public function init() {
		add_action( 'admin_menu', array( $this, 'menus' ), 99 );
	}

	/**
	 * Getting all of admin-face menus of plugin.
	 *
	 * @since  1.0.0
	 * @return array
	 */
	public function get_menus() {
		return $this->menus;
	}

	public function menus() {
		$this->menus['paddle'] = add_submenu_page(
			'woocommerce',
			__( 'Paddle', 'paddle' ),
            __( 'Paddle', 'paddle' ),
            apply_filters( 'asnp_paddle_menu_capability', 'manage_woocommerce' ),
            'asnp-paddle',
            array( $this, 'create_menu' )
		);
	}

	public function create_menu() {
		?>
		<div id="asnp-paddle" class="wrap"></div>
		<?php
	}

}
