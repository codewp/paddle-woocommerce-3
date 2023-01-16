<?php

defined( 'ABSPATH' ) || exit;

class Paddle_WC_Admin_Product_Hooks {

	public static function init() {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'general_product_data' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_data' ) );
	}

	public static function general_product_data() {
		global $product_object;

		echo '<div class="options_group paddle_product_data">';
		woocommerce_wp_checkbox(
			array(
				'id'          => '_paddle_one_off_purchase',
				'value'       => $product_object->get_meta( '_paddle_one_off_purchase', true, 'edit' ) ? 'yes' : 'no',
				'label'       => __( 'One-Off purchase?', 'paddle' ),
				'description' => __( 'Enable Paddle one-off purchase for the product', 'paddle' ),
			)
		);
		echo '</div>';
	}

	public static function save_product_data( $product ) {
		$product->update_meta_data( '_paddle_one_off_purchase', ! empty( $_POST['_paddle_one_off_purchase'] ), true );
	}

}
