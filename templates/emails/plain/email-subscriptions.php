<?php
/**
 * Order subscripitions shown in emails.
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_before_order_subscriptions', $order, $sent_to_admin, $plain_text, $email );

echo __( 'Subscriptions', 'paddle' ) . "\n\n";

if ( ! $sent_to_admin ) {
	echo esc_html__( 'You are subscribed to the below products, and you can cancel your subscription at any time from your account.', 'paddle' ) . "\n\n";
}

foreach ( $subscriptions as $item ) {
	echo wp_kses_post( __( 'Product', 'woocommerce' ) . "\t " . $item->get_name() ) . "\n";
}

echo "==========\n\n";

if ( ! empty( $subscription->next_payment_amount ) ) {
	echo wp_kses_post( __( 'Next Payment Amount', 'paddle' ) . "\t " . wc_price( $subscription->next_payment_amount, array( 'currency' => $order->get_currency() ) ) ) . "\n";
}

echo '=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=';

do_action( 'woocommerce_email_after_order_subscriptions', $order, $sent_to_admin, $plain_text, $email );

echo "\n\n";
