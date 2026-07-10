<?php

// include('specialoffers.php');

define('BSF_PRODUCTS_NOTICES', false);

add_filter('use_block_editor_for_post', '__return_false');
add_filter( 'doing_it_wrong_trigger_error', '__return_false' );

// add_filter( 'oss_woocommerce_enable_extended_logging', '__return_true' );

add_action('wp_print_styles', 'dash_my_deregister_styles', 100);

function dash_my_deregister_styles()
{
    if (!current_user_can('administrator')) {
        wp_deregister_style('dashicons');
    }
    
	
    wp_deregister_style('wp-block-library');
    wp_deregister_style('astra-contact-form-7');
	
    wp_deregister_style('wc-blocks-style');
    wp_deregister_style('wc-blocks-vendors-style');
    wp_deregister_style('wc-blocks-style-css');
    wp_deregister_style('wc-blocks-vendors-style-css');
    wp_deregister_style('jetpack_css');
    wp_deregister_style('jetpack');
    
    wp_dequeue_style('astra-contact-form-7');
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wc-blocks-style');
    wp_dequeue_style('wc-blocks-vendors-style');
    wp_dequeue_style('wc-blocks-style-css');
    wp_dequeue_style('wc-blocks-vendors-style-css');
    wp_dequeue_style('jetpack_css');
    wp_dequeue_style('jetpack');
    
}

add_shortcode('custom_gtranslate', 'custom_gtranslate_fn');

function custom_gtranslate_fn() {
    if (is_admin())
        return false;

    ob_start();

    ?>
    <!-- GTranslate: https://gtranslate.io/ -->
    <a href="#" onclick="doGTranslate('<?php echo strtolower(ICL_LANGUAGE_CODE); ?>|en');return false;" title="English" class="glink nturl notranslate"><img src="//www.brieftaubenshop.de/wp-content/plugins/gtranslate/flags/24/en.png" height="24" width="24" alt="English" /> <span>English</span></a>
    <a href="#" onclick="doGTranslate('<?php echo strtolower(ICL_LANGUAGE_CODE); ?>|de');return false;" title="German" class="glink nturl notranslate"><img src="//www.brieftaubenshop.de/wp-content/plugins/gtranslate/flags/24/de.png" height="24" width="24" alt="German" /> <span>German</span></a>
    <a href="#" onclick="doGTranslate('<?php echo strtolower(ICL_LANGUAGE_CODE); ?>|nl');return false;" title="Nederlands" class="glink nturl notranslate"><img src="//www.brieftaubenshop.de/wp-content/plugins/gtranslate/flags/24/nl.png" height="24" width="24" alt="Nederlands" /> <span>Nederlands</span></a>
    <a href="#" onclick="doGTranslate('<?php echo strtolower(ICL_LANGUAGE_CODE); ?>|pl');return false;" title="Polish" class="glink nturl notranslate"><img src="//www.brieftaubenshop.de/wp-content/plugins/gtranslate/flags/24/pl.png" height="24" width="24" alt="Polish" /> <span>Poland</span></a>
    <style>
        #goog-gt-tt {
            display: none !important;
        }

        .goog-te-banner-frame {
            display: none !important;
        }

        .goog-te-menu-value:hover {
            text-decoration: none !important;
        }

        .goog-text-highlight {
            background-color: transparent !important;
            box-shadow: none !important;
        }

        body {
            top: 0 !important;
        }

        #google_translate_element2 {
            display: none !important;
        }
    </style>

    <div id="google_translate_element2"></div>
    <script>
        function googleTranslateElementInit2() {
            new google.translate.TranslateElement({
                pageLanguage: '<?php echo strtolower(ICL_LANGUAGE_CODE); ?>',
                autoDisplay: false
            }, 'google_translate_element2');
        }
    </script>
    <script src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit2"></script>

    <script>
        function GTranslateGetCurrentLang() {
            var keyValue = document['cookie'].match('(^|;) ?googtrans=([^;]*)(;|$)');
            return keyValue ? keyValue[2].split('/')[2] : null;
        }

        function GTranslateFireEvent(element, event) {
            try {
                if (document.createEventObject) {
                    var evt = document.createEventObject();
                    element.fireEvent('on' + event, evt)
                } else {
                    var evt = document.createEvent('HTMLEvents');
                    evt.initEvent(event, true, true);
                    element.dispatchEvent(evt)
                }
            } catch (e) {}
        }

        function doGTranslate(lang_pair) {
            if (lang_pair.value) lang_pair = lang_pair.value;
            if (lang_pair == '') return;
            var lang = lang_pair.split('|')[1];
            if (GTranslateGetCurrentLang() == null && lang == lang_pair.split('|')[0]) return;
            var teCombo;
            var sel = document.getElementsByTagName('select');
            for (var i = 0; i < sel.length; i++)
                if (sel[i].className.indexOf('goog-te-combo') != -1) {
                    teCombo = sel[i];
                    break;
                } if (document.getElementById('google_translate_element2') == null || document.getElementById('google_translate_element2').innerHTML.length == 0 || teCombo.length == 0 || teCombo.innerHTML.length == 0) {
                setTimeout(function() {
                    doGTranslate(lang_pair)
                }, 500)
            } else {
                teCombo.value = lang;
                GTranslateFireEvent(teCombo, 'change');
                GTranslateFireEvent(teCombo, 'change')
            }
        }
    </script>

    <?php
    return ob_get_clean();
}


/* Changing the phone no. before generating DHL label */

add_filter('pr_shipping_dhl_label_args', 'remove_the_dhl_phone', 10, 2);
function remove_the_dhl_phone($args, $order_id)
{

    $args['shipping_address']['phone'] = '';

    return $args;
}

/* Change the canonical link for the shop page and category pages to first page link */

function yoast_seo_canonical_change_woocom_shop($canonical)
{

    if (is_shop()  || is_product_category()) {
        if (is_paged()) {
            $canon_page = get_pagenum_link(1);
            return $canon_page;
        }
    }

    return $canonical;
}

add_filter('wpseo_canonical', 'yoast_seo_canonical_change_woocom_shop', 99, 1);

add_action('woocommerce_after_add_to_cart_form', 'woocommerce_after_add_to_cart_form_special_info');

function woocommerce_after_add_to_cart_form_special_info()
{
    $specialinfo = get_field('product_special_info');
    if ($specialinfo) {
    ?>
        <div role="alert" style="background-color: #d9edf7; border-color: #bce8f1; color: #31708f; padding: 20px; line-height: 1.4;" class="alert">
            <?php echo $specialinfo; ?>
        </div>
    <?php
    }
}

add_action('wpo_create_custom_product', 'wpo_create_custom_product_visibilityset', 99, 2);

function wpo_create_custom_product_visibilityset($post_id, $product)
{
    $product->set_catalog_visibility('hidden');
    update_post_meta($post_id, '_visibility', 'hidden');
}

add_filter('ywpi_attach_invoice_on_order_status', 'send_on_new_order_bts', 99, 1);

function send_on_new_order_bts($allowed_statuses)
{

    if (!in_array('new_order', $allowed_statuses)) {
        $allowed_statuses[] = 'new_order';
    }
    return $allowed_statuses;
}

// ADDING A CUSTOM COLUMN TITLE TO ADMIN PRODUCTS LIST
add_filter('manage_edit-product_columns', 'custom_product_column', 11);
function custom_product_column($columns)
{
    //add columns
    $columns['shipping_class'] = __('Shipping class', 'woocommerce'); // title
    return $columns;
}

// ADDING THE DATA FOR EACH PRODUCTS BY COLUMN (EXAMPLE)
add_action('manage_product_posts_custom_column', 'custom_product_list_column_content', 10, 2);
function custom_product_list_column_content($column, $product_id)
{
    global $post;
    $_product = wc_get_product($product_id);
    $shipclass = $_product->get_shipping_class();

    switch ($column) {
        case 'shipping_class':
            echo $shipclass;
            break;
    }
}

// Adding to admin order list bulk dropdown a custom action 'custom_cancellation'
add_filter('bulk_actions-edit-shop_order', 'cancellation_bulk_actions_edit_product', 99, 1);
function cancellation_bulk_actions_edit_product($actions)
{
    $actions['write_cancellation'] = __('Cancel orders', 'woocommerce');
    return $actions;
}

// Make the action from selected orders
add_filter('handle_bulk_actions-edit-shop_order', 'cancellation_handle_bulk_action_edit_shop_order', 10, 3);
function cancellation_handle_bulk_action_edit_shop_order($redirect_to, $action, $post_ids)
{
    if ($action !== 'write_cancellation')
        return $redirect_to; // Exit

    $processed_ids = array();

    foreach ($post_ids as $post_id) {
        $order = wc_get_order($post_id);
        $order->update_status('cancelled');
        $processed_ids[] = $post_id;
    }

    return $redirect_to = add_query_arg(array(
        'write_cancellation' => '1',
        'processed_count' => count($processed_ids),
        'processed_ids' => implode(',', $processed_ids),
    ), $redirect_to);
}


// The results notice from bulk action on orders
add_action('admin_notices', 'cancellation_bulk_action_admin_notice');
function cancellation_bulk_action_admin_notice()
{
    if (empty($_REQUEST['write_cancellation'])) return; // Exit

    $count = intval($_REQUEST['processed_count']);

    printf('<div id="message" class="updated fade"><p>' .
        _n(
            'Processed %s Order for cancellation.',
            'Processed %s Orders for cancellation.',
            $count,
            'write_cancellation'
        ) . '</p></div>', $count);
}

add_filter('woocommerce_available_payment_gateways', 'bbloomer_payment_gateway_disable_country', 99, 1);
add_action('woocommerce_after_add_to_cart_form', 'bbloomer_custom_action', 5);

function bbloomer_custom_action()
{
    $link = 'Anfrage für Produkt: *' . get_the_title() . '* - ' . get_the_permalink();
    echo "<a style='padding: 10px 20px 10px 50px; background: url(https://d3h4lv9ugipyd0.cloudfront.net/wp-content/uploads/2018/12/unnamed.png); background-size: 25px; background-position: 15px; background-repeat: no-repeat; border: none; border-radius: 20px; margin-bottom: 30px; display: inline-block; background-color: #1ebea5; color: #fff; font-weight: bold;' class='wp-order' href='https://wa.me/4915238829620?text=" . $link . "' >Order on Whatsapp</a>";
}

add_filter('woe_get_order_product_fields', 'woe_add_order_fields');

function woe_add_order_fields($fields)
{
    $fields['revenueaccount'] = array('label' => 'Revenue Account Number', 'colname' => 'Revenue Account Number', 'checked' => 1);
    $fields['paymentmethodnumber'] = array('label' => 'Payment Method Number', 'colname' => 'Payment Method Number', 'checked' => 1);
    $fields['taxrate'] = array('label' => 'TAX RATE', 'colname' => 'TAX RATE', 'checked' => 1);
    $fields['tax7rate'] = array('label' => 'Tax amount 7% 8305', 'colname' => 'Tax amount 7% 8305', 'checked' => 1);
    $fields['tax19rate'] = array('label' => 'Tax amount 19% 8405', 'colname' => 'Tax amount 19% 8405', 'checked' => 1);
    return $fields;
}

add_filter('woe_get_order_product_value_revenueaccount', function ($value, $order, $item, $product) {

    $total = $item->get_total();
    $total_tax = $item->get_total_tax();
    if ($total_tax > 0) {
        $tax_rate = round($total_tax * 100 / $total);
    }
    if ($tax_rate == 7)
        return '8305';
    elseif ($tax_rate == 19)
        return '8405';
    return '--';
}, 10, 4);

add_filter('woe_get_order_product_value_paymentmethodnumber', function ($value, $order, $item, $product) {

    $paymentmethod = $order->get_payment_method();
    $paymentmethodno = 13000;
    if (stripos($paymentmethod, 'amazon') !== FALSE) {
        $paymentmethodno = 16000;
    }
    if (stripos($paymentmethod, 'paypal') !== FALSE) {
        $paymentmethodno = 14000;
    }
    if (stripos($paymentmethod, 'klarna') !== FALSE) {
        $paymentmethodno = 17000;
    }
    if (stripos($paymentmethod, 'kco') !== FALSE) {
        $paymentmethodno = 17000;
    }
    if (stripos($paymentmethod, 'stripe') !== FALSE) {
        $paymentmethodno = 17000;
    }
    return $paymentmethodno;
}, 10, 4);

add_filter('woe_get_order_product_value_tax7rate', function ($value, $order, $item, $product) {
    $total = $item->get_total();
    $total_tax = $item->get_total_tax();
    if ($total_tax > 0) {
        $tax_rate = round($total_tax * 100 / $total);
    }
    if ($tax_rate == 7)
        $total_tax = $total_tax;
    else
        $total_tax = '';
    return $total_tax;
}, 10, 4);

add_filter('woe_get_order_product_value_tax19rate', function ($value, $order, $item, $product) {
    $total = $item->get_total();
    $total_tax = $item->get_total_tax();
    if ($total_tax > 0) {
        $tax_rate = round($total_tax * 100 / $total);
    }
    if ($tax_rate == 19)
        $total_tax = $total_tax;
    else
        $total_tax = '';
    return $total_tax;
}, 10, 4);

add_filter('woe_get_order_product_value_taxrate', function ($value, $order, $item, $product) {
    $total = $item->get_total();
    $total_tax = $item->get_total_tax();
    if ($total_tax > 0) {
        $tax_rate = round($total_tax * 100 / $total);
    }
    return $tax_rate;
}, 10, 4);

add_action('kco_wc_before_extra_fields', 'kco_wc_show_germanized_field');
function kco_wc_show_germanized_field()
{
    if (class_exists('WooCommerce_Germanized')) {
        $manager = WC_GZD_Legal_Checkbox_Manager::instance();
        $manager->render();
    }
}


function remove_dashboard_widgets()
{
    // remove WooCommerce Plugin News - by Visser Labs   
    remove_meta_box('yith_dashboard_products_news', 'dashboard', 'normal');
    remove_meta_box('dashboard_activity', 'dashboard', 'normal');
    remove_meta_box('dashboard_primary', 'dashboard', 'normal');
    remove_meta_box('dashboard_quick_press', 'dashboard', 'normal');
    remove_meta_box('example_dashboard_widget', 'dashboard', 'normal');
    remove_meta_box('wpseo-dashboard-overview', 'dashboard', 'normal');
    remove_meta_box('woocommerce_dashboard_recent_reviews', 'dashboard', 'normal');

    // remove WooCommerce Plugins - by Visser Labs
    remove_meta_box('yith_dashboard_blog_news', 'dashboard', 'normal');
}
add_action('wp_user_dashboard_setup', 'remove_dashboard_widgets', 20);
add_action('wp_dashboard_setup', 'remove_dashboard_widgets', 20);

function yith_remove_notice($show_license_notice)
{
    return false;
}

add_filter("yith_plugin_fw_show_activate_license_notice", "yith_remove_notice", 99999999999999999, 1);

function bbloomer_payment_gateway_disable_country($available_gateways)
{

    global $woocommerce;

    if (is_admin()) return;

    if (isset($woocommerce->customer)) {
        if (isset($available_gateways['invoice']) && $woocommerce->customer->get_shipping_country() <> 'DE') {
            unset($available_gateways['invoice']);
        }
    }
    return $available_gateways;
}


add_filter('woocommerce_get_price_html', 'change_displayed_sale_price_html', 10, 2);
function change_displayed_sale_price_html($price, $product)
{
    // Only on sale products on frontend and excluding min/max price on variable products
    if ($product->is_on_sale() && !is_admin() && !$product->is_type('variable')) {
        // Get product prices
        $regular_price = (float) $product->get_regular_price(); // Regular price
        $sale_price = (float) $product->get_price(); // Active price (the "Sale price" when on-sale)

        // "Saving price" calculation and formatting
        $saving_price = wc_price($regular_price - $sale_price);

        // "Saving Percentage" calculation and formatting
        $precision = 1; // Max number of decimals
        $saving_percentage = round(100 - ($sale_price / $regular_price * 100), 1) . '%';

        // Append to the formated html price
        $price .= sprintf(__('<p class="saved-sale">Sie sparen: %s <em>(%s)</em></p>', 'woocommerce'), $saving_price, $saving_percentage);
    }
    return $price;
}

add_action('register_new_user', 'wp_send_admin_notify', 999);

function wp_send_admin_notify()
{
    $headers = array('Content-Type: text/html; charset=UTF-8');
    $message = 'New Registration on Brieftaubenshop';
    $message .= "<br/>NAME: " . $_POST['first-name'];
    $message .= "<br/>SURNAME: " . $_POST['last-name'];
    $message .= "<br/>EMAIL: " . $_POST['email'];
    wp_mail('Brieftaubenshop@t-online.de', 'New Registration', $message, $headers);
}


// alter the subscriptions error
function my_woocommerce_add_error($error)
{
    if (strcmp($error, 'akzeptiere')) {
        error_log($error);
        // $tofind = 'Bitte akzeptiere unsere <a href="" target="_blank">Allgemeinen Gesch채ftsbedingungen</a> und <a href="" target="_blank">Widerrufsbestimmungen</a>.';
        $tofind = 'Bitte akzeptiere unsere <a href="https://www.brieftaubenshop.de/agb/" target="_blank">Allgemeinen Geschäftsbedingungen</a> und <a href="" target="_blank">Widerrufsbestimmungen</a>.';
        $toreplace = 'Bitte akzeptiere unsere Allgemeinen Geschäftsbedingungen und Widerrufsbestimmungen.<span class="checkbox">Klicken Sie, um zu akzeptieren</span>';
        $error = str_replace($tofind, $toreplace, $error);
    }
    return $error;
}

add_filter('woocommerce_add_error', 'my_woocommerce_add_error', 99, 1);


function add_flyjs_function()
{
    wp_enqueue_script('jquery-ui-fly', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.5/jquery-ui.min.js', array('jquery'), '1.0.0', true);
}
add_action('wp_enqueue_scripts', 'add_flyjs_function');


// add_action('wp_head', 'mouseflow_bas_analytics');
/* Done using plugin */

function mouseflow_bas_analytics()
{

    if (is_checkout() && !is_wc_endpoint_url('order-received')) { ?>

        <script type="text/javascript">
            window._mfq = window._mfq || [];
            (function() {
                var mf = document.createElement("script");
                mf.type = "text/javascript";
                mf.async = true;
                mf.src = "//cdn.mouseflow.com/projects/4a65b369-bfab-41ae-b2b2-53a3952e618d.js";
                document.getElementsByTagName("head")[0].appendChild(mf);
            })();
        </script>

    <?php }

    if (is_wc_endpoint_url('order-received')) { ?>

        <script type="text/javascript">
            window._mfq = window._mfq || [];
            (function() {
                var mf = document.createElement("script");
                mf.type = "text/javascript";
                mf.async = true;
                mf.src = "//cdn.mouseflow.com/projects/4a65b369-bfab-41ae-b2b2-53a3952e618d.js";
                document.getElementsByTagName("head")[0].appendChild(mf);
            })();
            window._mfq.push(["tag", "OrderPlaced"]);
        </script>

        <?php }
}


add_filter('woocommerce_reports_order_statuses', 'include_custom_order_status_to_reports', 20, 1);

function include_custom_order_status_to_reports($statuses)
{
    // Adding the custom order status to the 3 default woocommerce order statuses
    return array('processing', 'pending', 'completed', 'on-hold');
}

function wc_custom_lost_password_form($atts)
{

    return wc_get_template('myaccount/form-lost-password.php', array('form' => 'lost_password'));
}


add_shortcode('lost_password_form', 'wc_custom_lost_password_form');

add_filter('yith_plugin_fw_show_activate_license_notice', 'yith_plugin_fw_show_activate_license_notice_false');

function yith_plugin_fw_show_activate_license_notice_false()
{
    return false;
}

function remove_smile_fonts()
{
    wp_dequeue_style('bsf-Defaults');
    wp_deregister_style('bsf-Defaults');
}
add_action('wp_print_styles', 'remove_smile_fonts', 100);


function deregister_or_dequeue_scripts_brief()
{
    wp_dequeue_script('wc-password-strength-meter');
    wp_deregister_script('wc-password-strength-meter');
}

add_action('wp_print_scripts', 'deregister_or_dequeue_scripts_brief', 100);

function reduce_woocommerce_min_strength_requirement($strength)
{
    return 0;
}



function disallowed_admin_pages()
{
    global $pagenow;
    $cuser = get_current_user_id();
    if ($cuser == 6) {
        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'wc-reports') {

            wp_redirect(admin_url('/'), 301);
            exit;
        }
    }
}

add_action('admin_init', 'disallowed_admin_pages');

function my_custom_admin_notice()
{

    global $pagenow;
    if ($pagenow == 'post.php' && !empty($_GET['post']) && $_GET['action'] == 'edit') :
        $post_id = $_GET['post'];
        $post = get_post($post_id);
        $post_type = $post->post_type;
        if ($post_type == 'shop_order') :
            $order = wc_get_order($post_id);
            $email = $order->billing_email;
            $user = get_user_by('email', $email);
            if ($user) {
                $customer_id = $user->id;
                $customer_note = get_user_meta($customer_id, 'customer_notes', true);
                if (!empty($customer_note)) :
                    ?>
                    <div class="notice notice-success is-dismissible" style="border: 3px solid red; border-radius: 5px;">
                        <p style="font-size:15px; font-weight:600;"><?php _e($customer_note); ?></p>
                    </div>
                    <?php
                endif;
            }
        endif;
    endif;
}

add_action('admin_notices', 'my_custom_admin_notice');

add_action('admin_head', 'my_custom_script');

function my_custom_script()
{
    ?>
    <script>
        jQuery(document).ready(function() {

            var customer_note = jQuery("#acf-customer_order_note").html();

            var note_field = '<div id="acf-customer_order_note" class="field field_type-textarea field_key-field_5b3db155cdafa" data-field_name="customer_order_note" data-field_key="field_5b3db155cdafa" data-field_type="textarea">' + customer_note + '</div>';

            jQuery("#acf-customer_order_note").remove();

            /*jQuery("#woocommerce-order-notes").remove();*/

            jQuery(".order_actions.submitbox").after(note_field);

        });
    </script>

    <style>
        #acf-customer_order_note {
            padding: 1em;
        }

        #acf-customer_order_note p label {
            font-weight: bold;
        }

        #acf-field-customer_order_note {
            width: 100%;
        }
    </style>

    <?php
}

add_action('admin_head', 'my_custom_fonts');

function my_custom_fonts()
{
    $user_id = array(8, 4, 3092);
    $current_user = get_current_user_id();

    echo '<style> #bulk-action-selector-top option[value="mark_completed"], #bulk-action-selector-top option[value="wc_pip_print_invoice"], #bulk-action-selector-top option[value="wc_pip_send_email_invoice"], #bulk-action-selector-top option[value="wc_pip_print_packing_list"], #bulk-action-selector-top option[value="wc_pip_send_email_packing_list"], #bulk-action-selector-top option[value="wc_pip_print_pick_list"], #bulk-action-selector-top option[value="wc_pip_send_email_pick_list"]{ display:none; } .wc_actions .button.packing-slip{ display:none !important; } .widefat .column-wc_actions a.button.ywpi_buttons{ text-indent:50px !important; } .wp-pointer-inner-content .wc_pip_print_invoice, .wp-pointer-inner-content .wc_pip_send_email_invoice{ display:none !important; } } </style> <script> jQuery(document).ready(function($){ $("a#dhl-label-print").html("DOWNLOAD") }); </script>';
    echo '<script> jQuery(document).ready(function(){ jQuery("#wpla_tracking_provider").val("DHL"); jQuery("#select2-wpla_tracking_provider-container").attr("title","DHL"); jQuery("#select2-wpla_tracking_provider-container").html("DHL"); jQuery("input#custom_tracking_provider").val("DHL"); var tracking=jQuery(".note_content").find("a").html(); if(tracking!=""){ jQuery("input#tracking_number").val(tracking); jQuery("input#wpla_tracking_number").val(tracking); } jQuery("input#tracking_number").on("change",function(){ var tracking_no=jQuery(this).val(); var tracking_url="https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html?piececode="+tracking_no; jQuery("input#custom_tracking_link").val(tracking_url); }); }); </script>';

    if (!in_array($current_user, $user_id)) {
        echo '<style> .toplevel_page_woocommerce .wp-submenu li:nth-child(9){ display:none; } </style>';
    }
}

add_action('admin_footer', 'my_custom_fonts_footer');

function my_custom_fonts_footer()
{
    echo "<script> jQuery(document).ready(function(){ jQuery('.wp-list-table .type-shop_order .cacie-editable-container').addClass('no-link'); }); </script>";
}

function filter_woocommerce_order_shipping_to_display_shipped_via($nbsp_small_class_shipped_via_sprintf_via_s_woocommerce_this_get_shipping_method_small, $instance)
{
    // make filter magic happen here... 
    return ' Versandkosten';
};

// add the filter 
add_filter('woocommerce_order_shipping_to_display_shipped_via', 'filter_woocommerce_order_shipping_to_display_shipped_via', 10, 2);



add_action('woocommerce_email_before_order_table', 'print_the_customer_id');

function print_the_customer_id($order)
{
    $order_data = $order->get_data();
    $payment_method = $order_data['payment_method'];
    $user_id = $order->get_user_id();

    if ($user_id > 0) {
        echo "Kundennummer: " . $user_id . "<br>";
    }

    if ($payment_method == 'bacs') {
        echo "Zahlungsmethode: Vorkasse";
    } else if ($payment_method == 'direct-debit') {
        echo "Zahlungsmethode: Lastschrift";
    } else if ($payment_method == 'paypal') {
        echo "Zahlungsmethode: Paypal";
    } else if ($payment_method == 'invoice') {
        echo "Zahlungsmethode: Rechnung";
    } else if ($payment_method == 'paypal_plus') {
        echo "Zahlungsmethode: Paypal";
    } else if ($payment_method == 'Kco') {
        echo "Zahlungsmethode: Klarna";
    } else if ($payment_method == 'other_payment') {
        echo "Zahlungsmethode: Amazon";
    }
}

add_filter('woocommerce_product_categories_widget_dropdown_args', 'wpsites_product_cat_widget');

function wpsites_product_cat_widget($args)
{

    $args = array(
        'hierarchical' => 1,
        'hide_empty' => 0,
        'parent' => 11,
        'taxonomy' => 'product_cat',
        'order_by' => 'name'
    );

    return $args;
}


add_filter( 'get_terms', 'ts_get_subcategory_terms', 99, 3 );
function ts_get_subcategory_terms( $terms, $taxonomies, $args ) {
    $new_terms = array();
    // if it is a product category and on the shop page
    if ( in_array( 'product_cat', $taxonomies ) && ! is_admin() &&is_shop() ) {
        foreach( $terms as $key => $term ) {
            if ( !in_array( $term->slug, array( 'demo-category' ) ) ) { //pass the slug name here
                $new_terms[] = $term;
            }
        }
        $terms = $new_terms;
    }
    return $terms;
}


add_shortcode('woocommerce-product-categories', 'woocommerce_product_categories');

function woocommerce_product_categories()
{
    $cat = '<aside id="woocommerce_product_categories-13" class="widget woocommerce widget_product_categories"> <div class="is-divider small"></div>';

    $args = array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'menu_order' => false,
        'orderby' => 'title',
        'order' => 'ASC',
        'parent'   => 0
    );

    $product_cat = get_terms($args);

    usort($product_cat, function ($a, $b) {
        return strcmp($a->name, $b->name);
    });

    $cat .= '<ul class="product-categories">';
    foreach ($product_cat as $key => $parent_product_cat) {

        $count = count(get_term_children($parent_product_cat->term_id, 'product_cat'));
        $count = 0;

        if ($count > 0) {
            $parent_class = 'cat-parent has-child';
        } else {
            $parent_class = '';
        }

        if ($parent_product_cat->term_id != 1379) {

            $is_angebot = get_field('is_angebot', $parent_product_cat);
            if ($is_angebot == 1) {
                $link_class = 'angebot';
            } else {
                $link_class = '';
            }

            $cat .= '
        <li class="cat-item cat-item-' . $parent_product_cat->term_id . ' ' . $parent_class . '"><a class="' . $link_class . '" href="' . get_term_link($parent_product_cat->term_id) . '">' . $parent_product_cat->name . '</a>
          ';
            $child_args = array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
                'parent'   => $parent_product_cat->term_id,
                'orderby' => 'title',
                'order' => 'ASC',
                'menu_order' => false
            );

            if ($count > 0) {
                $cat .= '
              <ul class="children">';
                $child_product_cats = get_terms($child_args);
                usort($child_product_cats, function ($a, $b) {
                    return strcmp($a->name, $b->name);
                });
                foreach ($child_product_cats as $child_product_cat) {
                    $cat .= '<li class="cat-item cat-item-' . $child_product_cat->term_id . '"><a href="' . get_term_link($child_product_cat->term_id) . '">' . $child_product_cat->name . '</a></li>';
                }
                $cat .= '</ul>';
            }

            $cat .= '</li>';
        }
    }
    $cat .= '</ul>
  </aside>';

    return $cat;
}


add_filter('woocommerce_order_button_text', 'woo_custom_order_button_text');
function woo_custom_order_button_text()
{
    return __('ZAHLUNGSPFLICHTIG BESTELLEN', 'woocommerce');
}


add_filter('woocommerce_email_attachments', 'attach_terms_conditions_pdf_to_email', 100, 3);

function attach_terms_conditions_pdf_to_email($attachments, $status, $order)
{

    $allowed_statuses = array('new_order', 'customer_invoice', 'customer_processing_order', 'customer_on_hold_order');

    if (isset($status) && in_array($status, $allowed_statuses)) {
        $attachments[] = get_attached_file(168623);
        $attachments[] = get_attached_file(168622);
        $attachments[] = get_attached_file(168621);
    }

    return $attachments;
}

//------------Add Input field to poduct page------------
function kia_custom_option()
{
    if ( (has_term('clip-telefonringe', 'product_cat') || has_term('ring-stickers-and-clip-rings', 'product_cat')) && !is_single(12843) && !is_single(164746) && !is_single(12849) && !is_single(164730) ) {
        $current_lang = apply_filters( 'wpml_current_language', NULL );
        if($current_lang == 'en') {
            $label = __('Telephone number', 'kia-plugin-textdomain');
        } else {
            $label = __('Telefonnummer', 'kia-plugin-textdomain');   
        }
        $value = isset($_POST['_custom_option']) ? sanitize_text_field($_POST['_custom_option']) : '';
        printf('<table class="_custom_input"><tr><td class="label"><label>%s</label></td><td class="value"><input name="_custom_option" value="%s" class="custom_user_input" required="required"/></td></tr></table>', $label, esc_attr($value));
    }
}
add_action('woocommerce_before_add_to_cart_button', 'kia_custom_option', 9);

//-----------Add the custom data to the cart item-----------
function kia_add_cart_item_data($cart_item, $product_id)
{

    if (isset($_POST['_custom_option'])) {
        $cart_item['user_custom_option'] = sanitize_text_field($_POST['_custom_option']);
    }
    return $cart_item;
}
add_filter('woocommerce_add_cart_item_data', 'kia_add_cart_item_data', 10, 2);

//---------Preserve the Cart Data---------
function kia_get_cart_item_from_session($cart_item, $values)
{

    if (isset($values['user_custom_option'])) {
        $cart_item['user_custom_option'] = $values['user_custom_option'];
    }
    return $cart_item;
}
add_filter('woocommerce_get_cart_item_from_session', 'kia_get_cart_item_from_session', 20, 2);

//----------Save the Custom Data On Checkout-----------
function kia_add_order_item_meta($item_id, $values)
{

    if (!empty($values['user_custom_option'])) {
        woocommerce_add_order_item_meta($item_id, 'user_custom_option', $values['user_custom_option']);
    }
}
add_action('woocommerce_add_order_item_meta', 'kia_add_order_item_meta', 10, 2);

//-----------Display all the Things!-----------
function kia_get_item_data($other_data, $cart_item)
{

    if (isset($cart_item['user_custom_option'])) {

        $other_data[] = array(
            'name' => __('Rings label', 'kia-plugin-textdomain'),
            'value' => sanitize_text_field($cart_item['user_custom_option'])
        );
    }

    return $other_data;
}
add_filter('woocommerce_get_item_data', 'kia_get_item_data', 10, 2);

//-----------Display to order Overview page-----------
function kia_order_item_product($cart_item, $order_item)
{

    if (isset($order_item['user_custom_option'])) {
        $cart_item_meta['user_custom_option'] = $order_item['user_custom_option'];
    }

    return $cart_item;
}
add_filter('woocommerce_order_item_product', 'kia_order_item_product', 10, 2);

add_action('woocommerce_review_order_after_order_total_custom', 'wc_gzd_cart_totals_order_total_tax_html_test');

function wc_gzd_cart_totals_order_total_tax_html_test()
{

    if (function_exists('wc_gzd_get_cart_total_taxes')) {
        foreach (wc_gzd_get_cart_total_taxes() as $tax) :

            $label = wc_gzd_get_tax_rate_label($tax['tax']->rate);

    ?>
            <tr class="order-tax">
                <?php
                if (is_checkout()) {
                ?>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                <?php
                }
                ?>
                <th><?php echo $label; ?></th>
                <td data-title="<?php echo esc_attr($label); ?>" colspan="3"><?php echo wc_price($tax['amount']); ?></td>
            </tr>

    <?php endforeach;
    }
}

add_action('woocommerce_before_mini_cart', 'continue_shopping_button', 1);
function continue_shopping_button()
{
    echo '<a href="https://www.brieftaubenshop.de/produktkategorie/" class="button checkout wc-forward">'.__('Continue shopping
').'</a>';
}

add_action('woocommerce_after_mini_cart', 'continue_shopping_button1', 1);
function continue_shopping_button1()
{
    if (!is_user_logged_in()) :
        echo '<p style="text-align:center;font-weight:bold; color:#9C1B20;">Sie bestellen als “Gast”!</p>';
    endif;
}



function add_bcc_to_certain_emails($headers, $object)
{

    $add_bcc_to = array(
        'customer_processing_order'
    );

    if (in_array($object, $add_bcc_to)) {
        $headers .= 'BCC: Brieftaubenshop <brieftaubenshop@t-online.de>' . "\r\n";
    }

    return $headers;
}

add_filter('woocommerce_email_headers', 'add_bcc_to_certain_emails', 10, 2);

add_filter('pre_option_woocommerce_default_gateway' . '__return_false', 99);

add_shortcode('guest-top-text', 'guest_mode_text');
function guest_mode_text()
{
    if (!is_user_logged_in()) :
        echo '<a href="https://wa.me/4915238829620" class="upper-top-text" style="font-size:18px; color:#0A4A7C; font-weight:bold;">Sie sind im “Gastmodus”  und können ohne Anmeldung einkaufen</a>';
    endif;
}


add_action('wp_footer', 'checkout_page_functions');
function checkout_page_functions()
{
    if (is_page('checkout')) {
        ?>
        <script>
            jQuery(document).ready(function($) {
                var checkbox = $('.mc4wp-checkbox.mc4wp-checkbox-woocommerce').html();
                var newblock = '<div class="newsletter-checkout">' + checkbox + '</div>';

                $('.legal.checkbox-legal').after(newblock);
                $('.mc4wp-checkbox.mc4wp-checkbox-woocommerce').remove();

                var register_link = '<div class="woocommerce-message">Falls Sie kein Kundenkonto besitzen, kommen Sie <a href="#" class="lrm-register">hier zur <strong>Registrierung</strong>!</a></div>';
                $('.woocommerce-form-login-toggle .woocommerce-message').after(register_link);

                $('.woocommerce-form-login-toggle .woocommerce-message .showlogin').html(' Klicke hier, um dich <strong>anzumelden</strong>');

                setTimeout(function() {

                    $('#payment_method_bacs').removeAttr('checked');
                    $('.payment_box.payment_method_bacs').css('display', 'none');

                }, 1000);

                console.log('success');

            });
        </script>
        <?php
    }
}


add_action('woocommerce_gzd_review_order_before_submit', 'legal_checkbox_form');
function legal_checkbox_form() {

    $agb_text = __('I have accepted the {{1}} and have noted the {{2}}.', 'bts');
    $agb_text = str_replace('{{1}}', '<a href="/agb" target="_blank">'.__('Terms & Conditions', 'bts').'</a>', $agb_text);
    $agb_text = str_replace('{{2}}', '<a href="/widerrufsbelehrung" target="_blank">'.__('Right of Revocation', 'bts').'</a>', $agb_text);
    
    ?>

    <div class="wc-gzd-checkbox-placeholder wc-gzd-checkbox-placeholder-legal-new">
        <p class="legal form-row checkbox-legal validate-required" data-checkbox="terms">
            <span class="legal-note">Hinweis: Sie müssen diesen Haken setzen, da ansonsten der Einkauf nicht durchgeführt werden kann!</span><label for="legal" class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="legal" id="legal">
                <span class="woocommerce-gzd-legal-checkbox-text"><?php echo $agb_text; ?></span>
            </label>
        </p>
        <div class="newsletter-checkout"><label><input type="checkbox" name="_mc4wp_subscribe_woocommerce" value="1"><span>Melden Sie mich für den Newsletter an!</span></label></div>
    </div>

    <?php
}

/*Automatic button for DHL Label Creation and send Tracking details*/

function generate_dhl_label($column)
{

    global $post;

    if ('5c528af4d5adc' === $column) {
        $post_id = $post->ID;
        $order = wc_get_order($post_id);
        // $total_weight = 0;
        $total_weight = 5;

        /* 
        foreach ($order->get_items() as $item_id => $product_item) {

            $quantity = $product_item->get_quantity();
            $product = $product_item->get_product();

            if ($product) {
                $product_weight = $product->get_weight();
                if ($product_weight) {
                    $total_weight += floatval($product_weight * $quantity);
                }
            }
        } */

        $wp_nonce = wp_create_nonce('create-dhl-label');
        $admin_url = admin_url('admin-ajax.php');

        $already_set = get_post_meta($post_id, '_pr_shipment_dhl_label_tracking', true);

        $html = '<div class="generate_label_container">';

        if ($already_set) {
            $html .= '<a href="https://tierfutterpro.de/dhl_download_label/' . $post_id . '" style="display:block">Downloal Label</a>';
        } else {
            $html .= '<span style="cursor:pointer; background: #eee; border: 1px solid #ccc; border-radius: 5px; padding: 0.75em;" class="generate_dhl_label" data-post_id="' . $post_id . '" data-wp_nonce="' . $wp_nonce . '" data-admin_url="' . $admin_url . '" data-weight="' . $total_weight . '">Generate</span>';
        }

        $html .= '<img style="display:none; width: 50px; margin-top: 10px; margin-left: 10px;" class="generate_label_loader"  src="/wp-content/uploads/2019/01/loader.gif">';

        $html .= '</div>';

        if ($total_weight > 0) :

            echo $html;

        endif;
    }
}


add_action('manage_shop_order_posts_custom_column', 'generate_dhl_label');

add_action('admin_footer', 'dhl_label_function');

function dhl_label_function()
{
    ?>

    <script>
        jQuery(document).ready(function($) {
            console.log('ready');
            $('.column-5c528af4d5adc').addClass('no-link');

            $('.generate_dhl_label').on('click', function() {

                $(this).parent().find('.generate_label_loader').show();
                var post_id = $(this).attr('data-post_id');
                var wp_nonce = $(this).attr('data-wp_nonce');
                var admin_url = $(this).attr('data-admin_url');
                var weight = $(this).attr('data-weight');
                var btn = $(this);

                var data = {
                    action: 'wc_shipment_dhl_gen_label',
                    order_id: post_id,
                    pr_dhl_label_nonce: wp_nonce,
                    pr_dhl_weight: weight,
                    pr_dhl_email_notification: 'no',
                    pr_dhl_additional_insurance: 'no',
                    pr_dhl_no_neighbor: 'no',
                    pr_dhl_named_person: 'no',
                    pr_dhl_premium: 'no',
                    pr_dhl_bulky_goods: 'no',
                    pr_dhl_identcheck: 'no',
                    pr_dhl_identcheck_dob: '',
                    pr_dhl_is_codeable: 'no',
                    pr_dhl_product: 'V01PAK',
                    pr_dhl_preferred_day: 0,
                    pr_dhl_preferred_time: '',
                    pr_dhl_age_visual: 0,
                    pr_dhl_identcheck_age: 0
                };

                $.post(admin_url, data, function(response) {

                    if (response.error) {
                        console.log(response.error);
                    } else {

                        var download_label = '<a href="' + response.label_url + '" style="display:block">Download label</a>';
                        btn.parent().html(download_label);
                        $('.generate_label_loader').hide();
                        btn.remove();

                        var data = {
                            action: 'wc_shipment_dhl_tracking_id',
                            order_id: post_id,
                        };

                        $.post(admin_url, data, function(response) {

                            var res = JSON.parse(response);
                            var tracking_number = res.tracking_number;
                            var security = res.security_hash;

                            var d = new Date();
                            var month = d.getMonth() + 1;
                            var day = d.getDate();
                            var curdate = d.getFullYear() + '-' + (('' + month).length < 2 ? '0' : '') + month + '-' + (('' + day).length < 2 ? '0' : '') + day;

                            var data = {
                                action: 'wc_shipment_tracking_save_form',
                                order_id: post_id,
                                tracking_provider: '',
                                custom_tracking_provider: 'DHL',
                                custom_tracking_link: 'https://www.dhl.de/de/privatkunden/pakete-empfangen/verfolgen.html?piececode=' + tracking_number,
                                tracking_number: tracking_number,
                                date_shipped: curdate,
                                security: security
                            };

                            $.post(admin_url, data, function(response) {

                                if (response != '-1') {

                                    console.log(response);

                                }

                            });

                        });

                    }

                });

            });

        });
    </script>

    <?php
}


add_action('wp_ajax_wc_shipment_dhl_tracking_id', 'get_shipment_dhl_tracking_id');
function get_shipment_dhl_tracking_id()
{
    $post_id = $_POST['order_id'];
    $tracking_details = get_post_meta($post_id, '_pr_shipment_dhl_label_tracking', true);
    $tracking_number = $tracking_details['tracking_number'];
    $security = wp_create_nonce('create-tracking-item');

    $details = array();
    $details['tracking_number'] = $tracking_number;
    $details['security_hash'] = $security;

    echo json_encode($details);

    wp_die();
}

/*Show Warning to admin if previous order of customer is pending*/

function pending_customer_order($column)
{

    global $post;

    if ('5c52af1c2c9f8' === $column) {

        $post_id = $post->ID;
        $order = wc_get_order($post_id);
        $email = $order->get_billing_email();

        if ($email != 'brieftaubenshop@t-online.de') {

            $query = new WC_Order_Query();
            $query->set('customer', $email);
            $orders = $query->get_orders();
            $pending = 0;
            $values = '';
            $flag = 0;

            foreach ($orders as $order) {
                $order_id = $order->get_id();
                $payment_date = get_post_meta($order_id, 'payment_date', true);
                if (empty($payment_date) && $order_id != $post_id) :
                    $pending++;
                    $order_no = get_post_meta($order_id, '_alg_wc_custom_order_number', true);
                    if ($flag != 0) {
                        $values .= ',';
                    }
                    $values .= '<a href="https://www.brieftaubenshop.de/wp-admin/post.php?post=' . $order_id . '&action=edit"><u>' . $order_no . '</u></a>';
                    $flag++;
                endif;
            }

            if ($pending > 0) :
                echo "<div style='border: 2px solid red; padding:5px; line-height:1.5em'>";
                echo "<b>Order_No:</b> " . $values;
                echo "</div>";
            endif;
        }
    }
}

// add_action('manage_shop_order_posts_custom_column', 'pending_customer_order');

function pending_previous_payment()
{

    global $pagenow;

    if ($pagenow == 'post.php' && !empty($_GET['post']) && $_GET['action'] == 'edit') :

        $post_id = $_GET['post'];
        if (get_post_type($post_id) == 'shop_order') {

            $order = wc_get_order($post_id);
            $email = $order->billing_email;
            $pending = 0;

            if ($email != 'brieftaubenshop@t-online.de') {

                $query = new WC_Order_Query();
                $query->set('customer', $email);
                $orders = $query->get_orders();

                foreach ($orders as $order) {
                    $order_id = $order->id;
                    $payment_date = get_post_meta($order_id, 'payment_date', true);
                    if (empty($payment_date) && $order_id != $post_id) :
                        $pending++;
                    endif;
                }
            }

            if ($pending > 0) :
                ?>

                <div class="notice notice-success is-dismissible" style="border: 3px solid red; border-radius: 5px;">
                    <p style="font-size:15px; font-weight:600;">Previous order payment still pending</p>
                </div>

                <?php
            endif;
        }

    endif;
}

add_action('admin_notices', 'pending_previous_payment');

add_action('woocommerce_order_status_processing', 'action_woocommerce_order_status_processing', 10, 1);
function action_woocommerce_order_status_processing($order_id)
{

    $pdf = new YITH_WooCommerce_Pdf_Invoice();
    $pdf->create_document($order_id, 'invoice');
}

add_action('woocommerce_order_status_pending', 'action_woocommerce_order_status_pending', 10, 1);
function action_woocommerce_order_status_pending($order_id)
{

    $pdf = new YITH_WooCommerce_Pdf_Invoice();
    $pdf->create_document($order_id, 'invoice');
}

add_filter('use_block_editor_for_post', '__return_false');

add_shortcode('test-test', 'test_function');
function test_function()
{

    $ordernums = [30980, 30976, 30965, 30961, 30957, 30956, 30953, 30949];

    foreach ($ordernums as $ordernum) {

        $order = wc_get_order($ordernum);
        $order_id = $order->get_id();

        $number = get_post_meta($order_id, '_alg_wc_custom_order_number', true);
        $format = '/' . $number . '/';

        update_post_meta($order_id, '_ywpi_invoice_number', $number);
        update_post_meta($order_id, '_ywpi_invoice_formatted_number', $format);

        $pdf = new YITH_WooCommerce_Pdf_Invoice();
        $pdf->regenerate_document($order_id, 'invoice');
    }
}

function filter_plugin_updates($value)
{
    unset($value->response['yith-woocommerce-pdf-invoice-premium-old/init.php']);
    return $value;
}
add_filter('site_transient_update_plugins', 'filter_plugin_updates');


/* Send mail when no shipping method applied*/
// add_filter('woocommerce_no_shipping_available_html', 'change_noship_message', 999, 1);
function change_noship_message($message)
{

    global $woocommerce;
    $items = $woocommerce->cart->get_cart();
    $country_code = $woocommerce->customer->get_shipping_country();
    $country_name = WC()->countries->countries[$country_code];
    $cart = WC()->cart->get_cart();
    $msg = array();
    $msg['country_code'] = $country_code;
    $msg['country_name'] = $country_name;
    $msg['cart'] = $cart;
    $msg = json_encode($msg);
    wp_mail('ritesh@appycodes.com', 'Shipping Method Unavailable', $msg);

    return $message;
}

// add_shortcode('change_price_of_product', 'change_price_of_product_fn');
function change_price_of_product_fn()
{

    global $wpdb;

    $query = "SELECT * FROM " . $wpdb->prefix . "posts WHERE post_type='product' ";

    $results = $wpdb->get_results($query);

    foreach ($results as $product) {
        //if($product->ID == '1502'):

        $regular_price = get_post_meta($product->ID, '_regular_price', true);

        if ($regular_price > 0) {
            $price = 0;

            $taxable = get_post_meta($product->ID, '_tax_status', true);

            if (!empty($taxable) && $taxable == 'taxable') {
                $tax_class = get_post_meta($product->ID, '_tax_class', true);

                if (!empty($tax_class)) {
                    if ($tax_class == 'reduced-rate') {
                        $old_tax_rate = 5;
                        $new_tax_rate = 7;
                    } else {
                        continue;
                    }
                } else {
                    $old_tax_rate = 16;
                    $new_tax_rate = 19;
                }

                $base_price = ($regular_price * 100) / (100 + $old_tax_rate);
                $price_excl_tax = round($base_price, 2);

                $new_tax_amount = ($price_excl_tax * $new_tax_rate) / 100;
                $new_tax_amount = round($new_tax_amount, 2);

                $regular_price_incl_tax = number_format($price_excl_tax + $new_tax_amount, 2);
                $price = $regular_price_incl_tax;

                update_post_meta($product->ID, '_regular_price', $regular_price_incl_tax);

                $sale_price = get_post_meta($product->ID, '_sale_price', true);

                if (!empty($sale_price) && $sale_price > 0) {
                    $base_price = ($sale_price * 100) / (100 + $old_tax_rate);
                    $price_excl_tax = round($base_price, 2);

                    $new_tax_amount = ($price_excl_tax * $new_tax_rate) / 100;
                    $new_tax_amount = round($new_tax_amount, 2);

                    $sale_price_incl_tax = number_format($price_excl_tax + $new_tax_amount, 2);

                    $price = $sale_price_incl_tax;

                    update_post_meta($product->ID, '_sale_price', $sale_price_incl_tax);
                }

                update_post_meta($product->ID, '_price', $price);

                $unit_price_auto = get_post_meta($product->ID, '_unit_price_auto', true);

                if (!empty($unit_price_auto) && $unit_price_auto == 'yes') {

                    $unit_product = get_post_meta($product->ID, '_unit_product', true);

                    $unit_base = get_post_meta($product->ID, '_unit_base', true);

                    $unit_price = 0;

                    if (!empty($unit_product) && !empty($unit_base)) {
                        $one_unit = $regular_price_incl_tax / $unit_product;

                        $unit_price_regular =  round($one_unit * $unit_base, 2);

                        $unit_price = $unit_price_regular;

                        update_post_meta($product->ID, '_unit_price_regular', $unit_price_regular);

                        if (!empty($sale_price) && $sale_price > 0) {
                            $one_unit = $sale_price_incl_tax / $unit_product;

                            $unit_price_sale =  round($one_unit * $unit_base, 2);

                            update_post_meta($product->ID, '_unit_price_sale', $unit_price_sale);

                            $unit_price = $unit_price_sale;
                        }

                        update_post_meta($product->ID, '_unit_price', $unit_price);
                    }
                }
            }
        }

        //endif;

    }
}

add_shortcode('footer_newsletter', 'footer_newsletter');
function footer_newsletter(){
	ob_start();
	?>
	<div class="footer-newsletter">
		<div class="vc_row wpb_row vc_row-fluid">
			<div class="wpb_column vc_column_container vc_col-sm-12">
				[mc4wp_form id="3032"]
			</div>
		</div>
		<div class="vc_row wpb_row vc_row-fluid">
			<div class="wpb_column vc_column_container vc_col-sm-3">
				<figure class="wpb_wrapper vc_figure">
					<a href="https://www.haendlerbund.de/de/mitglied/13C8186CB65" target="_blank" class="vc_single_image-wrapper   vc_box_border_grey"><img class="vc_single_image-img" src=""></a>
				</figure>
			</div>
			<div class="wpb_column vc_column_container vc_col-sm-5">
				<figure class="wpb_wrapper vc_figure">
					<a href="https://www.haendlerbund.de/de/mitglied/bbf5bf0d-c253-11e4-bc3a-14dae9b38da3-6863442777" target="_blank" class="vc_single_image-wrapper   vc_box_border_grey"><img width="200" height="122" src="https://i1.wp.com/www.brieftaubenshop.de/wp-content/uploads/2019/03/hbLogo-e1561830682733.png?fit=200%2C122&amp;ssl=1" class="vc_single_image-img attachment-full" alt="" loading="lazy"></a>
				</figure>
			</div>
			<div class="wpb_column vc_column_container vc_col-sm-4">
				<figure class="wpb_wrapper vc_figure">
					<div class="vc_single_image-wrapper   vc_box_border_grey"><img class="vc_single_image-img" src=""></div>
				</figure>
			</div>
		</div>
		<div class="vc_row wpb_row vc_row-fluid">
			<div class="wpb_column vc_column_container vc_col-sm-6">
				<figure class="wpb_wrapper vc_figure">
					<a href="https://www.kaeufersiegel.de/zertifikat/?uuid=dd7f4988-17cc-11e8-bcf5-9c5c8e4fb375-2414081313" target="_blank" class="vc_single_image-wrapper   vc_box_border_grey"><img class="vc_single_image-img" src="https://www.kaeufersiegel.de/zertifikat/logo.php?uuid=dd7f4988-17cc-11e8-bcf5-9c5c8e4fb375-2414081313&amp;size=150"></a>
				</figure>
			</div>
		</div>
	</div>
	<?php
	return do_shortcode(ob_get_clean());
}



//add_filter( 'woocommerce_package_rates', 'custom_shipping_costs', 10, 2 );
function custom_shipping_costs( $rates, $package ) {
    
    global $woocommerce;
    $shipping_country = $woocommerce->customer->get_shipping_country();
    
    $eu_countries = array('AT','BE','BG','CY','CZ','DK','EE','ES','FI','FR','GB','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PL','PT','RO','SE','SI','SK','IM','MC');

    if(in_array($shipping_country , $eu_countries)) {
        foreach( $rates as $rate_key => $rate ){
            if( $rate->method_id != 'free_shipping'){
                // Set new cost
                $shipping_cost = $rates[$rate_key]->cost;
                $rates[$rate_key]->cost = $shipping_cost * 1.17;
            }
        }
    }
    return $rates;
}



//add_action( 'admin_init', 'test_order_update_order1' );
function test_order_update_order1() {
    $order_id   = 101350; // This needs to be a real order or there will be errors
    $order      = new WC_Order( $order_id );
    
    $order->calculate_taxes();
    $order->calculate_totals();
    $order->save();
}



//add_action( 'admin_init', 'test_order_update_order' );
function test_order_update_order() {
    
        $order_ids=[102454];
        foreach($order_ids as $val){
        $order_id=$val;
    	
    	$order = wc_get_order($order_id);
        $order_id = $order->get_id();
       
        global $woocommerce;
        $total = 0;
        $total_tax = 0;
       
        $i = 0;
        $rateid = array();
        $ratepercent = array();
        $taxarray = array();
        foreach ($order->get_items(array('tax')) as $item_key => $item_values){
             $rateid[$i]=$item_values->get_rate_id();
             $ratepercent[$i] = $item_values['rate_percent'];
             $item_id = $item_values->get_id();
             $label=$item_values['label'];
             wc_update_order_item_meta($item_id,'label',$label);
             $i++;
        }
        
        
    //     $i = 0;
    //     foreach ($order->get_items(array('line_item')) as $item_key => $item_values){
	   //      $item_id = $item_values->get_id();
    // 		 $item_data = $item_values->get_data();
    // 		 $price=$item_data['total'];
    // 		 $item_tax = $item_data['total_tax'];
    //          $price = $price + $item_tax;
    //          $tax_rate = $ratepercent[$i];
    // 		 $base_price = ($price * 100) / (100 + $tax_rate);
    // 		 $tax = ($base_price * $tax_rate)/100;
    // 		 $tax = round($tax,2);
    // 		 $taxarray[$i] = $tax;
    // 		 $original_price=$base_price;
    		 
    // 		 $total = $total + $price;
    		 
    // 		 $tax_data=(string)$tax;
    // 		 $tax_data_length=strlen((string)$tax);
    
    // 		 $tax_data='a:2:{s:5:"total";a:1:{i:'.$rateid[$i].';s:'.$tax_data_length.':"'.$tax_data.'";}s:8:"subtotal";a:1:{i:'.$rateid[$i].';s:'.$tax_data_length.':"'.$tax_data.'";}}';
    // 		 wc_update_order_item_meta($item_id,'_line_total',$original_price);
    // 		 wc_update_order_item_meta($item_id,'_line_subtotal',$original_price);
    // 		 wc_update_order_item_meta($item_id,'_line_subtotal_tax',$tax);
    // 		 wc_update_order_item_meta($item_id,'_line_tax',$tax);
    // 		 wc_update_order_item_meta($item_id,'_line_tax_data',$tax_data);
	       
	   //     $i++;
    //     }
        
        
        
    //     $i=0;
    //     foreach ($order->get_items(array('tax')) as $item_key => $item_values){
    //          $item_id = $item_values->get_id();
    //          wc_update_order_item_meta($item_id,'tax_amount',$taxarray[$i]);
    //          $i++;
    //     }
        
        
        
        foreach( $order->get_items( 'shipping' ) as $item_id => $item ){
            
            $item_id = $item->get_id();
            
            $shipping_method_total = 9.04;
            
            foreach($item['taxes']['total'] as $key => $value){
                $rate_id = $key;
            }
            
            foreach ($order->get_items(array('tax')) as $item_key => $item_values){
                if($item_values['rate_id'] == $rate_id){
                    $shipping_tax_rate = $item_values['rate_percent'];
                    $base_price = ($shipping_method_total * 100) / (100 + $shipping_tax_rate);
                    $base_price = round($base_price ,2);
    		        $shipping_tax = ($base_price * $shipping_tax_rate)/100;
    		        $shipping_tax = round($shipping_tax,2);
                    
                    $tax_data=(string)$shipping_tax;
    		        $tax_data_length=strlen((string)$shipping_tax);
    
    		        $tax_data='a:1:{s:5:"total";a:1:{i:'.$rate_id.';s:'.$tax_data_length.':"'.$tax_data.'";}}';
                }
                
            }
           
            wc_update_order_item_meta($item_id,'cost',$base_price);
            wc_update_order_item_meta($item_id,'total_tax',$shipping_tax);
            wc_update_order_item_meta($item_id,'taxes',$tax_data);
            
            $total = $total + $shipping_method_total;
            
        }
        
        // update_post_meta($order_id,'_order_total',$total);
        // update_post_meta($order_id,'_order_tax',$tax);
        update_post_meta($order_id,'_order_shipping',$base_price);
        update_post_meta($order_id,'_order_shipping_tax',$shipping_tax);
        
        
        $pdf=new YITH_WooCommerce_Pdf_Invoice();
        $pdf->regenerate_document( $order_id, 'invoice');
    }      
}



