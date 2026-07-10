<?php

die();

// Load WordPress environment
require_once( dirname(__FILE__) . '/wp-load.php' );

// ====== CONFIG ======
$secret_key = 'D982DSHJ239823'; // Set your secret key
$product_id_to_block = [44660,165266];
$send_report_to = 'iitb.riteshag.com';
// =====================

if (!isset($_GET['key']) || $_GET['key'] !== $secret_key) {
    wp_die('❌ Invalid access key.');
}


$today = date('Y-m-d');
$args = [
    'limit'        => -1,
    'status'       => ['any'],
    'date_created' => $today,
    'meta_key'     => '_created_via',
    'meta_value'   => 'store-api',
    'return'       => 'objects',
];

$orders = wc_get_orders($args);

if (empty($orders)) {
    echo "✅ No store-api orders today.";
    exit;
}

$deleted = [];
foreach ($orders as $order) {
    $order_id = $order->get_id();
    $items = $order->get_items();
    $email = $order->get_billing_email();

    // Check if it only contains the spam product
    if (count($items) === 1) {
        $item = reset($items);
        if ( in_array($item->get_product_id(), $product_id_to_block) ) {
            wp_delete_post($order_id, true);
            error_log("🗑️ Deleted spam order ID: $order_id");
            $deleted[] = "Order ID: $order_id | Email: $email";
        }
    }
}

if (empty($deleted)) {
    echo "✅ No matching orders to delete.";
    exit;
}

// Send email summary
$subject = "🚨 Deleted Store-API Spam Orders on " . date('Y-m-d');
$body = "The following spam orders were deleted:\n\n" . implode("\n", $deleted);
$headers = ['Content-Type: text/plain; charset=UTF-8'];

wp_mail($send_report_to, $subject, $body, $headers);

echo "🧹 Deleted " . count($deleted) . " spam orders. Email sent to {$send_report_to}.";
