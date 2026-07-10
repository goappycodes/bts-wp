<?php
// Load WordPress environment
require_once( dirname(__FILE__) . '/wp-load.php' );

global $wpdb;

// Settings
$allowed_author_id = 19450;
$admin_email       = 'goappycodes@gmail.com';

// Fetch all active posts including translated ones (exclude trash/drafts)
$posts = $wpdb->get_results("
    SELECT p.ID, p.post_author, p.post_title, p.post_content
    FROM {$wpdb->posts} p
    INNER JOIN {$wpdb->prefix}icl_translations t ON p.ID = t.element_id
    WHERE p.post_type = 'post'
      AND p.post_status NOT IN ('trash', 'auto-draft', 'inherit', 'revision')
");

// Prepare tracking
$deleted_posts = [];

foreach ($posts as $post) {
    $should_delete = false;

    // Condition 1: Not written by allowed author
    if ((int)$post->post_author !== $allowed_author_id) {
        $should_delete = true;
    }

    // Condition 2: Contains forbidden keyword (e.g. casino)
    if (preg_match('/\bcasino\b/i', $post->post_title) || preg_match('/\bcasino\b/i', $post->post_content)) {
        $should_delete = true;
    }

    if ($should_delete) {
        $post_id   = $post->ID;
        $post_lang = apply_filters('wpml_element_language_code', null, [
            'element_id'   => $post_id,
            'element_type' => 'post_post',
        ]);
        $post_url  = get_permalink($post_id);

        wp_trash_post($post_id); // move to trash

        $deleted_posts[] = "Post ID: $post_id | Title: {$post->post_title} | Lang: $post_lang | Author: {$post->post_author} | URL: $post_url";
    }
}

// Send email report
if (!empty($deleted_posts)) {
    $subject = '[Security Alert] Unauthorized or Suspicious Blog Posts Deleted';
    $body    = "The following blog posts were automatically deleted because they either:\n"
             . "- Were not authored by user ID $allowed_author_id, OR\n"
             . "- Contained the keyword 'casino' in title or content.\n\n"
             . implode("\n", $deleted_posts)
             . "\n\nTime: " . date('Y-m-d H:i:s');

    $headers = ['From: Blog Cleaner <no-reply@brieftaubenshop.de>'];

    wp_mail($admin_email, $subject, $body, $headers);

    // Optional: also log locally for audit
    $log_file = WP_CONTENT_DIR . '/uploads/cleanblogs-log.txt';
    file_put_contents($log_file, $body . "\n\n", FILE_APPEND);

    echo "Deleted " . count($deleted_posts) . " unauthorized or suspicious posts. Email sent to $admin_email.";
} else {
    echo "No unauthorized or suspicious posts found.";
}
?>
