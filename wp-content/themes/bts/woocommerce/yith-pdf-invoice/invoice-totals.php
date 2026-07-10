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

/** @var WC_Order $current_order */
/** @var YITH_Document $document */

$current_order   = $document->order;
$invoice_details = new YITH_Invoice_Details( $document );
$order_data = $current_order->get_data();
$order_shipping_tax = $order_data['shipping_tax'];

$language=get_post_meta($current_order->id,'wpml_language');
$language=$language[0];


$order_note=get_post_meta($current_order->id,'customer_order_note');
$order_note=$order_note[0];

$payment_method=$order_data['payment_method'];

$customer_note = $current_order->get_customer_note();
?>


<div class="document-ordernote" style="width:450px; float:left; text-align:justify;padding-top:25px;">
   <?php if(!empty($order_note)): ?>
    <p style="font-weight:bold;font-size:13px;">Anmerkung:</p>
    <p style="font-size:13px;"><?php echo $order_note;?></p> 
   <?php endif; ?> 
   
   <?php if(!empty($customer_note)): ?>
    <p style="font-weight:bold;font-size:13px;">Bestellnotiz:</p>
    <p style="font-size:13px;"><?php echo $customer_note;?></p> 
   <?php endif; ?> 
</div>       



<?php if ( ywpi_is_visible_order_totals( $document ) ) : ?>
	<div class="document-totals">
		<table class="invoice-totals" style="margin-bottom:30px;">
			<tr class="invoice-details-subtotal">
				<td class="left-content column-product">
				    <?php 
				    if($language=='de'){
				    _e( "Zwischensumme:", 'yith-woocommerce-pdf-invoice' );    
				    }
				    else
				    {
				    _e( "Subtotal", 'yith-woocommerce-pdf-invoice' );
				    }
				    ?>
					<?php
					if( ywpi_is_visible_order_discount( $document ) ):
						if( YITH_PDF_Invoice()->subtotal_incl_discount ):
							_e('Discount inc.','yith-woocommerce-pdf-invoice');
						else:
							_e('Discount exc.','yith-woocommerce-pdf-invoice');
						endif;
					endif;
					?>
				</td>
				<td class="right-content column-total">
				<?php 				      
				      $subtotal=$invoice_details->get_order_subtotal( YITH_PDF_Invoice()->subtotal_incl_discount);
				      $subtotal=round($subtotal,2);
				      foreach ( $invoice_details->get_order_shipping () as $item_id => $item ) {   
				      $shipping=$item['cost'];					      					      	      
				      }
				      $subtotal=$subtotal-$shipping;	
				      $order_shipping_tax;		     				    
				?>
				<?php //echo $invoice_details->get_order_currency( $current_order, $invoice_details->get_order_subtotal( YITH_PDF_Invoice()->subtotal_incl_discount ) ); 
				echo $invoice_details->get_order_currency( $current_order, $subtotal );?>
                </td>
			</tr>

    		<?php foreach ( $invoice_details->get_order_shipping () as $item_id => $item ) { 
    		   $shipping_total=$item['cost'];
    		   $shipping_tax = $item['total_tax'];
    		   $shipping_tax_percent = (($shipping_tax * 100) / $shipping_total);
    		   $shipping_tax_percent = round($shipping_tax_percent);
    		   $shipping_base = $shipping_total;
    		   if($shipping_tax == 0){
    		       $shipping_tax = ($shipping_total * 0.19);
    		       $shipping_base = $shipping_total - $shipping_tax; 
    		       $shipping_tax_percent = 19;
    		   }
    		   if($shipping_total > 0){ 
    			?>
    
    			<tr>
    				<td class="column-product">
    					<?php 
    					if($language=='de'){
    					echo  __ ( 'Versandkosten (ohne MwSt.)', 'yith-woocommerce-pdf-invoice' );    
    					}
    					else
    					{
    					echo  __ ( 'Shipping (excl tax)', 'yith-woocommerce-pdf-invoice' );
    					}
    					?>
    				</td>
    

    				<td class="right-content column-price">  					 
    					<?php echo ( isset( $item['cost'] ) ) ? $invoice_details->get_order_currency( $current_order, $shipping_base) : ''; ?>
    				</td>  				
                       </tr>
                       
                    <tr>
    				<?php
    
    				if ( ywpi_is_enabled_column_tax ( $document ) ) : ?>
    				        <?php if ( ywpi_is_enabled_column_percentage_tax ( $document ) && isset($item['cost']) ) : ?>
		                        <td class="column-price">
		                            <?php if( $item['cost'] != 0 && $item['cost'] != '' ): ?>
		                                <?php $tax_percentage = ( ( $invoice_details->get_item_shipping_taxes ( $item ) ) * 100 ) / $item["cost"]; ?>
		                                <?php
		                                if($language=='de'){
		                                 echo __ ( 'Versandkosten MwSt.', 'yith-woocommerce-pdf-invoice' )." (". $shipping_tax_percent . '%)'; 
		                                }
		                                else
		                                {
		                                 echo __ ( 'Shipping Tax', 'yith-woocommerce-pdf-invoice' )." (". $shipping_tax_percent . '%)';     
		                                }
		                                ?>
		                            <?php else: ?>
		                                <?php 
		                                if($language=='de'){
		                                echo __ ( 'Versandkosten MwSt.', 'yith-woocommerce-pdf-invoice' ) .'(0%)'; 
		                                }
		                                else
		                                {
		                                echo __ ( 'Shipping Tax', 'yith-woocommerce-pdf-invoice' ) .'(0%)'; 
		                                }
		                                ?>
		                            <?php endif; ?>
		                        </td>
		               <?php endif; ?> 
    					<td class="right-content column-price">
    						<?php
    						echo ( $invoice_details->get_order_currency( $current_order, wc_round_tax_total ( $shipping_tax ) ) );
    						?>
    					</td>
    				<?php endif; ?>
    		     </tr> 
                    
                    
                     
                     <!--<tr>
    				<?php if ( ywpi_is_enabled_column_total_taxed ( $document ) ) : ?>
    				        <td class="column-product">
    				     	<?php 
    				     	if($language=='de'){
    				     	echo  __ ( 'Versandkosten', 'yith-woocommerce-pdf-invoice' );   
    				     	}
    				     	else
    				     	{
    				     	echo  __ ( 'Shipping', 'yith-woocommerce-pdf-invoice' );
    				     	}
    				     	?>
    				        </td>
    					<td class="right-content column-price">
    						<?php echo $invoice_details->get_order_currency( $current_order, $item["cost"] ); ?>
    					</td>
    				<?php endif; ?>
    		    </tr>-->
    			<?php
    			}
    		}; ?>
    
    


			<?php if ( 'yes' == get_option( 'woocommerce_calc_taxes' ) ) :
				foreach ( $current_order->get_items( 'tax' ) as $item_id => $item_tax ) : ?>
				<?php
				 $tax_data = $item_tax->get_data();
			     $item_tax_label = $tax_data['label'];
			     $item_tax_total = $tax_data['tax_total'];	
			     if($item_tax_total > 0):
				?>
					<tr class="invoice-details-vat">
						<td class="left-content column-product"><?php echo $item_tax_label; ?>:</td>
						<td class="right-content column-total"><?php echo $invoice_details->get_order_currency( $current_order, $item_tax_total ); ?></td>
					</tr>
				<?php endif; endforeach; ?>
			<?php endif; ?>

			<?php do_action( 'yith_pdf_invoice_before_total', $current_order ); ?>
			
			<?php 
			$discount=$invoice_details->get_order_discount(); 
			if($discount>0)
			{
			?>
				<tr>
					<td class="left-content column-product">
					    <?php 
					     if($language=='de'){
					      _e( "Gutschein", 'yith-woocommerce-pdf-invoice' );   
					     }
					     else
					     {
					     _e( "Gft Coupon", 'yith-woocommerce-pdf-invoice' );
					     }
					    ?>
					    </td>
					<td class="right-content column-total"><?php echo '-'.$invoice_details->get_order_currency( $current_order,$invoice_details->get_order_discount() ); ?></td>
				</tr>
			<?php
			}
			?>

			<tr class="invoice-details-total">
				<td class="left-content column-product">
				    <?php 
				    if($language=='de'){
				    _e( "Endbetrag", 'yith-woocommerce-pdf-invoice' );   
				    }
				    else
				    {
				    _e( "Total Amount", 'yith-woocommerce-pdf-invoice' );
				    }
				    ?>
				</td>
				<td class="right-content column-total"><?php echo $invoice_details->get_order_currency( $current_order, $invoice_details->get_order_total() ); ?></td>
			</tr>
		</table>
	</div>
	<?php
	if($payment_method =='invoice')
	{
	?>
	   <div style="width:750px; float right; text-align:right;"><b>Bitte zahlen Sie den Rechnungsbetrag innerhalb von 14 Tagen nach Erhalt der Ware.</b></div>
	<?php
	}
	?>
<?php endif; ?>