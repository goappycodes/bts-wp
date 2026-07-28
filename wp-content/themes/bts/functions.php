<?php
/**
 * bts Theme functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package bts
 * @since 1.0.0
 */

/**
 * Define Constants
 */
//define( 'CHILD_THEME_BTS_VERSION', '1.0.0' );
define( 'CHILD_THEME_BTS_VERSION', time() );

/**
 * Enqueue styles
 */
function child_enqueue_styles() {

	wp_enqueue_style( 'bts-theme-css', get_stylesheet_directory_uri() . '/style.css', array('astra-theme-css'), CHILD_THEME_BTS_VERSION, 'all' );
	
    
    // 	Global Style
    wp_enqueue_style( 'global', get_stylesheet_directory_uri() . '/assets/css/global.css', array(), CHILD_THEME_BTS_VERSION );
    
    // 	Header Style
    wp_enqueue_style( 'header', get_stylesheet_directory_uri() . '/assets/css/header.css', array(), CHILD_THEME_BTS_VERSION );
    
    // 	Footer Style
    wp_enqueue_style( 'footer', get_stylesheet_directory_uri() . '/assets/css/footer.css', array(), CHILD_THEME_BTS_VERSION );
    
    // 	Footer Style
    wp_enqueue_style( 'about-us', get_stylesheet_directory_uri() . '/assets/css/about-us.css', array(), CHILD_THEME_BTS_VERSION );
    
    if(is_page('contact') || is_page('kontakt')){
        wp_enqueue_style( 'contact-us', get_stylesheet_directory_uri() . '/assets/css/contact.css', array(), CHILD_THEME_BTS_VERSION );
    }


    if( is_search() ) {
        
        // 	Homepage Style
        wp_enqueue_style( 'search', get_stylesheet_directory_uri() . '/assets/css/search.css', array(), CHILD_THEME_BTS_VERSION );
    }
    
    
    if( is_front_page() ) {
        
        // 	Homepage Style
        wp_enqueue_style( 'home', get_stylesheet_directory_uri() . '/assets/css/home.css', array(), CHILD_THEME_BTS_VERSION );
        
        // Home Jquery
        wp_enqueue_script('home', get_stylesheet_directory_uri() . '/assets/js/home.js', array('jquery'), time());
    }
    
    if ( is_product_category() || is_shop() ) {
        // 	Archive Product
        wp_enqueue_style( 'archive-product', get_stylesheet_directory_uri() . '/assets/css/archive-product.css', array(), CHILD_THEME_BTS_VERSION );
        wp_enqueue_script('archive-product', get_stylesheet_directory_uri() . '/assets/js/archive-product.js', array('jquery'), time());
    }
    
    if ( is_account_page() ) {
        // 	My Account Page
        wp_enqueue_style( 'my-account', get_stylesheet_directory_uri() . '/assets/css/my-account.css', array(), CHILD_THEME_BTS_VERSION );
    }
    
    if ( is_product() ) {
        // 	Single Product Page
        wp_enqueue_style( 'single-product', get_stylesheet_directory_uri() . '/assets/css/single-product.css', array(), CHILD_THEME_BTS_VERSION );
    }
    
    if ( is_cart() ) {
        // 	Cart Page
        wp_enqueue_style( 'cart', get_stylesheet_directory_uri() . '/assets/css/cart.css', array(), CHILD_THEME_BTS_VERSION );
        wp_enqueue_script('cart', get_stylesheet_directory_uri() . '/assets/js/cart.js', array('jquery'), time());
    }
    
    if ( is_checkout() ) {
        // 	Cart Page
        wp_enqueue_style( 'checkout', get_stylesheet_directory_uri() . '/assets/css/checkout.css', array(), CHILD_THEME_BTS_VERSION );
        wp_enqueue_script('checkout', get_stylesheet_directory_uri() . '/assets/js/checkout.js', array('jquery'), time());
        
        wp_dequeue_style( 'select2' );
        wp_deregister_style( 'select2' );

        wp_dequeue_script( 'selectWoo');
        wp_deregister_script('selectWoo');
        
    }

}

add_action( 'wp_enqueue_scripts', 'child_enqueue_styles', 15 );

add_filter('use_block_editor_for_post', '__return_false', 10);


require_once('legacy_functions.php');
require_once('dynamic-price.php');
require_once('category-seo.php');


function example_theme_support() {
    remove_theme_support( 'widgets-block-editor' );
}
add_action( 'after_setup_theme', 'example_theme_support' );



// Cart Page Hooks
add_action( 'astra_entry_before', 'cart_page_logo_fn' );

function cart_page_logo_fn() {
    if ( is_cart() || is_checkout() ){ 
        
        $custom_logo_id = get_theme_mod( 'custom_logo' );
        $logo = wp_get_attachment_image_src( $custom_logo_id , 'full' );
        
    ?>

    <div class="cart-page-logo">
        <a href="/"><img src="<?php echo esc_url( $logo[0] ); ?>"></a>
    </div>

<?php }
}


add_action('wp_head', 'head_preload');

function head_preload(){
	if( is_front_page() ) {
		?>	
		<link rel="preload" href="<?php echo  get_field('banner_image'); ?>" as="image" />
		<style>
		    header .custom-logo-link img { max-height: 99px; }
            .whatsapp-link img {
            	height: 40px!important;
            	object-fit: contain;
            }		    
		</style>
		<?php
	}
}


add_action( 'woocommerce_before_add_to_cart_button', 'bts_add_custom_option');
add_filter( 'woocommerce_get_item_data', 'bts_get_trophy_engraved_message_item_data', 10, 2 );
add_action( 'woocommerce_checkout_create_order_line_item', 'bts_add_trophy_engraved_message_to_order_line_item', 10, 3 );
add_filter( 'woocommerce_is_purchasable', 'disable_purchasable_on_product_category_archives_for_trophy', 10, 2 );
add_filter( 'woocommerce_add_cart_item_data', 'bts_add_trophy_engraved_message_to_cart', 10, 3 );


function bts_add_custom_option(){

	if (!get_field('enable_trophy')){
		return ;
	}
	
	$maxlength = get_field('max_engraving_length') ? get_field('max_engraving_length') : "250";
	
	$value = filter_input( INPUT_POST, 'trophy_engraved_message' ); ?>
	<script>
		window.onload = (event) => {
			let engravingTextArea = document.getElementById("engraving-textbox");
			let characterCounter = document.getElementById("char_count");
			const maxNumOfChars = <?php echo $maxlength; ?>;		
			const countCharacters = () => {
				let numOfEnteredChars = engravingTextArea.value.length;
				let counter = maxNumOfChars - numOfEnteredChars;
				characterCounter.textContent = counter + "/<?php echo $maxlength; ?>";
			};		
			
			engravingTextArea.addEventListener("input", countCharacters);
			engravingTextArea.addEventListener("paste", countCharacters);
			engravingTextArea.addEventListener("keyup", countCharacters);
			
		};
	</script>
	<p class="trophy-message">
		<label for="trophy_engraved_message"><?php _e( 'Trophy Engraved Message:', 'bts' ); ?> </label>
		<textarea id="engraving-textbox" rows="6" maxlength="<?php echo $maxlength; ?>" required type="text" id="trophy_engraved_message" name="trophy_engraved_message" placeholder="<?php _e( 'Nutzen Sie dieses Feld, um Ihre Pokalplakette individuell zu beschriften.', 'bts' ); ?>" value="<?php $value ?>"></textarea>
		<small id="char_count"><?php echo $maxlength; ?>/<?php echo $maxlength; ?> zeichen übrig</small>

	</p>
	<?php
}

function bts_add_trophy_engraved_message_to_cart( $cart_item_data, $product_id, $variation_id ) {
	
	$trophy_engraved_message = filter_input( INPUT_POST, 'trophy_engraved_message' );

	if ( empty( $trophy_engraved_message ) ) {
		return $cart_item_data;
	}

	$cart_item_data['trophy_engraved_message'] = $trophy_engraved_message;

	return $cart_item_data;
}

function bts_get_trophy_engraved_message_item_data( $item_data, $cart_item ) {

	if ( isset( $cart_item['trophy_engraved_message'] ) ){
		$item_data[] = array(
			'key' => __( 'Engraving', 'bts' ),
			'display' => wpautop($cart_item['trophy_engraved_message'])
		);
	}

	return $item_data;

}

function bts_add_trophy_engraved_message_to_order_line_item( $order_item, $cart_item_key, $cart_item_values ) {

	if ( ! empty( $cart_item_values['trophy_engraved_message'] ) ) {
		$order_item->add_meta_data( __( 'Engraving', 'bts' ), $cart_item_values [ 'trophy_engraved_message' ]);
	}

}


function disable_purchasable_on_product_category_archives_for_trophy( $purchasable, $product ) {
	if ( get_field('enable_trophy') && is_product_category() ){
		return false;
	}
	
    return $purchasable;
}


add_action( 'woocommerce_checkout_create_order', 'append_hausno_checkout_field_value', 10, 2 );
function append_hausno_checkout_field_value( $order, $data ){

    if( isset( $data['billing_house_number'] ) && ! empty( $data['billing_house_number'] ) ) 
        $order->set_billing_address_1( sanitize_text_field( $data['billing_address_1'] ).' '.sanitize_text_field( $data['billing_house_number'] ) );

    if( isset( $data['shipping_house_number'] ) && ! empty( $data['shipping_house_number'] ) ) 
        $order->set_shipping_address_1( sanitize_text_field( $data['shipping_address_1'] ).' '.sanitize_text_field( $data['shipping_house_number'] ) );
        
}

add_filter( 'woocommerce_paypal_payments_sync_wc_shipment_tracking', '__return_false');

add_filter( 'woocommerce_rest_prepare_shop_order_object', 'woocommerce_rest_prepare_shop_order_object_remove_fee', 99, 3);
function woocommerce_rest_prepare_shop_order_object_remove_fee($response, $object, $request){

    if ('shop_order' == $object->get_type()){
        $data = $response->data;
        $fee_lines = $data['fee_lines'];
        foreach ($fee_lines as $key => $fee_line) {
            if ( stripos($fee_line, "voucher") !== false || stripos($fee_line, "Wertgutschein") !== false ) {
                unset($fee_lines[$key]);
            }
        }
        $data['fee_lines'] = $fee_lines;
        $response->data = $data;
    }

    return $response;
}

add_filter('wpml_tm_job_field_is_translatable', 'do_not_translate_title', 10, 2);

function do_not_translate_title($is_translatable, $job_translate){
    global $wpdb;
    $postid = $wpdb->get_var( $wpdb->prepare("SELECT `field_data` FROM `wp_icl_translate` WHERE `field_type` = 'original_id' AND `job_id` = %d", $job_translate["job_id"] ));
    $posttype = get_post_type($postid);
    if ($job_translate["field_type"] == "title" && $posttype == "product") {
        return false;
    } else {
        return true;
    }
}

add_action('wp_head', 'insert_language_strings', 1);
function insert_language_strings(){
    ?>
    <script>
        var read_more_text = "<?php echo __('Read More'); ?>";
    </script>
    <?php
}

add_filter('mod_rewrite_rules', 'fix_rewritebase');
function fix_rewritebase($rules){
    $home_root = parse_url(home_url());
    if ( isset( $home_root['path'] ) ) {
        $home_root = trailingslashit($home_root['path']);
    } else {
        $home_root = '/';
    }
  
    $wpml_root = parse_url(get_option('home'));
    if ( isset( $wpml_root['path'] ) ) {
        $wpml_root = trailingslashit($wpml_root['path']);
    } else {
        $wpml_root = '/';
    }
  
    $rules = str_replace("RewriteBase $home_root", "RewriteBase $wpml_root", $rules);
    $rules = str_replace("RewriteRule . $home_root", "RewriteRule . $wpml_root", $rules);
  
    return $rules;
}


add_action('woocommerce_checkout_process', 'block_suspicious_gmail_orders');
function block_suspicious_gmail_orders() {
    if (!isset($_POST['billing_email'])) return;
    $email = sanitize_email($_POST['billing_email']);
    if (is_spam_email($email)) {
        wc_add_notice(__('Suspicious email. Please contact support.'), 'error');
    }
}

// Trash API-created orders after insert
// add_action('woocommerce_new_order', 'block_spam_orders_any_source', 10, 2);
function block_spam_orders_any_source($order_id, $order) {
    $email = $order->get_billing_email();
    if (is_spam_email($email)) {
        wp_trash_post($order_id);
    }
}

add_filter('woocommerce_store_api_checkout_order_pre_create_order', 'block_spammy_store_api_orders', 10, 2);
function block_spammy_store_api_orders($prepared_order, $request) {
    $email = isset($prepared_order['billing_email']) ? sanitize_email($prepared_order['billing_email']) : '';
    if (is_spam_email($email)) {
        throw new \WC_REST_Exception('woocommerce_rest_invalid_email', __('Sorry, we could not process your order. Please contact support.'), 403);
    }
    return $prepared_order;
}

function is_spam_email($email){
    return preg_match('/^[a-z]+[a-z]+\.\d{6}@gmail\.com$/i', $email);
}

add_filter('woocommerce_rest_check_permissions', 'block_order_creation_via_api', 10, 4);
function block_order_creation_via_api($permission, $context, $object_id, $post_type) {
    // Deny all create requests for order objects
    if ($post_type === 'shop_order' && $context === 'create') {
        return false;
    }

    return $permission;
}

add_filter('woocommerce_rest_api_get_routes', 'disable_order_creation_endpoints');
function disable_order_creation_endpoints($routes) {
    unset($routes['/wc/v3/orders']);
    unset($routes['/wc/v2/orders']);
    return $routes;
}

// add_filter('woocommerce_rest_pre_insert_shop_order_object', 'block_all_api_order_creation', 10, 2);
function block_all_api_order_creation($order, $request) {
    // Detect REST API context
    if (defined('REST_REQUEST') && REST_REQUEST) {
        // Optional: Check user agent or request headers if needed
        throw new \WC_REST_Exception('woocommerce_rest_order_creation_blocked', 'Order creation via API is not allowed.', 403);
    }

    return $order;
}

// add_action('woocommerce_new_order', 'trash_order_if_rest', 5, 2);
function trash_order_if_rest($order_id, $order) {
    if (defined('REST_REQUEST') && REST_REQUEST) {
        wp_trash_post($order_id);
    }
}


add_filter('woocommerce_store_api_checkout_order_pre_create_order', 'block_store_api_single_product_spam', 10, 2);
function block_store_api_single_product_spam($prepared_order, $request) {
    if (isset($prepared_order['line_items']) && count($prepared_order['line_items']) === 1) {
        $item = $prepared_order['line_items'][0];
        if (isset($item['product_id']) && $item['product_id'] == 165266) {
            throw new \WC_REST_Exception('woocommerce_rest_blocked', 'We could not process your order. Please contact support.', 403);
        }
    }

    return $prepared_order;
}


add_action('woocommerce_checkout_create_order', 'block_single_product_spam_orders', 10, 2);
function block_single_product_spam_orders($order, $data) {
    $items = $order->get_items();
    $product_ids = [];

    foreach ($items as $item) {
        $product_ids[] = $item->get_product_id();
    }

    // If only one item and it's 165266, block it
    if (count($product_ids) === 1 && $product_ids[0] == 165266) {
        throw new \Exception('We could not process your order. Please contact support.');
    }
}

add_action('save_post_shop_order', 'block_spam_order_on_save', 10, 3);
function block_spam_order_on_save($post_id, $post, $update) {
    // Avoid infinite loop
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $order = wc_get_order($post_id);
    if (!$order) return;

    $items = $order->get_items();
    if (count($items) === 1) {
        $item = reset($items);
        if ($item->get_product_id() == 165266) {
            // Delete immediately
            wp_delete_post($post_id, true);
            error_log("Blocked spam order with product 165266: Order ID $post_id");
        }
    }
}

add_action('save_post', 'nuke_spam_order_from_any_source', 10, 3);
function nuke_spam_order_from_any_source($post_id, $post, $update) {
    // Run only for shop_order post type
    if ($post->post_type !== 'shop_order') return;

    // Get order object safely
    $order = wc_get_order($post_id);
    if (!$order) return;

    // Get all items and check if it's only product 165266
    $items = $order->get_items();
    if (count($items) === 1) {
        $item = reset($items);
        if ($item->get_product_id() == 165266) {
            // Delete the post immediately and log it
            wp_delete_post($post_id, true);
            error_log("🔥 Deleted spam order $post_id (Product 165266)");
        }
    }
}

add_filter('doing_it_wrong_trigger_error', '__return_false');

// add_filter( 'wpseo_taxonomy_meta_cache', '__return_false' );


// Force priceCurrency into Yoast's Offer schema
add_filter( 'wpseo_schema_offer', function( $data, $context ) {
    if ( isset( $data['@type'] ) && $data['@type'] === 'Offer' ) {
        
        // Default currency from WooCommerce
        if ( empty( $data['priceCurrency'] ) ) {
            $data['priceCurrency'] = get_woocommerce_currency();
        }

        // Also clean up priceSpecification structure if it's incorrectly nested
        if ( isset( $data['priceSpecification'] ) && is_array( $data['priceSpecification'] ) ) {
            // If priceSpecification has numeric keys like [0], normalize into a flat array
            $fixed_specs = [];
            foreach ( $data['priceSpecification'] as $spec ) {
                if ( is_array( $spec ) && ! empty( $spec['@type'] ) ) {
                    // Ensure each spec also has currency
                    if ( empty( $spec['priceCurrency'] ) ) {
                        $spec['priceCurrency'] = $data['priceCurrency'];
                    }
                    $fixed_specs[] = $spec;
                }
            }
            if ( ! empty( $fixed_specs ) ) {
                $data['priceSpecification'] = $fixed_specs;
            }
        }
    }
    return $data;
}, 10, 2 );
