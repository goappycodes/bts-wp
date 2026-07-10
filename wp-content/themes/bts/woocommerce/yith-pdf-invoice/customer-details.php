<?php
/**
 * The Template for invoice
 *
 * Override this template by copying it to [your theme]/woocommerce/invoice/ywpi-invoice-template.php
 *
 * @author        Yithemes
 * @package       yith-woocommerce-pdf-invoice-premium/Templates
 * @version       1.0.0
 */

if ( ! defined ( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$current_order   = $document->order;
$billing_first_name= $current_order->billing_first_name;
$billing_last_name= $current_order->billing_last_name;
$billing_address=$current_order->billing_address_1;
$billing_postcode=$current_order->billing_postcode;
$billing_city=$current_order->billing_city;

$shipping_first_name= $current_order->shipping_first_name;
$shipping_last_name= $current_order->shipping_last_name;
$shipping_address=$current_order->shipping_address_1;
$shipping_postcode=$current_order->shipping_postcode;
$shipping_city=$current_order->shipping_city;
?>
<div class="ywpi-customer-details">
	<div class="ywpi-customer-content">
		<?php 
		echo '<b>Rechnungsadresse</b><br>';
		echo $content;
		?>
		<?php do_action ( 'yith_pdf_invoice_after_customer_content', $document, $order_id ); ?>
	</div>
</div>
<?php
if($billing_first_name !=$shipping_first_name || $billing_last_name!=$shipping_last_name || $billing_address!=$shipping_address || $billing_postcode!=$shipping_postcode || $billing_city!=$shipping_city){
?>
<div class="ywpi-customer-shipping-details">
	<div class="ywpi-customer-content">
		<?php   
		echo '<b>Lieferadresse</b><br>';
		echo $current_order->shipping_first_name.' '.$current_order->shipping_last_name.'<br>';
		echo $current_order->shipping_address_1.'<br>';
		echo $current_order->shipping_postcode.' '.$current_order->shipping_city.'<br>';
		echo WC()->countries->countries[$current_order->shipping_country];
		?>
	</div>
</div>
<?php
}
?>