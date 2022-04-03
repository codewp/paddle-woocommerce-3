<?php
defined( 'ABSPATH' ) || exit;

class Paddle_Menu {

    public function init() {
        add_filter( 'woocommerce_account_menu_items', array( $this, 'account_menu_item' ) );
    }

    public function account_menu_item( $items ) {
        if ( array_key_exists( 'orders', $items ) ) {
            $items = wcs_array_insert_after( 'orders', $items, 'paddle-subscriptions', __( 'Subscriptions', 'paddle' ) );
        } else {
            $items['paddle-subscriptions'] = __( 'Subscriptions', 'paddle' );
        }

        return $items;
    }

}
