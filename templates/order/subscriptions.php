<?php
/**
 * Order subscripitions shown in checkout thankyou page.
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<h2 class="woocommerce-order-subscriptions__title"><?php esc_html_e( 'Subscriptions', 'paddle' ); ?></h2>

<p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-subscribed-message"><?php echo wp_kses_post( sprintf( __( 'You are subscribed to the below products, and you can cancel your subscription at any time from %s.', 'paddle' ), '<a href="' . esc_url( wc_get_account_endpoint_url( 'paddle-subscriptions' ) ) . '" target="_blank"><strong>' . __( 'your account', 'paddle' ) . '</strong></a>' ) ); ?></p>

<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
	<thead>
		<tr>
			<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $subscriptions as $item ) : ?>
			<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">
				<td class="woocommerce-table__product-name product-name">
					<?php
					$product           = $item->get_product();
					$is_visible        = $product && $product->is_visible();
					$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

					echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ) );
					?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<?php
		if ( ! empty( $subscription->next_payment_amount ) ) : ?>
			<tr>
				<th scope="row"><?php echo esc_html__( 'Next Payment Amount', 'paddle' ); ?></th>
				<td><?php echo wp_kses_post( wc_price( $subscription->next_payment_amount, array( 'currency' => $order->get_currency() ) ) ); ?></td>
			</tr>
		<?php endif; ?>
	</tfoot>
</table>
