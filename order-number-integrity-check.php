<?php
/**
 * WooCommerce Order Number Duplicate Report (report-only)
 *
 * Built for the "Custom Order Numbers for WooCommerce" (WPFactory / Algoritmika)
 * plugin. This script does NOT change anything - it only reports.
 *
 * What it does on every run:
 *  1. Loads ONLY the orders created in the last 24 hours and, for each one,
 *     checks the WHOLE database for any other order that shares the same custom
 *     order number. Any collision is logged (nothing is renumbered).
 *  2. Emails a report to ONIC_ALERT_EMAIL only when a duplicate was found.
 *
 * Unlike a windowed comparison, the duplicate check compares each recent order
 * against every order in the database, so a collision with an old order (older
 * than any lookback window) is still caught.
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
define( 'ONIC_CHECK_HOURS',   24 );    // only orders created in this window are checked
define( 'ONIC_ALWAYS_EMAIL',  false ); // true = also email when everything is healthy

// Plugin-specific keys (defaults are for WPFactory "Custom Order Numbers for WooCommerce").
// If you use SkyVerge "Sequential Order Numbers Pro" instead, set:
//   ONIC_FULL_META  = '_order_number_formatted'
//   ONIC_NUM_META   = '_order_number'
define( 'ONIC_FULL_META',   '_alg_wc_full_custom_order_number' );
define( 'ONIC_NUM_META',    '_alg_wc_custom_order_number' );
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

// Report-only run: mark as cron so any incidental order hydration stays quiet.
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
 * Resolve the custom order number for many order ids in ONE query, without
 * hydrating any WC_Order objects. Prefers the numeric meta, falls back to the
 * last digit run in the formatted number (safe with date-based prefixes like
 * "2026-"). Returns array( order_id => numeric ). Ids are chunked to keep the
 * IN() list bounded on very high-volume stores.
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
 * A single indexed IN() scan (chunked), so the duplicate check is O(1) queries
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

onic_log( 'Starting order number duplicate report (checking orders from the last ' . ONIC_CHECK_HOURS . 'h against the whole database).' );

// ------------------------------------------------------------------
// Load orders from the last 24 hours and, for each, check the whole
// database for a duplicate custom order number. Report only.
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
			continue; // unique in the database - nothing to report
		}

		// Only the (rare) colliding orders get hydrated, to order them by time
		// and show the formatted number in the report.
		$entries = array();
		foreach ( $match_ids as $mid ) {
			$mo = wc_get_order( $mid );
			if ( ! $mo ) {
				continue;
			}
			$entries[] = array(
				'id'   => $mid,
				'ts'   => $mo->get_date_created() ? $mo->get_date_created()->getTimestamp() : 0,
				'full' => (string) $mo->get_meta( ONIC_FULL_META ),
			);
		}
		if ( count( $entries ) < 2 ) {
			continue;
		}

		// Sort by creation time; the earliest is the "original".
		usort( $entries, function ( $a, $b ) {
			return $a['ts'] <=> $b['ts'];
		} );

		$keeper = array_shift( $entries );

		foreach ( $entries as $dupe ) {
			$dupes_found[] = $dupe['id'];
			$onic_problems = true;

			$recent = ( $dupe['ts'] >= $window_start );
			onic_log( 'DUPLICATE: order #' . $dupe['id'] . ' shares number ' . $numeric . ' (' . $dupe['full'] . ') with order #' . $keeper['id'] . ( $recent ? ' [created in the last ' . ONIC_CHECK_HOURS . 'h]' : '' ) . '.' );
		}
	}
}

if ( empty( $dupes_found ) ) {
	onic_log( 'No duplicate order numbers found for orders in the last ' . ONIC_CHECK_HOURS . 'h.' );
}

onic_log( 'Check complete. Duplicates found: ' . ( $onic_problems ? 'YES (' . count( $dupes_found ) . ')' : 'no' ) . '.' );

// ------------------------------------------------------------------
// Email report.
// ------------------------------------------------------------------
if ( $onic_problems || ONIC_ALWAYS_EMAIL ) {
	$site    = wp_parse_url( home_url(), PHP_URL_HOST );
	$subject = $onic_problems
		? '[' . $site . '] Duplicate order number(s) detected'
		: '[' . $site . '] Order number check: all healthy';

	$body = "WooCommerce order number duplicate report\n"
		. 'Site: ' . home_url() . "\n"
		. 'Run at: ' . current_time( 'Y-m-d H:i:s' ) . "\n\n"
		. implode( "\n", $onic_log );

	$sent = wp_mail( ONIC_ALERT_EMAIL, $subject, $body );
	echo $sent ? "Report emailed to " . ONIC_ALERT_EMAIL . ".\n" : "WARNING: wp_mail failed to send the report.\n";
}

exit( 0 );
