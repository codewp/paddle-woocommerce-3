<?php
defined( 'ABSPATH' ) or exit;

class Paddle_WC_Webhooks {

    protected $log;

    protected $log_enabled;

    protected $status_header = 500;

    public function __construct( WC_Logger $log, $log_enabled = true ) {
        $this->log         = $log;
        $this->log_enabled = $log_enabled;
    }

    public function init() {
        add_action( 'woocommerce_api_paddle_webhook', array( $this, 'webhook' ) );
    }

    public function set_status_header( $value ) {
        $this->status_header = $value;
    }

    public function webhook() {
        $this->set_status_header( 500 );

        if ( 1 != Paddle_WC_API::check_webhook_signature() ) {
            status_header( $this->status_header );
            exit;
        }

        $action = isset( $_POST['alert_name '] ) ? sanitize_text_field( $_POST['alert_name '] ) : '';

        if ( ! empty( $action ) && is_callable( array( $this, $action ) ) ) {
            $this->{$action}();
        }

        if ( ! empty( $action ) ) {
            do_action( 'paddle_wc_webhook_' . $action );
        }

        status_header( $this->status_header );

        exit;
    }

    public function payment_succeeded() {
        $paddle_order_id = isset( $_POST['order_id'] ) ? sanitize_text_field( $_POST['order_id'] ) : '';
        $order           = ! empty( $_POST['passthrough'] ) ? paddle_wc_get_order_by_passthrough( $_POST['passthrough'] ) : false;

        if ( empty( $paddle_order_id ) || ! $order ) {
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
            if ( $this->log_enabled ) {
                $this->log->warning( 'Could not find an order related to Paddle order #' . sanitize_key( $paddle_order_id ) . ' to refund it, please take an appropriate action.', array( 'source' => 'paddle' ) );
            }
            $this->set_status_header( 200 );
            return;
        }

        $refunded = $order->update_status( 'refunded', 'Refunded from Paddle.' );

        if ( $refunded ) {
            if ( $this->log_enabled ) {
                $this->log->info( 'Order #' . sanitize_key( $order->get_id() ) . ' related to Paddle order #' . sanitize_key( $paddle_order_id ) . ' refunded successfully.', array( 'source' => 'paddle' ) );
            }
            do_action( 'paddle_wc_payment_refunded_successfully', $paddle_order_id, $order );
            $this->set_status_header( 200 );
        } elseif ( $this->log_enabled ) {
            $this->log->warning( 'Paddle order #' . sanitize_key( $paddle_order_id ) . ' refunded on paddle but could not be refunded on WooCommerce, so please take appropriate action.', array( 'source' => 'paddle' ) );
        }
    }

}
