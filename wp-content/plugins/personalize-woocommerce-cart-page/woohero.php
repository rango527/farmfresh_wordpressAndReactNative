<?php 
/*
Plugin Name: WooHero
Plugin URI:  http://www.najeebmedia.com
Description: WOOHero is like a Swiss Army knife for WooCommerce. WooHero holds a bunch of features of many small plugins.
Version: 3.1
Author: N-Media
Text Domain: wooh
WC requires at least: 3.0.0
WC tested up to: 3.5.3
Author URI: http://www.najeebmedia.com/
*/


/* **== Direct access not allowed ==** */
if( ! defined('ABSPATH' ) ){ exit; }

/* ==== Define WC WooHero Constant ===== */
define( 'WOOH_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define( 'WOOH_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define( 'WOOH_VERSION', 3.0 );
define( 'WOOH_DEBUG', true );

/* **======== plugin includes files ========** */
if( file_exists( dirname(__FILE__).'/inc/helpers.php' )) include_once dirname(__FILE__).'/inc/helpers.php';
if( file_exists( dirname(__FILE__).'/inc/admin.php' )) include_once dirname(__FILE__).'/inc/admin.php';
if( file_exists( dirname(__FILE__).'/inc/classes/class.admin.php' )) include_once dirname(__FILE__).'/inc/classes/class.admin.php';
if( file_exists( dirname(__FILE__).'/inc/classes/class.settings.php' )) include_once dirname(__FILE__).'/inc/classes/class.settings.php';


class WOOH_MAIN {

    private static $ins = null;
    
    function __construct(){
         add_action( 'init', array($this, 'wooh_language_setup'));
    }
    

    function wooh_language_setup() {
        
        $locale_dir = dirname( plugin_basename( dirname(__FILE__) ) ) . '/languages';
		load_plugin_textdomain('wooh', false, $locale_dir);
    }  
    
    
    public static function get_instance() {
          // create a new object if it doesn't exist.
        is_null(self::$ins) && self::$ins = new self;
        return self::$ins;
    }


}

/* == Lets start plugin ==*/
add_action('plugins_loaded', 'wooh_start');
function wooh_start() {
    return WOOH_MAIN::get_instance();
}