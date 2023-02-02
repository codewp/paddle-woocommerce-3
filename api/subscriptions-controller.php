<?php
defined( 'ABSPATH' ) || exit;

class Paddle_Subscriptions_Controller extends Paddle_Base_Controller {

	protected $rest_base = 'subscriptions';

	public function register_routes() {
		register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => array( $this, 'get_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
            )
		);

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/account',
            array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_account_items' ),
					'permission_callback' => array( $this, 'get_account_items_permissions_check' ),
					'args'                => $this->get_collection_params(),
				),
            )
		);
	}

	public function get_account_items_permissions_check( $request ) {
		if ( ! is_user_logged_in() || ! current_user_can( 'read' ) ) {
			return new \WP_Error( 'woocommerce_paddle_rest_cannot_view', __( 'Sorry, you cannot list resources.', 'paddle' ), array( 'status' => rest_authorization_required_code() ) );
		}

		return true;
	}

	/**
	 * Get current user subscriptions.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$page = ! empty( $request['page'] ) ? absint( $request['page'] ) : 1;
		try {
			$response = paddle_wc()->subscriptions->get_items( array(
				'offset'   => $page * 20 - 20,
				'paginate' => true,
				'number'   => 20,
				'orderby'  => 'order_id',
				'search'   => ! empty( $request['search'] ) ? sanitize_text_field( $request['search'] ) : '',
			) );

			$data = array(
				'items' => array(),
				'pages' => $response['pages'],
			);

			if ( 0 < $response['total'] ) {
				foreach ( $response['items'] as $item ) {
					$data['items'][] = $this->prepare_item_for_response( $item, $request );
				}
			}

			return new \WP_REST_Response( $data );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'woocommerce_paddle_rest_error_occurred', $e->getMessage(), array( 'status' => rest_authorization_required_code() ) );
		}
	}

	/**
	 * Get current user subscriptions.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_account_items( $request ) {
		$page = ! empty( $request['page'] ) ? absint( $request['page'] ) : 1;
		try {
			$response = paddle_wc()->subscriptions->get_items( array(
				'user_id'  => get_current_user_id(),
				'offset'   => $page * 10 - 10,
				'paginate' => true,
				'number'   => 10,
				'orderby'  => 'order_id',
			) );

			$data = array(
				'items' => array(),
				'pages' => $response['pages'],
			);

			if ( 0 < $response['total'] ) {
				foreach ( $response['items'] as $item ) {
					$data['items'][] = $this->prepare_item_for_account_response( $item );
				}
			}

			return new \WP_REST_Response( $data );
		} catch ( \Exception $e ) {
			return new \WP_Error( 'woocommerce_paddle_rest_error_occurred', $e->getMessage(), array( 'status' => rest_authorization_required_code() ) );
		}
	}

	public function prepare_item_for_response( $item, $request ) {
		if ( empty( $item->order_id ) || 0 >= (int) $item->order_id ) {
			throw new \Exception( 'Order ID is missing.' );
		}

		$order = wc_get_order( (int) $item->order_id );
		if ( ! $order ) {
			throw new \Exception( 'Order not found.' );
		}

		return array(
			'id'                  => absint( $item->id ),
			'subscription_id'     => ! empty( $item->subscription_id ) ? sanitize_text_field( $item->subscription_id ) : '',
			'order_id'            => absint( $order->get_id() ),
			'order_url'           => $order->get_edit_order_url(),
			'date'                => wc_format_datetime( $order->get_date_created() ),
			'date_time'           => esc_attr( $order->get_date_created()->date( 'c' ) ),
			'next_payment_amount' => ! empty( $item->next_payment_amount ) ? wc_price( $item->next_payment_amount, array( 'currency' => $order->get_currency() ) ) : '',
			'next_bill_date'      => ! empty( $item->next_bill_date ) ? wc_format_datetime( wc_string_to_datetime( $item->next_bill_date ) ) : '',
			'next_bill_date_time' => ! empty( $item->next_bill_date ) ? wc_string_to_datetime( $item->next_bill_date )->date( 'c' ) : '',
			'cancel_url'          => ! empty( $item->cancel_url ) ? esc_url( $item->cancel_url ) : '',
			'update_url'          => ! empty( $item->update_url ) ? esc_url( $item->update_url ) : '',
			'status'              => ! empty( $item->status ) ? sanitize_text_field( $item->status ) : '',
			'user_name'           => ! empty( $item->display_name ) ? sanitize_text_field( $item->display_name ) : '',
			'user_email'          => ! empty( $item->user_email ) ? sanitize_email( $item->user_email ) : '',
			// 'payment_method'      => ! empty( $item->payment_method ) ? sanitize_text_field( $item->payment_method ) : '',
		);
	}

	protected function prepare_item_for_account_response( $item ) {
		if ( empty( $item->order_id ) || 0 >= (int) $item->order_id ) {
			throw new \Exception( 'Order ID is missing.' );
		}

		$order = wc_get_order( (int) $item->order_id );
		if ( ! $order ) {
			throw new \Exception( 'Order not found.' );
		}

		return array(
			'order_id'            => absint( $order->get_id() ),
			'order_url'           => $order->get_view_order_url(),
			'date'                => wc_format_datetime( $order->get_date_created() ),
			'date_time'           => esc_attr( $order->get_date_created()->date( 'c' ) ),
			'next_payment_amount' => ! empty( $item->next_payment_amount ) ? wc_price( $item->next_payment_amount, array( 'currency' => $order->get_currency() ) ) : '',
			'next_bill_date'      => ! empty( $item->next_bill_date ) ? wc_format_datetime( wc_string_to_datetime( $item->next_bill_date ) ) : '',
			'next_bill_date_time' => ! empty( $item->next_bill_date ) ? wc_string_to_datetime( $item->next_bill_date )->date( 'c' ) : '',
			'cancel_url'          => ! empty( $item->cancel_url ) ? esc_url( $item->cancel_url ) : '',
			'update_url'          => ! empty( $item->update_url ) ? esc_url( $item->update_url ) : '',
			'status'              => ! empty( $item->status ) ? sanitize_text_field( $item->status ) : '',
			// 'payment_method'      => ! empty( $item->payment_method ) ? sanitize_text_field( $item->payment_method ) : '',
		);
	}

}
