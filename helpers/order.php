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

/**
 * Renew order downloadable files expire time.
 *
 * @param  int|WC_Order $order
 *
 * @return void
 */
function paddle_wc_renew_order_downloadable_files( $order ) {
	$order = is_numeric( $order ) ? wc_get_order( $order ) : $order;
	if ( ! $order || ! $order instanceof WC_Order ) {
		throw new Exception( 'Order not found.' );
	}

	$data_store = WC_Data_Store::load( 'customer-download' );
	$downloads  = $data_store->get_downloads(
		array(
			'order_id' => $order->get_id(),
			'orderby'  => 'product_id',
		)
	);
	if ( empty( $downloads ) ) {
		return;
	}

	foreach ( $downloads as $download ) {
		$product = wc_get_product( $download->get_product_id() );
		if ( ! $product ) {
			continue;
		}

		$expiry = $product->get_download_expiry();
		$download->set_access_expires( strtotime( current_time( 'mysql', true ) . ' + ' . $expiry . ' DAY' ) );
		$download->save();
	}
}

function paddle_wc_renew_order( $order, $subscription_id = null, $paddle_subscription_id = null ) {
	$order = is_numeric( $order ) ? wc_get_order( $order ) : $order;
	if ( ! $order || ! $order instanceof WC_Order ) {
		throw new Exception( 'Order not found.' );
	}

	$transient = get_transient( '_paddle_renewed_order_' . $order->get_id() );
	if ( $transient ) {
		return;
	}
	set_transient( '_paddle_renewed_order_' . $order->get_id(), true, HOUR_IN_SECONDS );

	$new_order = wc_create_order(
		array(
			'customer_id' => $order->get_customer_id(),
			'parent'      => $order->get_id(),
		)
	);

	$new_order->update_meta_data( '_renewed_order', $order->get_id(), true );
	if ( ! empty( $subscription_id ) ) {
		$new_order->update_meta_data( '_subscription_id', $subscription_id, true );
	}
	if ( ! empty( $paddle_subscription_id ) ) {
		$new_order->update_meta_data( '_paddle_subscription_id', $paddle_subscription_id, true );
	}
	$new_order->set_currency( $order->get_currency() );
	$new_order->set_billing_first_name( $order->get_billing_first_name() );
	$new_order->set_billing_last_name( $order->get_billing_last_name() );
	$new_order->set_billing_company( $order->get_billing_company() );
	$new_order->set_billing_address_1( $order->get_billing_address_1() );
	$new_order->set_billing_address_2( $order->get_billing_address_2() );
	$new_order->set_billing_city( $order->get_billing_city() );
	$new_order->set_billing_state( $order->get_billing_state() );
	$new_order->set_billing_postcode( $order->get_billing_postcode() );
	$new_order->set_billing_country( $order->get_billing_country() );
	$new_order->set_billing_email( $order->get_billing_email() );
	$new_order->set_billing_phone( $order->get_billing_phone() );

	// VAT info.
	$vat_number = $order->get_meta( 'vat_number' );
	$vat_company_name = $order->get_meta( 'vat_company_name' );
	$vat_country = $order->get_meta( 'vat_country' );
	$vat_city = $order->get_meta( 'vat_city' );
	$vat_street = $order->get_meta( 'vat_street' );
	$vat_postcode = $order->get_meta( 'vat_postcode' );

	if ( ! empty( $vat_number ) ) {
		$new_order->add_meta_data( 'vat_number', $vat_number, true );
	}

	if ( ! empty( $vat_company_name ) ) {
		$new_order->add_meta_data( 'vat_company_name', $vat_company_name, true );
	}

	if ( ! empty( $vat_country ) ) {
		$new_order->add_meta_data( 'vat_country', $vat_country, true );
	}

	if ( ! empty( $vat_city ) ) {
		$new_order->add_meta_data( 'vat_city', $vat_city, true );
	}

	if ( ! empty( $vat_street ) ) {
		$new_order->add_meta_data( 'vat_street', $vat_street, true );
	}

	if ( ! empty( $vat_postcode ) ) {
		$new_order->add_meta_data( 'vat_postcode', $vat_postcode, true );
	}

	// Set new order items.
	foreach ( $order->get_items() as $item ) {
		// Ignore one-off purchase products.
		$product = $item->get_product();
		if ( ! $product || $product->get_meta( '_paddle_one_off_purchase', true ) ) {
			continue;
		}

		$new_order->add_product(
			$product,
			$item->get_quantity(),
			array(
				'total'    => $item->get_total(),
				'subtotal' => $item->get_subtotal(),
			)
		);
	}

	$new_order->calculate_totals();
	$new_order->save();

	do_action( 'paddle_wc_order_renewed', $new_order, $order, $subscription_id, $paddle_subscription_id );

	$new_order->payment_complete();

	return $new_order;
}

function paddle_wc_get_order_subscription_items( $order ) {
	$order = is_numeric( $order ) ? wc_get_order( $order ) : $order;
	if ( ! $order || ! $order instanceof WC_Order ) {
		throw new Exception( 'Order not found.' );
	}

	$subscriptions = array();
	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( $product && ! $product->get_meta( '_paddle_one_off_purchase', true ) ) {
			$subscriptions[] = $item;
		}
	}

	return $subscriptions;
}

function paddle_wc_has_order_subscription_items( $order ) {
	$order = is_numeric( $order ) ? wc_get_order( $order ) : $order;
	if ( ! $order || ! $order instanceof WC_Order ) {
		throw new Exception( 'Order not found.' );
	}

	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( $product && ! $product->get_meta( '_paddle_one_off_purchase', true ) ) {
			return true;
		}
	}

	return false;
}
