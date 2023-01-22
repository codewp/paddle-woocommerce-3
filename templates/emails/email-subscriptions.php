<?php
/**
 * Order subscripitions shown in emails.
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_before_order_subscriptions', $order, $sent_to_admin, $plain_text, $email ); ?>

<h2 class="woocommerce-order-subscriptions__title"><?php esc_html_e( 'Subscriptions', 'paddle' ); ?></h2>

<?php if ( ! $sent_to_admin ) : ?>
	<p><?php echo wp_kses_post( sprintf( __( 'You are subscribed to the below products, and you can cancel your subscription at any time from %s.', 'paddle' ), '<a href="' . esc_url( wc_get_account_endpoint_url( 'paddle-subscriptions' ) ) . '" target="_blank"><strong>' . __( 'your account', 'paddle' ) . '</strong></a>' ) ); ?></p>
<?php endif; ?>

<div style="margin-bottom: 40px;">
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
		<thead>
			<tr>
				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $subscriptions as $item ) : ?>
				<tr>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;">
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
					<th class="td" scope="row" colspan="2" style="border-top-width: 4px; text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo esc_html__( 'Next Payment Amount', 'paddle' ); ?></th>
					<td class="td" style="border-top-width: 4px; text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( wc_price( $subscription->next_payment_amount, array( 'currency' => $order->get_currency() ) ) ); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>
</div>

<?php do_action( 'woocommerce_email_after_order_subscriptions', $order, $sent_to_admin, $plain_text, $email ); ?>
