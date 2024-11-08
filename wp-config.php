<?php

define('FS_METHOD', 'direct');
define('FORCE_SSL_ADMIN', true);

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dbs8020349' );

/** Database username */
define( 'DB_USER', 'dbu4108765' );

/** Database password */
define( 'DB_PASSWORD', 'RrvZzMqJFBByXQyhZGTB' );

/** Database hostname */
define( 'DB_HOST', 'db5009457014.hosting-data.io' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          '[8)-odvhyHw7DUuJ;$^A/|s%id=(|>pEaXHI(I:[0Jw0+oaYGGs9sJ9+^%w$yo~k' );
define( 'SECURE_AUTH_KEY',   'ba_>sJJ!P2pdwe{d$Kx0(BqHjK4 Wu;xEE+;J&%<:L5$4=RbJ_VX%K.)ifNqEW(;' );
define( 'LOGGED_IN_KEY',     '&<MQbP]]j#BhHss8cw):ggWFf6{p[H`96#2]$]&VRke+s2sSy}&Riv|Ui`-Ii69H' );
define( 'NONCE_KEY',         'wOV]6 0npNQF2^zkI`V0/]>nA-sQ426ysI;6GPqE(.J )Zu#;GRrR#Hquk;%9X;,' );
define( 'AUTH_SALT',         '_!PZ*J7=]>_wZ`PtnFoC,yec@mQ,^axuuw]_- -P^<PfK|;p&tG9[c<0lVk;<^Lq' );
define( 'SECURE_AUTH_SALT',  '|HEo`P<eXpNhu=.jY1a<a(,weErB(Dq@<Pw553sCRI%fEHZ,T*ey5?mN(6Z3{d-/' );
define( 'LOGGED_IN_SALT',    '9[Cv!H!W:I[Di}sbFV.6y6bqUNJS/c-pe|$U/o3hs4v+UV`tu5[`Lz3I}5.rin]p' );
define( 'NONCE_SALT',        '&LJ}[rx8D1X9%lBpi%iH07F( jquRCiZ8@caoRyY)d?)=t4bda4V-4gJJ2}@pt(2' );
define( 'WP_CACHE_KEY_SALT', '<ClH24n:rW5 GS#CcqD.2uDxR59a`qK!%=t~&))R]H]X^p1(q#Pe@D*:7?2FB{^8' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'JSrsLOIM';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
