<?php

/* Template Name: Homepage */

get_header(); ?>



<div class="site-inner-content ast-full-width">
    
    <?php  
	$banner_image 		= get_field('banner_image');
	$banner_image_link 	= get_field('banner_image_link'); 
	?>
    <!--Banner-->
    <div class="home-banner ast-full-width">
        <a href="<?php echo esc_url( $banner_image_link ); ?>"><img width="1040" height="379" src="<?php echo str_replace('https://', 'https://i0.wp.com/', $banner_image); ?>"></a>
    </div>
    
    <?php  $banner_product_images = get_field('banner_product_images'); ?>
    
    <!--Banner Products-->
    <div class="banner-products ast-full-width">
        <div class="ast-builder-grid-row ast-grid-center-col-layout-only">
            <div class="ast-width-md-4">
                <a
                    class="banner-product-image"
                    href="<?php echo isset($banner_product_images['product_link_1']) ? $banner_product_images['product_link_1'] : ""; ?>">
                    <img width="300" height="160" src="<?php echo str_replace('https://', 'https://i0.wp.com/', $banner_product_images['product_image_1']); ?>" />
                </a>
            </div>
            <div class="ast-width-md-4">
                <a
                    class="banner-product-image"
                    href="<?php echo isset($banner_product_images['product_link_2']) ? $banner_product_images['product_link_2'] : ""; ?>">
                    <img width="300" height="160" src="<?php echo str_replace('https://', 'https://i0.wp.com/', $banner_product_images['product_image_2']); ?>" />
                </a>
            </div>
            <div class="ast-width-md-4">
                <a
                    class="banner-product-image"
                    href="<?php echo isset($banner_product_images['product_link_3']) ? $banner_product_images['product_link_3'] : ""; ?>">
                    <img width="300" height="160" src="<?php echo str_replace('https://', 'https://i0.wp.com/', $banner_product_images['product_image_3']); ?>" />
                </a>
            </div>
        </div>
        <?php $shop_button_text = get_field('shop_button_text'); ?>
        <?php $shop_button_link = get_field('shop_button_link'); ?>
        <a href="<?php echo $shop_button_link; ?>" class="ast-button ast-full-width aligncenter"><?php echo $shop_button_text; ?></a>
    </div>
    
    <?php  $welcome_content = get_field('welcome_content_copy'); ?>
    

    
    <?php
    
    if (isset($_GET['asdsd123131'])) {
        print_r($welcome_content);
    } 
    ?>    
    <!--Welcome-->
    <div class="welcome-content aligncenter">
        <h3><?php echo $welcome_content['main_heading']; ?></h3>
        <h4><?php echo $service_detail_information['service_detail_information']; ?></h4>
        <div class="bts-whatsapp-link">
            <span>Whatsapp</span>
            <img width="50" height="50" src="/wp-content/uploads/2021/02/whatsapp.svg">
            <a href="tel:<?php echo $welcome_content['whatsapp_number']; ?>"><?php echo $welcome_content['whatsapp_number']; ?></a>
        </div>
        <?php echo $welcome_content['main_content']; ?>
        <a href="javascript:void(0);" class="continue-reading"><?php echo __('Read More'); ?> <img width="22" height="22" src="/wp-content/uploads/2021/12/arrow-down.svg"></a>
        <div class="more-content">
            <?php echo $welcome_content['read_more_content']; ?>
        </div>
    </div>
    
    <!--Buy Shop Banner-->
    <div class="buy-shop-banner">
        <img src="<?php echo get_field('why_should_buy_banner_image'); ?>" class="ast-full-width">
    </div>
    
    <!--Our best-selling products-->
    <div class="our-best-selling-products">
        <h3><?php echo get_field('best_selling_products_title'); ?></h3>
        <?php echo do_shortcode('[best_selling_products limit="5" columns="5" orderby="popularity"]'); ?>
    </div>
    
</div>

<?php get_footer();