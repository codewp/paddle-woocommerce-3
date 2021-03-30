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

        $action = isset( $_POST['alert_name '] ) ? sanitize_key( $_POST['alert_name '] ) : '';

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
            do_action( 'paddle_wc_payment_refunded_successfully', $paddle_order_id, $order );
            $this->set_status_header( 200 );
        } elseif ( paddle_wc()->log_enabled ) {
            paddle_wc()->log->warning( 'Paddle order #' . $paddle_order_id  . ' refunded on paddle but could not be refunded on WooCommerce, so please take appropriate action.', array( 'source' => 'paddle' ) );
        }
    }

}
