<?php
/**
 * WooCommerce Order Number Integrity Check & Auto-Fix
 *
 * Built for the "Custom Order Numbers for WooCommerce" (WPFactory / Algoritmika)
 * plugin, where the next order number is stored in the wp_options table and can
 * go stale when Redis object cache serves an old value.
 *
 * What it does on every run:
 *  1. Loads all orders from the last 90 days and maps their custom order numbers.
 *  2. Verifies the plugin counter option is >= (highest used number + 1).
 *     If it is behind (stale cache scenario), it resets the counter to max + 1
 *     and flushes the object cache keys for that option.
 *  3. Scans orders created in the last 24 hours for duplicate custom order
 *     numbers (compared against the full 90-day window). The NEWER order in a
 *     duplicate pair gets re-numbered from the corrected counter. An order note
 *     is added so there is an audit trail.
 *  4. Emails a report to ONIC_ALERT_EMAIL only when a problem was found.
 *
 * Usage:
 *   CLI / cron (recommended):
 *     php /path/to/wordpress/order-number-integrity-check.php
 *   Browser:
 *     https://yoursite.com/order-number-integrity-check.php?key=SECRET
 *
 * Suggested crontab (every 15 minutes):
 *   [star]/15 * * * * php /var/www/html/order-number-integrity-check.php >> /var/log/order-number-check.log 2>&1
 *   (replace [star] with *)
 *
 * Place this file in the WordPress root directory (same folder as wp-load.php).
 */

// ---------------------------- CONFIG ----------------------------
define( 'ONIC_SECRET',        '2823ff8336773a0daa1a57704cdc102a' );
define( 'ONIC_ALERT_EMAIL',   'goappycodes@gmail.com' );
define( 'ONIC_LOOKBACK_DAYS', 90 );    // comparison window for duplicates + max number
define( 'ONIC_CHECK_HOURS',   24 );    // only orders in this window get auto-fixed
define( 'ONIC_AUTO_FIX',      true );  // false = report-only mode (no changes, still emails)
define( 'ONIC_ALWAYS_EMAIL',  false ); // true = also email when everything is healthy

// Plugin-specific keys (defaults are for WPFactory "Custom Order Numbers for WooCommerce").
// If you use SkyVerge "Sequential Order Numbers Pro" instead, set:
//   ONIC_FULL_META  = '_order_number_formatted'
//   ONIC_NUM_META   = '_order_number'
//   ONIC_COUNTER_OPT = '' (SkyVerge computes next number from max, no counter option)
define( 'ONIC_FULL_META',   '_alg_wc_full_custom_order_number' );
define( 'ONIC_NUM_META',    '_alg_wc_custom_order_number' );
define( 'ONIC_COUNTER_OPT', 'alg_wc_custom_order_numbers_counter' );
// -----------------------------------------------------------------

// Security gate for HTTP access. CLI runs without the key.
if ( php_sapi_name() !== 'cli' ) {
	if ( ! isset( $_GET['key'] ) || $_GET['key'] !== ONIC_SECRET ) {
		http_response_code( 403 );
		exit( 'Forbidden' );
	}
	header( 'Content-Type: text/plain; charset=utf-8' );
}

// Bootstrap WordPress. Script must live in the WP root.
require_once __DIR__ . '/wp-load.php';

if ( ! function_exists( 'wc_get_orders' ) ) {
	echo "WooCommerce is not active. Aborting.\n";
	exit( 1 );
}

// Prevent WooCommerce emails from firing on our meta updates.
if ( ! defined( 'DOING_CRON' ) ) {
	define( 'DOING_CRON', true );
}

$onic_log      = array();
$onic_problems = false;

function onic_log( $msg ) {
	global $onic_log;
	$line = '[' . current_time( 'Y-m-d H:i:s' ) . '] ' . $msg;
	$onic_log[] = $line;
	echo $line . "\n";
}

/**
 * Extract the numeric sequence part of a custom order number.
 * Prefers the plugin's numeric meta; falls back to the last digit run
 * in the formatted number (safe with date-based prefixes like "2026-").
 */
function onic_extract_numeric( $order ) {
	$numeric = $order->get_meta( ONIC_NUM_META );
	if ( '' !== $numeric && null !== $numeric && is_numeric( $numeric ) ) {
		return (int) $numeric;
	}
	$full = (string) $order->get_meta( ONIC_FULL_META );
	if ( '' === $full ) {
		return 0;
	}
	if ( preg_match( '/(\d+)(?!.*\d)/', $full, $m ) ) {
		return (int) $m[1];
	}
	return 0;
}

/**
 * Build a new formatted number by replacing the last digit run in the old
 * formatted number, preserving zero-padding width.
 */
function onic_build_full_number( $old_full, $new_numeric ) {
	if ( '' === $old_full ) {
		return (string) $new_numeric;
	}
	return preg_replace_callback(
		'/(\d+)(?!.*\d)/',
		function ( $m ) use ( $new_numeric ) {
			return str_pad( (string) $new_numeric, strlen( $m[1] ), '0', STR_PAD_LEFT );
		},
		$old_full,
		1
	);
}

/**
 * Read the counter option bypassing the object cache as much as possible,
 * so a stale Redis value does not fool the check itself.
 */
function onic_get_counter_from_db() {
	global $wpdb;
	if ( '' === ONIC_COUNTER_OPT ) {
		return null;
	}
	$val = $wpdb->get_var(
		$wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", ONIC_COUNTER_OPT )
	);
	return ( null === $val ) ? null : (int) $val;
}

/**
 * Write the counter directly to the DB and purge every cache layer that
 * could serve the old value (single option key + alloptions blob).
 */
function onic_set_counter( $value ) {
	global $wpdb;
	update_option( ONIC_COUNTER_OPT, $value );
	// Belt and braces: direct DB write in case update_option short-circuits
	// on a stale cached value that "equals" the new one.
	$wpdb->update(
		$wpdb->options,
		array( 'option_value' => $value ),
		array( 'option_name' => ONIC_COUNTER_OPT )
	);
	wp_cache_delete( ONIC_COUNTER_OPT, 'options' );
	wp_cache_delete( 'alloptions', 'options' );
	wp_cache_delete( 'notoptions', 'options' );
}

onic_log( 'Starting order number integrity check (lookback ' . ONIC_LOOKBACK_DAYS . 'd, fix window ' . ONIC_CHECK_HOURS . 'h).' );

// ------------------------------------------------------------------
// 1. Load last 90 days of orders and map their numbers.
// ------------------------------------------------------------------
$orders = wc_get_orders( array(
	'limit'        => -1,
	'type'         => 'shop_order',
	'status'       => array_keys( wc_get_order_statuses() ), // include failed/cancelled: they consumed numbers too
	'date_created' => '>=' . ( time() - ONIC_LOOKBACK_DAYS * DAY_IN_SECONDS ),
	'orderby'      => 'date',
	'order'        => 'ASC',
) );

if ( empty( $orders ) ) {
	onic_log( 'No orders found in the lookback window. Nothing to do.' );
	exit( 0 );
}

$number_map  = array(); // numeric => array of ['id' =>, 'ts' =>, 'full' =>]
$max_numeric = 0;

foreach ( $orders as $order ) {
	$numeric = onic_extract_numeric( $order );
	if ( $numeric <= 0 ) {
		continue; // order has no custom number assigned (yet)
	}
	$number_map[ $numeric ][] = array(
		'id'   => $order->get_id(),
		'ts'   => $order->get_date_created() ? $order->get_date_created()->getTimestamp() : 0,
		'full' => (string) $order->get_meta( ONIC_FULL_META ),
	);
	if ( $numeric > $max_numeric ) {
		$max_numeric = $numeric;
	}
}

onic_log( 'Scanned ' . count( $orders ) . ' orders. Highest custom order number in window: ' . $max_numeric . '.' );

// ------------------------------------------------------------------
// 2. Counter sanity check (this is where stale Redis bites).
// ------------------------------------------------------------------
$expected_next = $max_numeric + 1;

if ( '' !== ONIC_COUNTER_OPT ) {
	$counter_db    = onic_get_counter_from_db();
	$counter_cache = (int) get_option( ONIC_COUNTER_OPT, 0 );

	onic_log( 'Counter in DB: ' . var_export( $counter_db, true ) . ' | Counter via get_option (cache): ' . $counter_cache . ' | Expected next: >= ' . $expected_next . '.' );

	if ( null !== $counter_db && $counter_db !== $counter_cache ) {
		$onic_problems = true;
		onic_log( 'MISMATCH: object cache is serving a different counter than the database. This is the stale Redis scenario.' );
		if ( ONIC_AUTO_FIX ) {
			wp_cache_delete( ONIC_COUNTER_OPT, 'options' );
			wp_cache_delete( 'alloptions', 'options' );
			onic_log( 'FIXED: flushed object cache keys for the counter option.' );
		}
	}

	$effective_counter = ( null !== $counter_db ) ? max( $counter_db, $counter_cache ) : $counter_cache;

	if ( $effective_counter < $expected_next ) {
		$onic_problems = true;
		onic_log( 'PROBLEM: counter (' . $effective_counter . ') is behind the highest used number (' . $max_numeric . '). New orders would reuse numbers.' );
		if ( ONIC_AUTO_FIX ) {
			onic_set_counter( $expected_next );
			onic_log( 'FIXED: counter reset to ' . $expected_next . ' and caches purged.' );
		}
	} else {
		onic_log( 'Counter OK.' );
	}
}

// ------------------------------------------------------------------
// 3. Duplicate check for orders created in the last 24 hours.
// ------------------------------------------------------------------
$window_start = time() - ONIC_CHECK_HOURS * HOUR_IN_SECONDS;
$fixed        = array();
$dupes_found  = array();
$next_number  = max( $expected_next, ( '' !== ONIC_COUNTER_OPT ? (int) onic_get_counter_from_db() : $expected_next ) );

foreach ( $number_map as $numeric => $entries ) {
	if ( count( $entries ) < 2 ) {
		continue;
	}

	// Sort by creation time; the earliest keeps the number.
	usort( $entries, function ( $a, $b ) {
		return $a['ts'] <=> $b['ts'];
	} );

	$keeper = array_shift( $entries );

	foreach ( $entries as $dupe ) {
		$dupes_found[] = $dupe['id'];
		$onic_problems = true;

		$in_window = ( $dupe['ts'] >= $window_start );
		onic_log( 'DUPLICATE: order #' . $dupe['id'] . ' shares number ' . $numeric . ' (' . $dupe['full'] . ') with order #' . $keeper['id'] . ( $in_window ? '' : ' [outside 24h fix window, reporting only]' ) . '.' );

		if ( ! ONIC_AUTO_FIX || ! $in_window ) {
			continue;
		}

		// Make sure the candidate number is genuinely unused.
		while ( isset( $number_map[ $next_number ] ) ) {
			$next_number++;
		}

		$order = wc_get_order( $dupe['id'] );
		if ( ! $order ) {
			onic_log( 'ERROR: could not load order #' . $dupe['id'] . ' for fixing.' );
			continue;
		}

		$new_full = onic_build_full_number( $dupe['full'], $next_number );

		$order->update_meta_data( ONIC_NUM_META, $next_number );
		$order->update_meta_data( ONIC_FULL_META, $new_full );
		$order->add_order_note( sprintf(
			'Order number integrity script: duplicate custom order number %s (also used by order #%d) replaced with %s.',
			$dupe['full'],
			$keeper['id'],
			$new_full
		) );
		$order->save();

		// Register the new number so later iterations respect it.
		$number_map[ $next_number ][] = array( 'id' => $dupe['id'], 'ts' => $dupe['ts'], 'full' => $new_full );

		onic_log( 'FIXED: order #' . $dupe['id'] . ' re-numbered ' . $dupe['full'] . ' -> ' . $new_full . '.' );
		$fixed[] = $dupe['id'];
		$next_number++;
	}
}

// If we consumed numbers while fixing, push the counter forward again.
if ( ONIC_AUTO_FIX && ! empty( $fixed ) && '' !== ONIC_COUNTER_OPT ) {
	onic_set_counter( $next_number );
	onic_log( 'Counter advanced to ' . $next_number . ' after re-numbering ' . count( $fixed ) . ' order(s).' );
}

if ( empty( $dupes_found ) ) {
	onic_log( 'No duplicate order numbers found.' );
}

onic_log( 'Check complete. Problems found: ' . ( $onic_problems ? 'YES' : 'no' ) . '.' );

// ------------------------------------------------------------------
// 4. Email report.
// ------------------------------------------------------------------
if ( $onic_problems || ONIC_ALWAYS_EMAIL ) {
	$site    = wp_parse_url( home_url(), PHP_URL_HOST );
	$subject = $onic_problems
		? '[' . $site . '] Order number issue ' . ( ONIC_AUTO_FIX ? 'detected and fixed' : 'detected (report-only mode)' )
		: '[' . $site . '] Order number check: all healthy';

	$body = "WooCommerce order number integrity report\n"
		. 'Site: ' . home_url() . "\n"
		. 'Run at: ' . current_time( 'Y-m-d H:i:s' ) . "\n\n"
		. implode( "\n", $onic_log );

	$sent = wp_mail( ONIC_ALERT_EMAIL, $subject, $body );
	echo $sent ? "Report emailed to " . ONIC_ALERT_EMAIL . ".\n" : "WARNING: wp_mail failed to send the report.\n";
}

exit( 0 );