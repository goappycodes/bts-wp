<?php

require('wp-load.php');

global $wpdb;
$prefix = $wpdb->prefix;
$posts = $prefix.'posts';
$postmeta = $prefix.'postmeta';

$query = "select ID from $posts where post_type = 'product' order by ID asc";

$products = $wpdb->get_col($query);

foreach ($products as $product_id){
    $product_id_en = apply_filters( 'wpml_object_id', $product_id, 'product', FALSE, 'en' );
    
    if ($product_id_en == $product_id) continue;
    if (get_post_meta($product_id_en, 'auto_en_title', true)) continue;
    
    echo $product_id.'--'.$product_id_en;
    $de_post = get_post($product_id);
    $en_post = get_post($product_id_en);
    // echo '<br/>';
    echo $de_post->post_title.'--'.$en_post->post_title;
    // echo '--<a href="https://www.brieftaubenshop.de/en/product/'.$de_post->post_name.'">VIEW</a>';
    
    $update_en = array(
        'ID'            => $product_id_en,
        'post_title'    => $de_post->post_title,
        'post_name'     => $de_post->post_name,
        // 'post_content'  => str_replace($en_post->post_title, $de_post->post_title, $en_post->post_content),
        // 'post_content'  => $en_post->post_content,
        // 'post_excerpt'  => str_replace($en_post->post_title, $de_post->post_title, $en_post->post_excerpt)
    );
    
    // echo '<pre>';
    // print_r($update_en);
    // echo '</pre>';

    wp_update_post( $update_en );
    
    if ( !get_post_meta($product_id_en, 'auto_en_title', true) ) {
        update_post_meta($product_id_en, 'auto_en_title', $en_post->post_title);
    }
    
    echo '<br/><br/>';
}
