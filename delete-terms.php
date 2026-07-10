<?php

require('wp-load.php');

$terms = get_terms( [
        'taxonomy'               => 'product_tag',
        'hide_empty'             => false,
    ] );

    foreach ( $terms as $t ) {
        echo '<br/>'.$t->name.'='.$t->count;
        if ( 0 === $t->count ) {
            wp_delete_term( $t->term_id, 'product_tag' );
        }
    }
