<?php
/**
 * WooCommerce Order Number Integrity Check & Auto-Fix
 *
 * Built for the "Custom Order Numbers for WooCommerce" (WPFactory / Algoritmika)
 * plugin, where the next order number is stored in the wp_options table and can
 * go stale when Redis object cache serves an old value.
 *
 * What it does on every run:
 *  1. Reads the highest custom order number currently in the database and makes
 *     sure the plugin counter option is >= (highest used number + 1). If the
 *     counter is behind (the stale Redis cache scenario), it resets the counter
 *     to max + 1 and flushes the object cache keys for that option.
 *  2. Loads ONLY the orders created in the last 24 hours and, for each one,
 *     checks the WHOLE database for any other order that shares the same custom
 *     order number. If a duplicate is found, the NEWER order (the recent one
 *     that reused a number) gets re-numbered from the corrected counter and an
 *     order note is added so there is an audit trail.
 *  3. Emails a report to ONIC_ALERT_EMAIL only when a problem was found.
 *
 * Unlike a windowed comparison, the duplicate check in step 2 compares each
 * recent order against every order in the database, so a collision with an old
 * order (older than any lookback window) is still caught.
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
define( 'ONIC_CHECK_HOURS',   24 );    // only orders created in this window are checked / auto-fixed
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
 * Resolve the meta table + id column to query, honouring HPOS (custom order
 * tables) when it is enabled and falling back to classic postmeta otherwise.
 * Returns array( $table_name, $id_column ).
 */
function onic_meta_table_info() {
	global $wpdb;
	$hpos = false;
	if ( class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
		$hpos = \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	}
	return $hpos
		? array( $wpdb->prefix . 'wc_orders_meta', 'order_id' )
		: array( $wpdb->postmeta, 'post_id' );
}

/**
 * Highest custom order number currently stored in the database (across ALL
 * orders, not a time window). This is the true ceiling the counter must clear.
 */
function onic_get_max_numeric_from_db() {
	global $wpdb;
	list( $table ) = onic_meta_table_info();
	$val = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT MAX(CAST(meta_value AS UNSIGNED)) FROM {$table} WHERE meta_key = %s",
			ONIC_NUM_META
		)
	);
	return (int) $val;
}

/**
 * Every order id in the database whose custom order number equals $numeric.
 * Queries the meta table directly so it is storage-agnostic (HPOS or legacy)
 * and does not depend on a comparison window.
 */
function onic_find_orders_by_number( $numeric ) {
	global $wpdb;
	list( $table, $id_col ) = onic_meta_table_info();
	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT {$id_col} FROM {$table} WHERE meta_key = %s AND meta_value = %s",
			ONIC_NUM_META,
			(string) $numeric
		)
	);
	return array_map( 'intval', (array) $ids );
}

/**
 * True if any order already uses this number (used to pick a safe replacement).
 */
function onic_number_in_use( $numeric ) {
	return count( onic_find_orders_by_number( $numeric ) ) > 0;
}

/**
 * Resolve the custom order number for many order ids in ONE query, without
 * hydrating any WC_Order objects. Mirrors onic_extract_numeric(): prefer the
 * numeric meta, fall back to the last digit run in the formatted number.
 * Returns array( order_id => numeric ). Ids are chunked to keep the IN() list
 * bounded on very high-volume stores.
 */
function onic_get_numbers_for_ids( array $ids ) {
	global $wpdb;
	list( $table, $id_col ) = onic_meta_table_info();
	$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );
	$num  = array();
	$full = array();
	foreach ( array_chunk( $ids, 1000 ) as $chunk ) {
		$id_ph = implode( ',', array_fill( 0, count( $chunk ), '%d' ) );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$id_col} AS id, meta_key, meta_value
				 FROM {$table}
				 WHERE {$id_col} IN ($id_ph) AND meta_key IN (%s, %s)",
				array_merge( $chunk, array( ONIC_NUM_META, ONIC_FULL_META ) )
			)
		);
		foreach ( $rows as $r ) {
			if ( ONIC_NUM_META === $r->meta_key ) {
				$num[ (int) $r->id ] = $r->meta_value;
			} else {
				$full[ (int) $r->id ] = (string) $r->meta_value;
			}
		}
	}
	$out = array();
	foreach ( $ids as $id ) {
		$n = 0;
		if ( isset( $num[ $id ] ) && '' !== $num[ $id ] && is_numeric( $num[ $id ] ) ) {
			$n = (int) $num[ $id ];
		} elseif ( isset( $full[ $id ] ) && '' !== $full[ $id ] && preg_match( '/(\d+)(?!.*\d)/', $full[ $id ], $m ) ) {
			$n = (int) $m[1];
		}
		$out[ $id ] = $n;
	}
	return $out;
}

/**
 * Given a set of custom order numbers, return every order in the WHOLE database
 * that carries one of them, grouped by number: array( numeric => array( ids ) ).
 * Replaces the per-order duplicate lookup (one query per recent order) with a
 * single indexed IN() scan (chunked), so the duplicate check is O(1) queries
 * instead of O(recent orders).
 */
function onic_find_orders_for_numbers( array $numbers ) {
	global $wpdb;
	list( $table, $id_col ) = onic_meta_table_info();
	$numbers = array_values( array_unique( array_map( 'intval', $numbers ) ) );
	$map = array();
	foreach ( array_chunk( $numbers, 1000 ) as $chunk ) {
		$vals = array_map( 'strval', $chunk );
		$ph   = implode( ',', array_fill( 0, count( $vals ), '%s' ) );
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT {$id_col} AS id, meta_value AS num
				 FROM {$table}
				 WHERE meta_key = %s AND meta_value IN ($ph)",
				array_merge( array( ONIC_NUM_META ), $vals )
			)
		);
		foreach ( $rows as $r ) {
			$map[ (int) $r->num ][] = (int) $r->id;
		}
	}
	return $map;
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

onic_log( 'Starting order number integrity check (checking orders from the last ' . ONIC_CHECK_HOURS . 'h against the whole database).' );

// ------------------------------------------------------------------
// 1. Counter sanity check (this is where stale Redis bites).
//    Uses the true database max, independent of the 24h fetch below.
// ------------------------------------------------------------------
$max_numeric   = onic_get_max_numeric_from_db();
$expected_next = $max_numeric + 1;

onic_log( 'Highest custom order number in the database: ' . $max_numeric . '.' );

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
// 2. Load orders from the last 24 hours and, for each, check the whole
//    database for a duplicate custom order number.
// ------------------------------------------------------------------
$window_start = time() - ONIC_CHECK_HOURS * HOUR_IN_SECONDS;

// Fetch only the IDs of recent orders (no object hydration) - the numbers are
// pulled in a single batch query below.
$recent_ids = wc_get_orders( array(
	'limit'        => -1,
	'return'       => 'ids',
	'type'         => 'shop_order',
	'status'       => array_keys( wc_get_order_statuses() ), // include failed/cancelled: they consumed numbers too
	'date_created' => '>=' . $window_start,
	'orderby'      => 'date',
	'order'        => 'ASC',
) );

if ( empty( $recent_ids ) ) {
	onic_log( 'No orders created in the last ' . ONIC_CHECK_HOURS . 'h. No duplicate check needed.' );
} else {
	onic_log( 'Loaded ' . count( $recent_ids ) . ' order id(s) from the last ' . ONIC_CHECK_HOURS . 'h to check.' );
}

$dupes_found = array();
$fixed       = array();

// Seed the replacement number above every number currently in use.
$counter_val = ( '' !== ONIC_COUNTER_OPT ) ? (int) onic_get_counter_from_db() : 0;
$next_number = max( $expected_next, $counter_val );

if ( ! empty( $recent_ids ) ) {
	// One query: resolve the custom order number for every recent order.
	$recent_numbers_map = onic_get_numbers_for_ids( $recent_ids );

	// Distinct positive numbers used by recent orders (a number can back several).
	$recent_numbers = array();
	foreach ( $recent_numbers_map as $n ) {
		if ( $n > 0 ) {
			$recent_numbers[ $n ] = true;
		}
	}
	$recent_numbers = array_keys( $recent_numbers );

	// One query: every order in the whole DB that shares any of those numbers,
	// grouped by number. Any group of 2+ is a collision.
	$matches_by_number = empty( $recent_numbers )
		? array()
		: onic_find_orders_for_numbers( $recent_numbers );

	foreach ( $recent_numbers as $numeric ) {
		$match_ids = isset( $matches_by_number[ $numeric ] ) ? $matches_by_number[ $numeric ] : array();
		if ( count( $match_ids ) < 2 ) {
			continue; // unique in the database - nothing to do
		}

		// Only the (rare) colliding orders get hydrated, to order them by time.
		$entries = array();
		foreach ( $match_ids as $mid ) {
			$mo = wc_get_order( $mid );
			if ( ! $mo ) {
				continue;
			}
			$entries[] = array(
				'id'    => $mid,
				'ts'    => $mo->get_date_created() ? $mo->get_date_created()->getTimestamp() : 0,
				'full'  => (string) $mo->get_meta( ONIC_FULL_META ),
				'order' => $mo,
			);
		}
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
			onic_log( 'DUPLICATE: order #' . $dupe['id'] . ' shares number ' . $numeric . ' (' . $dupe['full'] . ') with order #' . $keeper['id'] . ( $in_window ? '' : ' [older than ' . ONIC_CHECK_HOURS . 'h, reporting only]' ) . '.' );

			if ( ! ONIC_AUTO_FIX || ! $in_window ) {
				continue;
			}

			// Make sure the candidate number is genuinely unused in the database.
			while ( onic_number_in_use( $next_number ) ) {
				$next_number++;
			}

			$fix_order = $dupe['order'];
			$new_full  = onic_build_full_number( $dupe['full'], $next_number );

			$fix_order->update_meta_data( ONIC_NUM_META, $next_number );
			$fix_order->update_meta_data( ONIC_FULL_META, $new_full );
			$fix_order->add_order_note( sprintf(
				'Order number integrity script: duplicate custom order number %s (also used by order #%d) replaced with %s.',
				$dupe['full'],
				$keeper['id'],
				$new_full
			) );
			$fix_order->save();

			onic_log( 'FIXED: order #' . $dupe['id'] . ' re-numbered ' . $dupe['full'] . ' -> ' . $new_full . '.' );
			$fixed[] = $dupe['id'];
			$next_number++;
		}
	}
}

// If we consumed numbers while fixing, push the counter forward again.
if ( ONIC_AUTO_FIX && ! empty( $fixed ) && '' !== ONIC_COUNTER_OPT ) {
	onic_set_counter( $next_number );
	onic_log( 'Counter advanced to ' . $next_number . ' after re-numbering ' . count( $fixed ) . ' order(s).' );
}

if ( empty( $dupes_found ) ) {
	onic_log( 'No duplicate order numbers found for orders in the last ' . ONIC_CHECK_HOURS . 'h.' );
}

onic_log( 'Check complete. Problems found: ' . ( $onic_problems ? 'YES' : 'no' ) . '.' );

// ------------------------------------------------------------------
// 3. Email report.
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
