<?php
/**
 * Override this template by copying it to [your theme folder]/woocommerce/yith-pdf-invoice
 *
 * @author        Yithemes
 * @package       yith-woocommerce-pdf-invoice-premium/Templates
 * @version       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

$current_order   = $document->order;
$invoice_details = new YITH_Invoice_Details( $document );
$user_id=$current_order->get_user_id();
if($user_id > 0){
$user_id='KD'.$user_id;
}
$order_data = $current_order->get_data();
$payment_method=$order_data['payment_method'];

$language=get_post_meta($current_order->id,'wpml_language');
$language=$language[0];
//$language='de';
?>
<div class="invoice-data-content">
	<table>
		<tr class="invoice-order-number">
			<td class="left-content">
				<?php 
				if($language=='de'){
				_e( "Rechnungsnr. und Bestellnr.", 'yith-woocommerce-pdf-invoice' );
				}
				else{
				_e( "Order No.", 'yith-woocommerce-pdf-invoice' );    
				}
				?>:
			</td>
			<td class="right-content">
				<?php echo $document->order->get_order_number(); ?>
				<?php do_action( 'yith_ywpi_template_order_number', $document ); ?>
			</td>
		</tr>

		<tr class="ywpi-invoice-date">
			<td class="left-content">
				<?php 
				if($language=='de'){
				 _e( "Rechnungsdatum:", 'yith-woocommerce-pdf-invoice' );   
				}
				else{
				_e( "Invoice date", 'yith-woocommerce-pdf-invoice' ); 
				}
				?>
			</td>
			<td class="right-content">
				<?php echo $document->get_formatted_order_date(); ?>
			</td>
		</tr>
		<?php
		if($user_id > 0){
		?>
		<tr class="ywpi-order-number">
			<td class="left-content">
				<?php
				if($language=='de'){
				_e( "Kundennummer:", 'yith-woocommerce-pdf-invoice' );    
				}
				else
				{
				_e( "Customer ID", 'yith-woocommerce-pdf-invoice' );
				}
				?>
			</td>
			<td class="right-content">
				<?php echo $user_id; ?>
				<?php do_action( 'yith_ywpi_template_order_number', $document ); ?>
			</td>
		</tr>
		<?php
		}
		?>
     	<tr class="ywpi-order-number">
			<td class="left-content">
				<?php 
				if($language=='de'){
				_e( "Zahlungsmethode:", 'yith-woocommerce-pdf-invoice' );    
				}
				else
				{
				_e( "Payment Method", 'yith-woocommerce-pdf-invoice' );
				}
				?>
			</td>
			<td class="right-content">
				<?php 
				if($payment_method=='bacs'){
				    if($language=='de'){
				    _e( "Vorkasse", 'yith-woocommerce-pdf-invoice' );
				    }
				    else{
				    _e( "Bank Transfer", 'yith-woocommerce-pdf-invoice' );    
				    }
				}
				else if($payment_method=='direct-debit'){
				    if($language=='de'){
				     _e( "Lastschrift", 'yith-woocommerce-pdf-invoice' );    
				    }
				    else
				    {
				    _e( "Direct Debit", 'yith-woocommerce-pdf-invoice' );
				    }
				}
				else if($payment_method=='paypal'){
				_e( "Paypal", 'yith-woocommerce-pdf-invoice' );
				}
				else if($payment_method=='invoice'){
				    if($language=='de'){
				      _e( "Rechnung", 'yith-woocommerce-pdf-invoice' );  
				    }
				    else
				    {
				     _e( "Bills", 'yith-woocommerce-pdf-invoice' );
				    }
				}
				else if($payment_method=='paypal_plus'){
				_e( "Paypal", 'yith-woocommerce-pdf-invoice' );
				}
				else if($payment_method=='kco'){
				_e( "Klarna", 'yith-woocommerce-pdf-invoice' );
				}
				else if($payment_method=='other_payment'){
				_e( "Amazon", 'yith-woocommerce-pdf-invoice' );
				}
				?>
				<?php do_action( 'yith_ywpi_template_order_number', $document ); ?>
			</td>
		</tr>
	       
		<!--<tr class="invoice-amount">
			<td class="left-content">
				<?php _e( "Amount", 'yith-woocommerce-pdf-invoice' ); ?>
			</td>
			<td class="right-content">
                <?php echo $invoice_details->get_order_currency( $current_order, $document->order->get_total() ); ?>
			</td>
		</tr>-->
	</table>
</div>