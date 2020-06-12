<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Rango(4)' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
define('FS_METHOD', 'direct');
/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '3je#,,ao-!b&Nq#,|bqj?vk</)[;CawUyj+k+ei<sveZc*6_ hoT^)xXW+-+;t9V');
define('SECURE_AUTH_KEY',  '.17A5P!)9-o6>3BvpIh}|J7!~Nd:>J4&TAA3VVC3u1g3}0aG2y}0$MC<M}h-$6Mv');
define('LOGGED_IN_KEY',    'c/Uy4iE0<v~!7-GHCn#~6S-d:=:y|<eIn}OT%a3X&>0i<wCGrqe?sA> dP-OvN=P');
define('NONCE_KEY',        '%7xZ&C heG59h.)P<$pmaNHp1V|L-8HGn`:fAjc|b9L W9UNwb~~fD.aZWJSS) 6');
define('AUTH_SALT',        'uRv7JVvl>2CaR FB1#A6=`f8-tY0 MvR}{SdFIuX-Jvh~9$NsZXu+z*1k[1_4=[(');
define('SECURE_AUTH_SALT', 'v-kr>].YJF4.+o%: %=pRgB+OdsV%?vVhgESLup2O}ZVK3QOXdTK ~5vYZV?0Ob{');
define('LOGGED_IN_SALT',   'J3+ t1t[apxyLL+tA:pCke/j]#Cl6SD<%)srJoKo:i#_L5ofzm^qG}{Tk;+Uk(t^');
define('NONCE_SALT',       '[qxOw0f8{k?!u=Rl|1EF.Y -*J2YV >8+V =F-*uyBcK}1{0)sE+H@|^YIgCA5c+');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
