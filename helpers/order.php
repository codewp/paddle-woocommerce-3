<?php
defined( 'ABSPATH' ) or exit;

/**
 * Get a WooCommerce order from passthrough or Paddle order ID.
 *
 * @param  string $passthrough
 * @param  string $paddle_order_id
 *
 * @return WC_Order|false
 */
function paddle_wc_get_order( $passthrough, $paddle_order_id ) {
    $order = false;
    if ( ! empty( $passthrough ) ) {
        $order = paddle_wc_get_order_by_passthrough( $passthrough );
    }

    if ( $order ) {
        return $order;
    }

    return paddle_wc_get_order_by_paddle_order( $paddle_order_id );
}

/**
 * Get WooCommerce order from the Paddle passthrough field.
 *
 * @param  string $passthrough
 *
 * @return WC_Order|false
 */
function paddle_wc_get_order_by_passthrough( $passthrough ) {
    if ( empty( $passthrough ) ) {
        return false;
    }

    $passthrough = json_decode( base64_decode( $passthrough ) );
    if ( empty( $passthrough ) || ! isset( $passthrough->order_id ) || 0 >= (int) $passthrough->order_id ) {
        return false;
    }

    return new WC_Order( (int) $passthrough->order_id );
}

/**
 * Get a WooCommerce order related to the given Paddle order.
 *
 * @param  string $paddle_order_id
 *
 * @return WC_Order|false
 */
function paddle_wc_get_order_by_paddle_order( $paddle_order_id ) {
    if ( empty( $paddle_order_id ) ) {
        return false;
    }

    global $wpdb;

    $order_id = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s LIMIT 1", array( '_paddle_order_id', $paddle_order_id ) ) );
    $order_id = ! empty( $order_id ) ? (int) $order_id[0] : 0;
    if ( 0 >= $order_id ) {
        return false;
    }

    return new WC_Order( $order_id );
}
