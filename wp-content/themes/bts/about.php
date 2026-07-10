<?php

/* Template Name: About */

get_header(); 

?>



<div class="site-inner-content ast-full-width">
    
    <!--Banner-->
    <div class="about-banner aligncenter">
        <img src="<?php echo get_field('banner_image'); ?>">
    </div>
    
    <!--About Content-->
    <div class="about-content">
        <div class="ast-builder-grid-row ast-grid-center-col-layout-only">
            <div class="ast-width-md-6">
                <?php echo get_field('content_block_1'); ?>
            </div>
            <div class="ast-width-md-6">
                <?php echo get_field('content_block_2'); ?>
            </div>
        </div>
    </div>
    
</div>

<?php get_footer();