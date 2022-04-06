<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Paddle_DB_Subscriptions extends Paddle_DB {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'paddle_subscriptions';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	/**
	 * Get columns and formats
	 *
	 * @since   1.0.0
	 *
	 * @return  array
	 */
	public function get_columns() {
		return array(
			'id'                   => '%d',
            'order_id'             => '%d',
            'subscription_id'      => '%s',
            'subscription_plan_id' => '%s',
            'paddle_user_id'       => '%s',
            'status'               => '%s',
            'cancel_url'           => '%s',
            'update_url'           => '%s',
            'next_bill_date'       => '%s',
            'currency'             => '%s',
            'unit_price'           => '%s',
		);
	}

	/**
	 * Get default column values.
	 *
	 * @since  1.0.0
	 *
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
            'next_bill_date' => '0000-00-00 00:00:00',
        );
	}

	/**
	 * Add a subscription.
	 * Update subscription if exists otherwise insert new one.
	 *
	 * @since  1.0.0
	 *
	 * @param  array $args
	 *
	 * @return false|int
	 */
	public function add( array $args = array() ) {
		$args = wp_parse_args( $args, $this->get_column_defaults() );

		if ( isset( $args['id'] ) ) {
			$item = $this->get_item( $args['id'] );
			if ( $item ) {
				$this->update( $item->id, $args );
				return $item->id;
			}
		}

		$id = $this->insert( $args, 'subscription' );

		return $id ? $id : false;
	}

	/**
	 * Retrieves a single subcription from the database;
	 *
	 * @since  1.0.0
	 *
	 * @param  int     $id
	 * @param  string  $output
	 *
	 * @return Object|Array|false  False on failure
	 */
	public function get_item( $id, $output = OBJECT ) {
		global $wpdb;

		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE id = %d LIMIT 1", $id ), $output );

		return $item ? $item : false;
	}

    public function get_item_by( $field, $value, $output = OBJECT ) {
        if ( empty( $field ) || empty( $value ) ) {
            return false;
        }

        global $wpdb;

        $item = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $field = %s LIMIT 1", $value ), $output );

        return $item ? $item : false;
    }

	/**
	 * Get a collectoin of subscriptions.
	 *
	 * @since  1.0.0
	 *
	 * @param  array  $args
	 *
	 * @return array
	 */
	public function get_items( array $args = array() ) {
		global $wpdb;

		$args = wp_parse_args( $args, array(
			'number'   => 20,
			'offset'   => 0,
			'orderby'  => 'id',
			'order'    => 'DESC',
			'output'   => OBJECT,
			'paginate' => false,
		) );

		if ( $args['number'] < 1 ) {
			$args['number']   = 999999999999;
			$args['paginate'] = false;
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? 'id' : $args['orderby'];
		$args['orderby'] = esc_sql( $args['orderby'] );
		$args['order']   = esc_sql( $args['order'] );

		$select_args = array();
		$where       = ' WHERE 1=1';

		// Specific conditions.
		if ( ! empty( $args['id'] ) ) {
			if ( is_array( $args['id'] ) ) {
				$ids = implode( ',', array_map( 'absint', $args['id'] ) );
			} else {
				$ids = absint( $args['id'] );
			}
			$where .= " AND `id` IN( {$ids} )";
		}

        // Search by subscription id.
        if ( ! empty( $args['subscription_id'] ) ) {
            $where .= ' AND LOWER(`subscription_id`) = %s';
            $select_args[] = strtolower( sanitize_text_field( $args['subscription_id'] ) );
        }

		// Search.
		if ( ! empty( $args['search'] ) ) {
			$where         .= ' AND (`order_id` LIKE %s OR LOWER(`subscription_id`) LIKE %s OR LOWER(`subscription_plan_id`) LIKE %s OR LOWER(`paddle_user_id`) LIKE %s)';
			$select_args[] = '%' . $wpdb->esc_like( strtolower( sanitize_text_field( $args['search'] ) ) ) . '%';
		}

		// Status.
		if ( isset( $args['status'] ) ) {
			$where .= ' AND LOWER(`status`) = %s';
            $select_args[] = strtolower( sanitize_text_field( $args['status'] ) );
		}

		// Select specific user subscriptions.
		if ( ! empty( $args['user_id' ] ) && 0 < (int) $args['user_id'] ) {
			$customer_orders = wc_get_orders(
				array(
					'customer' => (int) $args['user_id'],
					'return'   => 'ids',
				)
			);
			if ( empty( $customer_orders ) ) {
				if ( empty( $args['paginate'] ) ) {
					return array();
				}
				return array(
					'items' => array(),
					'total' => 0,
					'pages' => 0,
				);
			}

			$where .= ' AND `order_id` IN (' . implode( ',', array_map( 'absint', $customer_orders ) ) . ')';
		}

		$select_args[] = absint( $args['offset'] );
		$select_args[] = absint( $args['number'] );

		$items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", $select_args ), $args['output'] );
		if ( empty( $items ) ) {
			if ( empty( $args['paginate'] ) ) {
				return array();
			}

			return array(
				'items' => array(),
				'total' => 0,
				'pages' => 0,
			);
		}

		if ( empty( $args['paginate'] ) ) {
			return $items;
		}

		$total = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT( $this->primary_key ) FROM $this->table_name $where", $select_args ) );

		return array(
			'items' => $items,
			'total' => absint( $total ),
			'pages' => ceil( absint( $total ) / absint( $args['number'] ) ),
		);
	}

    /**
	 * Deleting a condition by it's id.
	 *
	 * @since  1.0.0
	 *
	 * @param  int $id
	 *
	 * @return boolean
	 */
	public function delete( $id ) {
		$id = absint( $id );
		if ( ! $id ) {
			return false;
		}

		$item = $this->get_item( $id );
		if ( 0 < $item->id ) {
			global $wpdb;
			return $wpdb->delete( $this->table_name, array( 'id' => $item->id ), array( '%d' ) );
		}

		return false;
	}

	/**
	 * Counting number of subcriptions.
	 *
	 * @since  1.0.0
	 *
	 * @return int
	 */
	public function count() {
		global $wpdb;

		$count = $wpdb->get_var( "SELECT COUNT( $this->primary_key ) FROM $this->table_name" );

		return absint( $count );
	}

}
