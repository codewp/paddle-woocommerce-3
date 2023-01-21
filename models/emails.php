<?php
defined( 'ABSPATH' ) or exit;

class Paddle_WC_Emails {

	public static function init() {
		add_action( 'woocommerce_email_order_details', array( __CLASS__, 'subscribed_message' ), 2, 4 );
	}

	public static function subscribed_message( $order, $sent_to_admin = false, $plain_text = false, $email = '' ) {
		if (
			$sent_to_admin ||
			! $order->has_status( 'completed' ) ||
			! is_a( $email, 'WC_Email_Customer_Completed_Order' )
		) {
			return;
		}

		if ( $plain_text ) {
			echo esc_html__( 'You are subscribed, and you can cancel your subscription at any time from your account.', 'paddle' );
		} else {
			echo '<p>' . sprintf( __( 'You are subscribed, and you can cancel your subscription at any time from %s.', 'paddle' ), '<a href="' . esc_url( wc_get_account_endpoint_url( 'paddle-subscriptions' ) ) . '" target="_blank"><strong>' . __( 'your account', 'paddle' ) . '</strong></a>' ) . '</p>';
		}
	}

}
