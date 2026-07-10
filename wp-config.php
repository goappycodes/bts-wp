<?php
@include '/home/forge/brieftaubenshop.de/public/malcare-waf.php';

// die("Under maintenance");

define( 'WP_CACHE', true ); // Added by WP Rocket

//Begin Really Simple SSL session cookie settings
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
//END Really Simple SSL

define('WPFC_CACHE_QUERYSTRING', true);

// ** MySQL settings ** //
/** The name of the database for WordPress */
define('DB_NAME', 'btswp');

/** MySQL database username */
define('DB_USER', 'forge');

/** MySQL database password */
define('DB_PASSWORD', 'yCK9M3_d5c');

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

// define( 'WPLANG', 'en_GB');

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', 'QNy+R=!YRXgKu[Ljt/r6Wg;*fLJ{)$f3FtN=a;vX#LZ643jdyvbcitHH-VO.a!Vu');
define('SECURE_AUTH_KEY', 'Pa07K :^i[5/ah[Udf eMJBm0k/Wi3:j]*T0s95J)UG@zjl[V-k_-v^aNjP|Ju?q');
define('LOGGED_IN_KEY', 'SnW0i3]-W_=~faa[ 3SPUj@ou/[z2bG/{{tP;&`CiH@;|?y+zTQP@KW?6.M=9R>b');
define('NONCE_KEY', 'lo=k%KbMRD<m+.9]ML%4]N80b_;PK9Kk4:a<K`@/rx@6|^!c-e*|A@TqFY(}.%M5');
define('AUTH_SALT', 'Co5tfgDEa~XqKu|3]9q*)/t^Z+)kU67S-H*+^15Zaj5Wo!u/ pJc6}BD4O{n(5AS');
define('SECURE_AUTH_SALT', 'wlL^7 5PJW*P;?SK{e>Sj+VcKOr.O-iC?Q8*;d+u=NZ&!B]G`EJ89t70,iN[sPLT');
define('LOGGED_IN_SALT', 'v5.N%Mb.?JYC *qcoB [osDSyf:RdT*N/}$wEge3/Eo[Ef_=j{h/TpO[&HF(40FR');
define('NONCE_SALT', 'vlGPIMaB0e t-<5&_M &I|s->C0/.N+eE{~6m&@aq6H&>K{{(#BwWu)aRbg>.apQ');

define( 'WC_GZD_DIRECT_DEBIT_KEY', 'def00000fa751526f063527fcc016ddf6a04481cc897504ab917a20e847a0388bc7e540292bee94cc343d0d2cc1a90e6fbc67c7610ca21044fd1c772c076016048712276' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'Jkef3j_';

define('WP_MEMORY_LIMIT', '2048M');


define('WP_DEBUG', FALSE);
define('WP_DEBUG_LOG', FALSE);
define('WP_DEBUG_DISPLAY', FALSE);


// define('WP_ALLOW_MULTISITE', true);


/* That's all, stop editing! Happy blogging. */

define( 'WC_GZD_ENCRYPTION_KEY', 'ed79022494807ccf07a575d223db9046a4eee3b8099716963cb2c192382a3447' );

//Disable File Edits
// define('DISALLOW_FILE_MODS', true);
// define('DISALLOW_FILE_EDIT', true);

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';