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
$order_id = $current_order->get_id();

?>
	<htmlpagefooter name="footer">
		<hr />
		<div id="footer">
			<table style="vertical-alig n:top;font-size:11px">
				<tr>
					<td></td>
					<td>
				</tr>
				<tr>
					<td style="text-align:left;">
                    Brieftaubenshop<br/>
                    JJ's Food & Feed Trade GmbH<br/>
                    Siemensstraße 18<br/>
                    D- 49770 Herzlake<br/>
                    Geschäftsführung: Jenn Johann
					</td>
					<td style="text-align:left;">
                    Web: www.brieftaubenshop.de<br/>
					info@brieftaubenshop.de<br/>
                    USt-ID: DE362101388<br/>
                    Handelsregister: Amtsgericht Osnabrück<br/>
                    HRB 219206
					</td>
					<td style="text-align:left;">
                    Bankverbindung<br/>
                    IBAN: DE09 2666 0060 1128 7632 00<br/>
                    BIC: GENODEF1LIG<br/>
                    Emsländische Volksbank
                    </td>
				</tr>
			</table>
		</div>
	</htmlpagefooter>
	<sethtmlpagefooter name="footer" value="on" />