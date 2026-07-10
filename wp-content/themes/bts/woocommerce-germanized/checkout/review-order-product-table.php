<?php
/**
 * Review order product table
 *
 * @author 		Vendidero
 * @package 	WooCommerceGermanized/Templates
 * @version     1.0.0
 */

if ( ! defined( 'ABSPATH' ) )
	exit;

do_action( 'woocommerce_gzd_review_order_before_cart_contents' );

foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
	$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

	if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
		?>
		<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
					    <td class="product-image"><?php $thumbnail = apply_filters( 'woocommerce_in_cart_product_thumbnail', $_product->get_image(), $values, $cart_item_key ); echo $thumbnail; ?></td>
						<td class="product-name1">
							<?php echo apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;'; ?>
							<?php //echo apply_filters( 'woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf( '&times; %s', $cart_item['quantity'] ) . '</strong>', $cart_item, $cart_item_key ); ?>
							<?php //echo WC()->cart->get_item_data( $cart_item ); ?>
						</td>
						<td class="product-name2"><?php echo wc_price(get_post_meta($cart_item['product_id'], '_price', true)); ?></td>
						<td class="product-name3"><?php echo $cart_item['quantity']; ?></td>
						<td class="product-total">
							<?php echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); ?>
						</td>
					</tr>
		<?php
	}
}