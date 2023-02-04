<?php
defined( 'ABSPATH' ) || exit;

class Paddle_WC_Emails {

	public function init() {
		add_action( 'woocommerce_email_order_details', array( $this, 'order_subscriptions' ), 2, 4 );
		add_filter( 'woocommerce_email_classes', array( $this, 'email_classes' ) );
		add_filter( 'woocommerce_email_actions', array( $this, 'email_actions' ) );
	}

	public function email_classes( $emails ) {
		include_once ASNP_PADDLE_WC_ABSPATH . 'emails/cancelled-subscription.php';

		$emails['Paddle_WC_Email_Cancelled_Subscription'] = new Paddle_WC_Email_Cancelled_Subscription();

		return $emails;
	}

	public function email_actions( $actions ) {
		$actions[] = 'paddle_wc_subscription_cancelled';
		return $actions;
	}

	public function order_subscriptions( $order, $sent_to_admin = false, $plain_text = false, $email = '' ) {
		if (
			! $order->has_status( array( 'completed', 'processing' ) ) ||
			(
				! is_a( $email, 'WC_Email_Customer_Completed_Order' ) &&
				! is_a( $email, 'WC_Email_New_Order' ) &&
				! is_a( $email, 'Paddle_WC_Email_Cancelled_Subscription' )
			)
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
