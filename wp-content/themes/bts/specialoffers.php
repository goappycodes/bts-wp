<?php

/*
* Automatically adding the product to the cart when cart total amount reach to $500.
*/


function woocommerce_custom_price_to_cart_item($cart_object)
{
    if (!WC()->session->__isset("reload_checkout")) {
        foreach ($cart_object->cart_contents as $key => $value) {
            if (isset($value["custom_price"])) {
                $value['data']->set_price($value["custom_price"]);
            }
        }
    }
}

//add_action( 'woocommerce_before_calculate_totals', 'woocommerce_custom_price_to_cart_item', 99 );


//Free Gifts section
function aapc_add_product_to_cart()
{


    global $woocommerce;

    $cart_total     = 79;
    $cart_total1  = 99;
    $cart_total0  = 1;

    $free_product_id = 45765;
    $free_product_id1 = 20102;
    $free_product_id2 = 9901;
    $free_product_id3 = 49210;
    $free_product_id4 = 34995;
    $free_product_id5 = 34991;
    $free_product_id6 = 49213;



    $custom_price = 0;
    $cart_item_data = array('custom_price' => $custom_price);
    $cart_products = array();


    if ($woocommerce->cart->subtotal >= $cart_total0) {
        if (!is_admin()) {
            //check if product already in cart
            if (sizeof(WC()->cart->get_cart()) > 0) {
                foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
                    $_product = $values['data'];
                    array_push($cart_products, $_product->get_id());
                }

                if (!in_array($free_product_id, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id, 1, '', '', $cart_item_data);
                }
            } else {
                // if no products in cart, add it
                WC()->cart->add_to_cart($free_product_id);
            }
        }
    } else {
        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item["custom_price"])  && $cart_item['product_id'] == $free_product_id) {
                $woocommerce->cart->remove_cart_item($cart_item_key);
            }
        }
    }


    if ($woocommerce->cart->subtotal >= $cart_total) {
        if (!is_admin()) {
            //check if product already in cart
            if (sizeof(WC()->cart->get_cart()) > 0) {
                foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
                    $_product = $values['data'];
                    array_push($cart_products, $_product->get_id());
                }

                if (!in_array($free_product_id, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id1, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id1, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id2, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id2, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id3, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id3, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id4, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id4, 1, '', '', $cart_item_data);
                }
            } else {
                // if no products in cart, add it
                WC()->cart->add_to_cart($free_product_id);
                WC()->cart->add_to_cart($free_product_id1);
                WC()->cart->add_to_cart($free_product_id2);
                WC()->cart->add_to_cart($free_product_id3);
                WC()->cart->add_to_cart($free_product_id4);
            }
        }
    } else {
        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {

            if (isset($cart_item["custom_price"])  && $cart_item['product_id'] == $free_product_id1) {
                $woocommerce->cart->remove_cart_item($cart_item_key);
            }
            if (isset($cart_item["custom_price"])  && $cart_item['product_id'] == $free_product_id2) {
                $woocommerce->cart->remove_cart_item($cart_item_key);
            }
            if (isset($cart_item["custom_price"])  && $cart_item['product_id'] == $free_product_id3) {
                $woocommerce->cart->remove_cart_item($cart_item_key);
            }
            if (isset($cart_item["custom_price"])  && $cart_item['product_id'] == $free_product_id4) {
                $woocommerce->cart->remove_cart_item($cart_item_key);
            }
        }
    }

    if ($woocommerce->cart->subtotal >= $cart_total1) {
        if (!is_admin()) {
            $found         = false;
            //check if product already in cart
            if (sizeof(WC()->cart->get_cart()) > 0) {
                foreach (WC()->cart->get_cart() as $cart_item_key => $values) {
                    $_product = $values['data'];
                    array_push($cart_products, $_product->get_id());
                }
                if (!in_array($free_product_id, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id1, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id1, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id2, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id2, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id3, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id3, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id4, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id4, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id5, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id5, 1, '', '', $cart_item_data);
                }

                if (!in_array($free_product_id6, $cart_products)) {
                    WC()->cart->add_to_cart($free_product_id6, 1, '', '', $cart_item_data);
                }
            } else {
                // if no products in cart, add it
                WC()->cart->add_to_cart($free_product_id);
                WC()->cart->add_to_cart($free_product_id1);
                WC()->cart->add_to_cart($free_product_id2);
                WC()->cart->add_to_cart($free_product_id3);
                WC()->cart->add_to_cart($free_product_id4);
                WC()->cart->add_to_cart($free_product_id5);
                WC()->cart->add_to_cart($free_product_id6);
            }
        }
    } else {
        foreach ($woocommerce->cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item["custom_price"])  &&  $cart_item['product_id'] == $free_product_id5) {
                $woocommerce->cart->remove_cart_item($cart_item_key);
            }

            if (isset($cart_item["custom_price"])  &&  $cart_item['product_id'] == $free_product_id6) {
                $woocommerce->cart->remove_cart_item($cart_item_key);
            }
        }
    }
}
//add_action( 'template_redirect', 'aapc_add_product_to_cart' );



/* Working on Aviform product */

// add_action('woocommerce_add_to_cart_validation', 'aviform_add_product_to_cart', 10, 5);
function aviform_add_product_to_cart($passed_validation, $product_id, $quantity)
{

    global $woocommerce;
    $custom_price = 0;
    $cart_item_data = array('custom_price' => $custom_price);
    $cart_products = array();
    $variation_id = 0;
    $variation = array();

    $f_terms = get_the_terms($product_id, 'product_cat');

    $p_term = array();

    foreach ($f_terms as $term) {
        $p_term[] = $term->term_id;
    }


    if (in_array('165', $p_term) || in_array('1128', $p_term)) {
        WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, $cart_item_data);
    }

    return $passed_validation;
}





function action_woocommerce_after_cart_item_quantity_update($cart_item_key, $quantity, $old_quantity, $cart)
{

    global $woocommerce;

    foreach (WC()->cart->get_cart() as $key => $values) {

        if ($key == $cart_item_key) {
            $_product = $values['data'];

            $pid = $_product->get_id();

            $f_terms = get_the_terms($pid, 'product_cat');

            $p_term = array();

            foreach ($f_terms as $term) {
                $p_term[] = $term->term_id;
            }

            if (in_array('165', $p_term) || in_array('1128', $p_term)) {
                foreach (WC()->cart->get_cart() as $key1 => $values1) {

                    $_product1 = $values1['data'];

                    $product_id = $_product1->get_id();

                    if ($pid == $product_id) {
                        $cart->cart_contents[$key1]['quantity'] = $quantity;
                    }
                }
            }


            // if($pid == 45765 || $pid == 20102 || $pid == 9901 || $pid == 49210 || $pid == 34995 || $pid == 34991 || $pid == 34991 || $pid == 49213)
            // {
            //     $cart->cart_contents[ $key ]['quantity'] = 1;
            // }
        }
    }
}

// add_action( 'woocommerce_after_cart_item_quantity_update', 'action_woocommerce_after_cart_item_quantity_update', 10, 4 ); 




function action_woocommerce_cart_item_removed($removed_cart_item_key, $cart)
{

    $line_item = $cart->removed_cart_contents[$removed_cart_item_key];

    $pid = $line_item['product_id'];

    $f_terms = get_the_terms($pid, 'product_cat');

    $p_term = array();

    foreach ($f_terms as $term) {
        $p_term[] = $term->term_id;
    }

    if (in_array('165', $p_term) || in_array('1128', $p_term)) {
        foreach (WC()->cart->get_cart() as $key => $values) {

            $_product = $values['data'];

            $product_id = $_product->get_id();

            if ($pid == $product_id) {
                WC()->cart->remove_cart_item($key);
            }
        }
    }
}

// add_action( 'woocommerce_cart_item_removed', 'action_woocommerce_cart_item_removed', 10, 2 ); 



function woocommerce_custom_price_to_cart_item_aviform($cart_object)
{

    if (!WC()->session->__isset("reload_checkout")) {
        foreach ($cart_object->cart_contents as $key => $value) {
            if (array_key_exists("custom_price", $value)) {
                $value['data']->set_price($value["custom_price"]);
            }
        }
    }
}

// add_action( 'woocommerce_before_calculate_totals', 'woocommerce_custom_price_to_cart_item_aviform', 99 );

/*End working on aviform product*/




//add_action('woocommerce_after_cart_table','free_gifts_cart_rules');
function free_gifts_cart_rules()
{

    global $woocommerce;
    $total = $woocommerce->cart->subtotal;

    if ($total >= 50 && $total < 100) :
    ?>

        <div class="free-gifts">
            <p class="free-gifts-title">Über € 50 Gratis:</p>
            <ul>
                <li> * Eurital Zuchtbuch</li>
                <li> * LED Lampe ( Inkl. Batterien)</li>
                <li> * Schlusselanhänger</li>
                <li> * 1 x Eurital Oregano</li>
            </ul>
        </div>

    <?php
    endif;

    if ($total >= 100) :
    ?>

        <div class="free-gifts">
            <p class="free-gifts-title">Über € 50 Gratis:</p>
            <ul>
                <li> * Eurital Zuchtbuch</li>
                <li> * LED Lampe ( Inkl. Batterien)</li>
                <li> * Schlusselanhänger</li>
                <li> * 1 x Eurital Oregano</li>
                <li> * 1 x Eurital Herbal Mix</li>
                <li> * 1 x 5 Näpfe</li>
            </ul>
        </div>

    <?php
    endif;
}

//add_action('woocommerce_gzd_review_order_before_submit','free_gifts_cart_rules_checkout');

function free_gifts_cart_rules_checkout()
{
    global $woocommerce;
    $total = $woocommerce->cart->subtotal;

    if ($total >= 50 && $total < 100) :
    ?>

        <div class="free-gifts">
            <p class="free-gifts-title">Über € 50 Gratis:</p>
            <ul>
                <li> * Eurital Zuchtbuch</li>
                <li> * LED Lampe ( Inkl. Batterien)</li>
                <li> * Schlusselanhänger</li>
                <li> * 1 x Eurital Oregano</li>
            </ul>
        </div>

    <?php
    endif;

    if ($total >= 100) :
    ?>
        <div class="free-gifts">
            <p class="free-gifts-title">Über € 50 Gratis:</p>
            <ul>
                <li> * Eurital Zuchtbuch</li>
                <li> * LED Lampe ( Inkl. Batterien)</li>
                <li> * Schlusselanhänger</li>
                <li> * 1 x Eurital Oregano</li>
                <li> * 1 x Eurital Herbal Mix</li>
                <li> * 1 x 5 Näpfe</li>
            </ul>
        </div>
    <?php
    endif;
}