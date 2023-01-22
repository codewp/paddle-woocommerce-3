<?php
defined( 'ABSPATH' ) || exit;

class Paddle_WC_Emails {

	public function init() {
		add_action( 'woocommerce_email_order_details', array( __CLASS__, 'order_subscriptions' ), 2, 4 );
	}

	public function order_subscriptions( $order, $sent_to_admin = false, $plain_text = false, $email = '' ) {
		if (
			! $order->has_status( 'completed' ) ||
			! is_a( $email, 'WC_Email_Customer_Completed_Order' )
		) {
			return;
		}

		$subscription = paddle_wc()->subscriptions->get_item_by( 'order_id', $order->get_id() );
		if ( ! $subscription ) {
			return;
		}

		$subscriptions = paddle_wc_get_order_subscription_items( $order );
		if ( empty( $subscriptions ) ) {
			return;
		}

		if ( $plain_text ) {
			paddle_get_template(
				'emails/plain/email-subscriptions.php',
				array(
					'order'         => $order,
					'sent_to_admin' => $sent_to_admin,
					'plain_text'    => $plain_text,
					'email'         => $email,
					'subscription'  => $subscription,
					'subscriptions' => $subscriptions,
				)
			);
		} else {
			paddle_get_template(
				'emails/email-subscriptions.php',
				array(
					'order'         => $order,
					'sent_to_admin' => $sent_to_admin,
					'plain_text'    => $plain_text,
					'email'         => $email,
					'subscription'  => $subscription,
					'subscriptions' => $subscriptions,
				)
			);
		}
	}

}
