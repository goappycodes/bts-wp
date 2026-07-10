<?php
/**
 * Order details table shown in emails.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$language=get_post_meta($order->id,'wpml_language');
$language=$language[0];
?>

<?php do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<table cellpadding="0" cellspacing="0" border="0" width="100%">
	<tr>
		<td class="top_content_container">
			
			<?php echo ec_special_title( __( "Order Details", 'email-control'), array("border_position" => "center", "text_position" => "center", "space_after" => "3", "space_before" => "3" ) ); ?>
			
			<table cellspacing="0" cellpadding="0" border="0" width="100%">
				<tr>
					<td class="order-table-heading" style="text-align:left;">
						<span class="highlight">						        
							<?php 
							if($language=='de'){
							_e( 'Bestellnummer:', 'email-control' );
							}
							else
							{
							_e( 'Order Number:', 'email-control' );
							}
							 ?>
						</span>
						<?php if ( ! $sent_to_admin ) : ?>
							<?php echo $order->get_order_number(); ?>
						<?php else : ?>
							<?php echo $order->get_order_number(); ?>
						<?php endif; ?>
					</td>
					<td class="order-table-heading" style="text-align:right;">
						<span class="highlight">
							<?php 
							if($language=='de'){
							_e( 'Bestelldatum:', 'email-control' );
							}
							else
							{
							_e( 'Order Date:', 'email-control' ) ;
							}
							?>
						</span> 
						<?php printf( '<time datetime="%s">%s</time>', $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ); ?>
					</td>
				</tr>
			</table>

			<div class="order_items_table">
			
				<table cellspacing="0" cellpadding="0" border="0" >
				    <thead>
					    <tr>
							    <th scope="col">
							    <?php
							     if($language=='de'){
							     _e( 'ARTIKEL', 'email-control' ); 
							     }
							     else
							     {
							     _e( 'Product', 'email-control' ); 
							     }
							     ?>
							    </th>
							    <th scope="col">
							    <?php 
							    if($language=='de'){
							    _e( 'MENGE', 'email-control' ); 
							    }
							    else
							    {
							    _e( 'Quantity', 'email-control' ); 
							    }
							    ?>
							    </th>
							    <th scope="col" style="text-align:right;">
							    <?php 
							    if($language=='de'){
							    _e( 'PREIS', 'email-control' );
							    }
							    else
							    {
							    _e( 'Price', 'email-control' );
							    }
							     ?>
							    </th>
							    <th scope="col" style="text-align:right;">
							    <?php 
							    if($language=='de'){
							    _e( 'NETTO', 'email-control' );
							    }
							    else
							    {
							    _e( 'Base Price', 'email-control' );
							    }
							     ?>
							    </th>
							    <th scope="col" style="text-align:right;">
							    <?php 
							    if($language=='de'){
							    _e( 'MWST (%)', 'email-control' );
							    }
							    else
							    {
							    _e( 'Tax(%)', 'email-control' ); 
							    }
							     ?>
							    </th>
							    <th scope="col" style="text-align:right;">
							    <?php
							    if($language=='de'){
							     _e( 'MWST', 'email-control' ); 
							     }
							     else
							     {
							     _e( 'Tax', 'email-control' ); 
							     }
							     ?>
							    </th>
							    <th scope="col" style="text-align:right;">
							    <?php 
							    if($language=='de'){
							    _e( 'SUMME', 'email-control' );
							    }
							    else
							    {
							     _e( 'Line Total', 'email-control' );
							    }
							     ?>
							    </th>
					    </tr>
				    </thead>
				    <tbody>
					    <?php echo wc_get_email_order_items( $order, array(
						    'show_sku'      => $sent_to_admin,
						    'show_image'    => FALSE,
						    'image_size'    => array( 70, 70 ),
						    'plain_text'    => $plain_text,
						    'sent_to_admin' => $sent_to_admin
					    ) ); ?>
				    </tbody>
				    <tfoot>
				    
					    <?php
					    if ( $totals = $order->get_order_item_totals() ) {
						    $i = 0;
						    
						 ?>   
						           <tr class="order_items_table_total_row_<?php echo esc_attr( sanitize_title( $total['label'] ) ) ?>">
							         <th scope="row" colspan="6">
									    <?php 
									    if($language=='de'){
									    _e( 'ZWISCHENSUMME', 'email-control' );
									    }
									    else
									    {
									    _e( 'Subtotal', 'email-control' );
									    } 
									    ?> 
								 </th>
							         <td style="text-align:right;">
									    <?php 
			                            $subtotal=$order->get_subtotal();
			                            echo wc_price($subtotal);
									    ?>
								 </td>
							    </tr>
							    <?php
							    $discount=get_post_meta($order->id,'_discount_percent');
			                    $discount=$discount[0];
			                    if($discount>0){
			                    $discount_amount=get_post_meta($order->id,'_cart_discount');
			                    $discount_amount=$discount_amount[0];
                                ?>
							    <tr class="order_items_table_total_row_<?php echo esc_attr( sanitize_title( $total['label'] ) ) ?>">
							         <th scope="row" colspan="6">
									    <?php 
									    if($language=='de'){
									    _e( 'RABATT', 'email-control' );
									    }
									    else
									    {
									    _e( 'Discount', 'email-control' );
									    } 
									    ?> 
								 </th>
							         <td style="text-align:right;">
									    <?php 
									    echo '-'.wc_price($discount_amount);
									    ?>
								 </td>
							    </tr>
							    <?php
			                    }
			                    ?>
						    <?php
						    $order_data = $order->get_data();
						    $shipping_total=$order_data['shipping_total'];
    		                $shipping_tax = $order_data['shipping_tax'];
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
                                                    
                                <tr class="order_items_table_total_row_<?php echo esc_attr( sanitize_title( $total['label'] ) ) ?>">
							         <th scope="row" colspan="6">
									    <?php 
									    if($language=='de'){
									    _e( 'VERSANDKOSTEN (OHNE MWST.)', 'email-control' );
									    }
									    else
									    {
									    _e( 'Shipping(excl tax)', 'email-control' );
									    } 
									    ?> 
								 </th>
							         <td style="text-align:right;">
									    <?php echo wc_price($shipping_base ); ?>
								 </td>
							    </tr>
							    
							     <tr class="order_items_table_total_row_<?php echo esc_attr( sanitize_title( $total['label'] ) ) ?>">
							         <th scope="row" colspan="6">
									    <?php 
									    if($language=='de'){
									    _e( 'VERSANDKOSTEN MWST.', 'email-control' ); echo '('.$shipping_tax_percent.'%)';
									    }
									    else
									    {
									    _e( 'Shipping Tax', 'email-control' ); echo '('.$shipping_tax_percent.'%)';
									    }
									     ?>
								 </th>
							         <td style="text-align:right;">
									    <?php echo wc_price($shipping_tax); ?>
								 </td>
							     </tr>
							     <!--<tr class="order_items_table_total_row_<?php echo esc_attr( sanitize_title( $total['label'] ) ) ?>">
							         <th scope="row" colspan="6">
									    <?php 
									    if($language=='de'){
									    _e( 'VERSANDKOSTEN', 'email-control' ); 
									    }
									    else
									    {
									    _e( 'Shipping', 'email-control' ); 
									    }
									    ?>
								 </th>
							         <td style="text-align:right;">
									    <?php echo wc_price($order_shipping_total); ?>
								 </td>
							     </tr>-->
                                                    
                            <?php
                            }
						    foreach( $order->get_items( 'tax' ) as $item_id => $item_tax ){
    						    $tax_data = $item_tax->get_data();
			                    $item_tax_label = $tax_data['label'];
			                    $item_tax_total = $tax_data['tax_total'];	
			                    if ($item_tax_total > 0){
        						    ?>
						            <tr class="order_items_table_total_row_<?php echo esc_attr( sanitize_title( $total['label'] ) ) ?>">
    							         <th scope="row" colspan="6">
        									    <?php echo $item_tax_label; ?>
        								 </th>
    							         <td style="text-align:right;">
        									    <?php echo wc_price(round($item_tax_total,2)); ?>
        								 </td>
    							    </tr>
        						    <?php
			                    }
						    }
						    ?>
						            <tr class="order_items_table_total_row_<?php echo esc_attr( sanitize_title( $total['label'] ) ) ?>">
							         <th scope="row" colspan="6">
									    <?php 
									    if($language=='de'){
									    _e( 'ENDBETRAG', 'email-control' );
									    }
									    else
									    {
									     _e( 'Total Amount', 'email-control' );
									    }
									     ?>
								 </th>
							         <td style="text-align:right;">
									    <?php echo $totals['order_total']['value']; ?>
								 </td>
							    </tr>
						    <?php
						    /*foreach ( $totals as $total ) {
							    $i++;
							    ?>
								    <tr class="order_items_table_total_row_<?php echo esc_attr( sanitize_title( $total['label'] ) ) ?>">
									    <th scope="row" colspan="6">
									    <?php echo $total['label']; ?>
								    </th>
									    <td style="text-align:right;">
									    <?php echo $total['value']; ?>
								    </td>
							    </tr>
							    <?php
						    }*/
					    }
					    if ( $order->get_customer_note() ) {
						    ?>
						    <tr class="order_items_table_total_row_note">
							    <td colspan="3">
								    <strong><?php _e( 'Note', 'woocommerce' ); ?></strong>
								    <br>
								    <?php echo wptexturize( $order->get_customer_note() ); ?>
							    </td>
						    </tr>
						    <?php
					    }
					    ?>
				    </tfoot>
			    </table>
			</div>
            
		</td>
	</tr>
</table>


<div class="order_other_table_holder">
	<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
</div>
