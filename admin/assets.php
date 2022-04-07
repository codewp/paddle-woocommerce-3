<?php
defined( 'ABSPATH' ) || exit;

class Paddle_WC_Admin_Assets {

	public function init() {
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_scripts' ), 15 );
	}

	public function load_scripts() {
		$screen    = get_current_screen();
        $screen_id = $screen ? $screen->id : '';

        if ( 'toplevel_page_asnp-paddle' === $screen_id ) {
			$this->register_polyfills();

            wp_enqueue_style(
                'asnp-paddle-admin',
                $this->get_url( 'admin/style', 'css' )
            );
            wp_enqueue_script(
                'asnp-paddle-admin',
                $this->get_url( 'admin/admin/index', 'js' ),
                array(
					'wp-element',
					'wp-hooks',
					'wp-i18n',
					'wp-api-fetch',
				),
                paddle_wc()->version,
                true
            );

			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations( 'asnp-paddle-admin', 'paddle', ASNP_PADDLE_WC_ABSPATH . 'languages' );
			}
        }
	}

	public function get_url( $file, $ext ) {
		return plugins_url( $this->get_path( $ext ) . $file . '.' . $ext, ASNP_PADDLE_WC_PLUGIN_FILE );
    }

    protected function get_path( $ext ) {
        return 'css' === $ext ? 'assets/css/' : 'assets/js/';
    }

	protected function register_polyfills() {
		$handles = array( 'element', 'i18n', 'hooks', 'api-fetch' );
		foreach ( $handles as $handle ) {
			if ( ! wp_script_is( 'wp-' . $handle, 'registered' ) ) {
				wp_register_script( 'wp-' . $handle, $this->get_url( 'vendor/' . $handle, 'js' ), array(),  paddle_wc()->version, true );
			}
		}
	}

}
