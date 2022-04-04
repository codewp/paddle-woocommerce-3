<?php
defined( 'ABSPATH' ) || exit;

class Paddle_Menu {

    protected $query_vars = array();

    public function init() {
        if ( ! is_admin() ) {
            add_filter( 'woocommerce_get_query_vars', array( $this, 'add_query_vars' ) );
            add_filter( 'woocommerce_account_menu_items', array( $this, 'add_menu_item' ) );
            add_action( 'woocommerce_account_paddle-subscriptions_endpoint', array( $this, 'endpoint_content' ) );
        }

        $this->init_query_vars();
    }

    public function init_query_vars() {
		$this->query_vars['paddle-subscriptions'] = 'paddle-subscriptions';
	}

    public function add_query_vars( $query_vars ) {
        return array_merge( $query_vars, $this->query_vars );
    }

    public function add_menu_item( $items ) {
        if ( empty( $this->query_vars['paddle-subscriptions'] ) ) {
			return $items;
		}

        if ( array_key_exists( 'orders', $items ) ) {
            $items = paddle_array_insert_after( 'orders', $items, 'paddle-subscriptions', __( 'Subscriptions', 'paddle' ) );
        } else {
            $items['paddle-subscriptions'] = __( 'Subscriptions', 'paddle' );
        }

        return $items;
    }

    public function endpoint_content() {
        echo '<div id="paddle-subscriptions">Paddle Subscriptions</div>';
    }

}
