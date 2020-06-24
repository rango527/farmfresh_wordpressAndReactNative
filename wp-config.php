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
define( 'DB_NAME', 'farmfreshDB' );

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
define('AUTH_KEY',         'w]qiv_NU9E*,ed7Wm<|=m*+S[h5I6sd<?c->=3l`M--m1t?P6iO?I4*SY9gCnj4&');
define('SECURE_AUTH_KEY',  'KZ`P:~=bbaaU1raoLbXz|Gz+$lN3n mn&UX./eoS@r>CCX][;QEcPL O{,5V[uX9');
define('LOGGED_IN_KEY',    'A4Cpbf#MV=5aX@QE+@~&38X-R+Kq;s<myl+|c!EF/HQ%L+nWLirw2$3a/rdgV|e-');
define('NONCE_KEY',        'EQ-]zSdh2tVxspjK1P@uI% |Dw)PwZ3g${0.Ly~7h)d7},<=J4-JjZE(_+9!9[GT');
define('AUTH_SALT',        '6{g3a6M|bs)i-^,lVXQ+U7]7wK=wA*!1lI^915T<N!X1F8a*?+o6GH #X4D,zTl`');
define('SECURE_AUTH_SALT', '(f21wT)!|_s|yp.f{CK0f&-L![y6kJYj:-T__<;YORUc+j>,G!kPuj|fl}7txlR0');
define('LOGGED_IN_SALT',   'j*F+)0SLY,i mQ_Es%:`Z FZio}RnfEu5=)ceM|,|)pZ8v,tx+Xui5H?!_P2o2|H');
define('NONCE_SALT',       '.j-q0ryRIpA)gB5#A`-|&WNDn$Hhsx7tYGTiK]o5YF`rI[:y61/[Lr&.{ED74V|1');

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
