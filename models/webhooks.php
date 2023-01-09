<?php
defined( 'ABSPATH' ) or exit;

class Paddle_WC_Webhooks {

    protected $status_header = 500;

    public function init() {
        add_action( 'woocommerce_api_paddle_webhook', array( $this, 'webhook' ) );
    }

    public function set_status_header( $value ) {
        $this->status_header = $value;
    }

    public function webhook() {
        $this->set_status_header( 500 );

        if ( 1 != Paddle_WC_API::check_webhook_signature() ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( 'Paddle webhook wrong signature.', array( 'source' => 'paddle' ) );
            }
            status_header( $this->status_header );
            exit;
        }

        $action = isset( $_POST['alert_name'] ) ? sanitize_key( $_POST['alert_name'] ) : '';

        if ( ! empty( $action ) && is_callable( array( $this, $action ) ) ) {
            $this->{$action}();
        } elseif ( paddle_wc()->log_enabled ) {
            paddle_wc()->log->error( 'Unsupported webhook action: ' . $action, array( 'source' => 'paddle' ) );
        }

        if ( ! empty( $action ) ) {
            do_action( 'paddle_wc_webhook_' . $action );
        }

        status_header( $this->status_header );

        exit;
    }

    public function payment_succeeded() {
        $paddle_order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
        $passthrough     = ! empty( $_POST['passthrough'] ) ? sanitize_text_field( $_POST['passthrough'] ) : '';
        $order           = ! empty( $passthrough ) ? paddle_wc_get_order_by_passthrough( $passthrough ) : false;

        if ( empty( $paddle_order_id ) || ! $order ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( 'Paddle payment success order not found for Paddle order #' . $paddle_order_id . ' and passthrough: ' . $passthrough . '.', array( 'source' => 'paddle' ) );
            }
            $this->set_status_header( 200 );
            return;
        }

        $order->add_meta_data( '_paddle_order_id', $paddle_order_id, true );
        $order->add_order_note( 'Paddle Order ID: ' . $paddle_order_id );

		do_action( 'paddle_wc_payment_succeeded', $order, $paddle_order_id, $_POST );

        $this->set_status_header( 200 );
    }

    public function payment_refunded() {
        if ( ! isset( $_POST['refund_type'] ) || 'full' !== $_POST['refund_type'] ) {
            return;
        }

        $paddle_order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
        $passthrough     = isset( $_POST['passthrough'] ) ? $_POST['passthrough'] : '';
        $order           = paddle_wc_get_order( $passthrough, $paddle_order_id );
        if ( ! $order ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->warning( 'Could not find an order related to Paddle order #' . $paddle_order_id . ' to refund it, please take an appropriate action.', array( 'source' => 'paddle' ) );
            }
            $this->set_status_header( 200 );
            return;
        }

        $refunded = $order->update_status( 'refunded', 'Refunded from Paddle.' );

        if ( $refunded ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->info( 'Order #' . sanitize_key( $order->get_id() ) . ' related to Paddle order #' . $paddle_order_id . ' refunded successfully.', array( 'source' => 'paddle' ) );
            }
            do_action( 'paddle_wc_order_refunded_successfully', $order );
            $this->set_status_header( 200 );
        } elseif ( paddle_wc()->log_enabled ) {
            paddle_wc()->log->warning( 'Paddle order #' . $paddle_order_id  . ' refunded on paddle but could not be refunded on WooCommerce, so please take appropriate action.', array( 'source' => 'paddle' ) );
        }
    }

    public function subscription_created() {
        $subscription_id = isset( $_POST['subscription_id'] ) ? sanitize_text_field( $_POST['subscription_id'] ) : '';
        $passthrough     = ! empty( $_POST['passthrough'] ) ? sanitize_text_field( $_POST['passthrough'] ) : '';
        $order           = ! empty( $passthrough ) ? paddle_wc_get_order_by_passthrough( $passthrough ) : false;

        if ( empty( $subscription_id ) || ! $order ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( 'Paddle subscription created: order not found for Paddle subscription #' . $subscription_id . ' and passthrough: ' . $passthrough, array( 'source' => 'paddle' ) );
            }
            $this->set_status_header( 200 );
            return;
        }

		try {
			$id = $this->add_subscription( $subscription_id, $_POST, $order );
			if ( 0 < $id ) {
				do_action( 'paddle_wc_subscription_created', $id, $subscription_id, $_POST );
			}
		} catch ( Exception $e ) {
			if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( $e->getMessage(), array( 'source' => 'paddle' ) );
            }
		}

        $this->set_status_header( 200 );
    }

    public function subscription_updated() {
        $subscription_id = isset( $_POST['subscription_id'] ) ? sanitize_text_field( $_POST['subscription_id'] ) : '';
        if ( empty( $subscription_id ) ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( 'Paddle subscription updated: subscription_id is required.', array( 'source' => 'paddle' ) );
            }
            $this->set_status_header( 200 );
            return;
        }

		try {
			$id = $this->add_subscription( $subscription_id, $_POST );
			if ( 0 < $id ) {
				do_action( 'paddle_wc_subscription_updated', $id, $subscription_id, $_POST );
			}
		} catch ( Exception $e ) {
			if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( $e->getMessage(), array( 'source' => 'paddle' ) );
            }
		}

        $this->set_status_header( 200 );
    }

    public function subscription_cancelled() {
        $subscription_id = isset( $_POST['subscription_id'] ) ? sanitize_text_field( $_POST['subscription_id'] ) : '';
        if ( empty( $subscription_id ) ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( 'Paddle subscription cancelled: subscription_id is required.', array( 'source' => 'paddle' ) );
            }
            $this->set_status_header( 200 );
            return;
        }

		try {
			$id = $this->add_subscription( $subscription_id, $_POST );
			if ( 0 < $id ) {
				do_action( 'paddle_wc_subscription_cancelled', $id, $subscription_id, $_POST );
			}
		} catch ( Exception $e ) {
			if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( $e->getMessage(), array( 'source' => 'paddle' ) );
            }
		}

        $this->set_status_header( 200 );
    }

	public function subscription_payment_succeeded() {
        $subscription_id = isset( $_POST['subscription_id'] ) ? sanitize_text_field( $_POST['subscription_id'] ) : '';
		$paddle_order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
        $passthrough     = ! empty( $_POST['passthrough'] ) ? sanitize_text_field( $_POST['passthrough'] ) : '';
        $order           = ! empty( $passthrough ) ? paddle_wc_get_order_by_passthrough( $passthrough ) : false;

        if ( empty( $subscription_id ) || ! $order ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( 'Paddle subscription payment succeeded: order not found for Paddle subscription #' . $subscription_id . ' and passthrough: ' . $passthrough, array( 'source' => 'paddle' ) );
            }
            $this->set_status_header( 200 );
            return;
        }

		try {
			$order->payment_complete();
			$id = $this->add_subscription( $subscription_id, $_POST, $order );
			$order->add_meta_data( '_paddle_order_id', $paddle_order_id, true );
			$order->add_order_note( 'Paddle Order ID: ' . $paddle_order_id );
			paddle_wc_renew_order_downloadable_files( $order );
			if ( 0 < $id ) {
				do_action( 'paddle_wc_subscription_payment_succeeded', $id, $subscription_id, $_POST );
			}
		} catch ( Exception $e ) {
			if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( $e->getMessage(), array( 'source' => 'paddle' ) );
            }
		}

		do_action( 'paddle_wc_subscription_payment_succeeded', $order, $subscription_id, $paddle_order_id, $_POST );

        $this->set_status_header( 200 );
    }

	public function subscription_payment_refunded() {
		if ( ! isset( $_POST['refund_type'] ) || 'full' !== $_POST['refund_type'] ) {
            return;
        }

		$subscription_id = isset( $_POST['subscription_id'] ) ? sanitize_text_field( $_POST['subscription_id'] ) : '';
        $paddle_order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
        $passthrough     = isset( $_POST['passthrough'] ) ? $_POST['passthrough'] : '';
        $order           = paddle_wc_get_order( $passthrough, $paddle_order_id );
        if ( ! $order ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->warning( 'Could not find an order related to Paddle order #' . $paddle_order_id . ' to refund it, please take an appropriate action.', array( 'source' => 'paddle' ) );
            }
            $this->set_status_header( 200 );
            return;
        }

		try {
			$id = $this->add_subscription( $subscription_id, $_POST, $order );
			if ( 0 < $id ) {
				do_action( 'paddle_wc_subscription_payment_refunded', $id, $subscription_id, $_POST );
			}
		} catch ( Exception $e ) {
			if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->error( $e->getMessage(), array( 'source' => 'paddle' ) );
            }
		}

        $refunded = $order->update_status( 'refunded', 'Refunded from Paddle.' );

        if ( $refunded ) {
            if ( paddle_wc()->log_enabled ) {
                paddle_wc()->log->info( 'Order #' . sanitize_key( $order->get_id() ) . ' related to Paddle order #' . $paddle_order_id . ' refunded successfully.', array( 'source' => 'paddle' ) );
            }
            do_action( 'paddle_wc_order_refunded_successfully', $order );
            $this->set_status_header( 200 );
        } elseif ( paddle_wc()->log_enabled ) {
            paddle_wc()->log->warning( 'Paddle order #' . $paddle_order_id  . ' refunded on paddle but could not be refunded on WooCommerce, so please take appropriate action.', array( 'source' => 'paddle' ) );
        }
	}

	protected function add_subscription( $subscription_id, $args, $order = null ) {
		if ( empty( $subscription_id ) ) {
			throw new Exception( 'Subscription update: Subscription ID is required.' );
		}

		if ( empty( $args ) ) {
			throw new Exception( 'Subscription update: Update args are required to update the subscription #' . sanitize_text_field( $subscription_id ) );
		}

		$data = $this->sanitize_subscription_data( $args );
		if ( empty( $data ) ) {
			throw new Exception( 'Subscription update: There is not any data to update the subscription #' . absint( $subscription->id ) . ' for the paddle subscription #' . sanitize_text_field( $subscription_id ) );
		}

		if ( $order ) {
			$data['order_id'] = absint( $order->get_id() );
			$data['user_id'] = absint( $order->get_user_id() );
		}

		$subscription = paddle_wc()->subscriptions->get_item_by( 'subscription_id', $subscription_id );
        if ( $subscription ) {
			$data['id'] = absint( $subscription->id );
        }

		return paddle_wc()->subscriptions->add( $data );
	}

	protected function sanitize_subscription_data( $args ) {
		if ( empty( $args ) ) {
			throw new Exception( 'Subscription data is empty.' );
		}

		$data = array();
		foreach ( $args as $key => $value ) {
			switch ( $key ) {
				case 'status':
				case 'subscription_id':
				case 'subscription_plan_id':
				case 'paddle_user_id':
				case 'next_bill_date':
				case 'currency':
				case 'unit_price':
					$data[ $key ] = sanitize_text_field( $value );
					break;

				case 'cancel_url':
				case 'update_url':
					$data[ $key ] = esc_url_raw( $value );
					break;

				case 'user_id':
					$data[ 'paddle_' . $key ] = sanitize_text_field( $value );
					break;
			}
		}

		return $data;
	}

}
