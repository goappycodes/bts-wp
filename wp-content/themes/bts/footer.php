<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

?>
<?php astra_content_bottom(); ?>
	</div> <!-- ast-container -->
	</div><!-- #content -->
<?php 
	astra_content_after();
		
	astra_footer_before();
		
	astra_footer();
		
	astra_footer_after(); 
?>
	</div><!-- #page -->
	<script type='text/javascript' src='/wp-content/themes/bts/scripts.js?ver=<?php echo time(); ?>'></script>
<?php 
	astra_body_bottom();    
	wp_footer(); 
?>

<?php

global $post;

if ($post){
    
    $categories = array();
    $terms = wp_get_post_terms( $post->ID, 'product_cat' );
    foreach ( $terms as $term )
        $categories[] = $term->slug;
    
    if ( in_array( 'tipes-ringe', $categories ) || in_array( 'tipes-rings-600', $categories ) ) {
        ?>
        <script>
        jQuery(document).ready(function($){
            console.log('tipes');	
            $('table._custom_input').hide();
            $('input.custom_user_input').removeAttr('required');	
        });
        </script>
        <?php
    }

    
}

?>

<div class="floating-offer-right">
	<img width="180" height="110" src="/wp-content/uploads/2019/02/kauf-auf-rechnung-e1561830729896.jpg"/>    
	<img width="180" height="110" src="/wp-content/uploads/2023/05/Ab-49-Euro-innerhalb-Deutschlands-bigger.jpg"/>    


</div>

<div class="home-left">
	<a href="https://www.kaeufersiegel.de/zertifikat/?uuid=dd7f4988-17cc-11e8-bcf5-9c5c8e4fb375-2414081313" target="_blank">
		<img width="180" height="110" src="https://www.kaeufersiegel.de/zertifikat/logo.php?uuid=dd7f4988-17cc-11e8-bcf5-9c5c8e4fb375-2414081313&amp;size=150" hspace="5" vspace="5" border="0" />
	</a>
</div>


	</body>
</html>