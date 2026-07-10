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

if ( $document_title ) : ?>
	<div class="ywpi-document-title" style="text-align:right">
		<?php // echo $document_title; ?>
		<img src="https://brieftaubenshop.de/wp-content/uploads/2018/06/New_logo_nr.jpg" style="width:70%;"/>
	</div>
	<div class="ywpi-document-subtitle" style="display:block;font-size:10px;text-align:left">
		<?php // echo $document_title; ?>
		<span style="padding-bottom:15px;margin-left:5px;border-bottom:1px solid; font-size:11px;">Brieftaubenshop – JJ's Food & Feed Trade GmbH – Siemensstraße 18 – 49770 Herzlake</span>
	</div>

<?php endif;
