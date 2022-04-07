<?php
defined( 'ABSPATH' ) || exit;

class Paddle_WC_Admin_Menu
{

	protected $menus = array();

	public function init() {
		add_action( 'admin_menu', array( $this, 'menus' ) );
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
		$this->menus['paddle'] = add_menu_page(
	        __( 'Paddle', 'paddle' ),
            __( 'Paddle', 'paddle' ),
            apply_filters( 'asnp_paddle_menu_capability', 'manage_options' ),
            'paddle',
            array( $this, 'create_menu' ),
            ASNP_PADDLE_WC_PLUGIN_URL . 'assets/images/menu-icon.svg'
        );
	}

	public function create_menu() {
		?>
		<div id="asnp-paddle-wrapper" class="asnp-paddle-wrapper">
			<div id="asnp-paddle">
			</div>
		</div>
		<?php
	}

}
