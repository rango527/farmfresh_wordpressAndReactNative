<?php
/*
Plugin Name: Product Quantity for WooCommerce
Plugin URI: https://wpfactory.com/item/product-quantity-for-woocommerce/
Description: Manage product quantity in WooCommerce, beautifully. Define a minimum / maximum / step quantity and more on WooCommerce products.
Version: 2.9
Author: WPWhale
Author URI: http://www.wpwhale.com
Text Domain: product-quantity-for-woocommerce
Domain Path: /langs
Copyright: © 2020 WPWhale
WC tested up to: 4.2
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'alg_wc_pq_check_free_active' ) ) :
function alg_wc_pq_check_free_active() {
	
	$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins', array() ) );
	
	if ( alg_wc_pq_check_if_active_plugin( 'product-quantity-for-woocommerce', 'product-quantity-for-woocommerce.php', $active_plugins ) ) {
	deactivate_plugins( plugin_basename( __FILE__ ) );
    wp_die( sprintf( __( 'You need to deactivate Product Quantity Control for WooCommerce. <br/> %s back to plugins. %s', 'product-quantity-for-woocommerce' ), '<a href="' . wp_nonce_url( 'plugins.php?plugin_status=all' ) . '">', '</a>' ) );
		if ( isset( $_GET['activate'] ) ) {
			unset( $_GET['activate'] );
		}
	}

}
endif;

register_activation_hook(__FILE__, 'alg_wc_pq_check_free_active');

require_once( 'includes/functions/alg-wc-pq-core-functions.php' );

if ( alg_wc_pq_do_disable( basename( __FILE__ ) ) ) {
	return;
}

if ( ! class_exists( 'Alg_WC_PQ' ) ) :

/**
 * Main Alg_WC_PQ Class
 *
 * @class   Alg_WC_PQ
 * @version 1.8.0
 * @since   1.0.0
 */
final class Alg_WC_PQ {

	/**
	 * Plugin version.
	 *
	 * @var   string
	 * @since 1.0.0
	 */
	public $version = '1.8.1';

	/**
	 * @var   Alg_WC_PQ The single instance of the class
	 * @since 1.0.0
	 */
	protected static $_instance = null;

	/**
	 * Main Alg_WC_PQ Instance
	 *
	 * Ensures only one instance of Alg_WC_PQ is loaded or can be loaded.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @static
	 * @return  Alg_WC_PQ - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Alg_WC_PQ Constructor.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 * @access  public
	 */
	function __construct() {

		// Set up localisation
		load_plugin_textdomain( 'product-quantity-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/langs/' );

		// Pro
		if ( 'product-quantity-for-woocommerce-pro.php' === basename( __FILE__ ) ) {
			require_once( 'includes/pro/class-alg-wc-pq-pro.php' );
		}

		// Include required files
		$this->includes();

		// Admin
		if ( is_admin() ) {
			$this->admin();
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function includes() {
		// Core
		$this->core = require_once( 'includes/class-alg-wc-pq-core.php' );
	}

	/**
	 * admin.
	 *
	 * @version 1.7.0
	 * @since   1.3.0
	 */
	function admin() {
		// Action links
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		// Settings
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'add_woocommerce_settings_tab' ) );
		require_once( 'includes/settings/class-alg-wc-pq-metaboxes.php' );
		require_once( 'includes/settings/class-alg-wc-pq-category-metaboxes.php' );
		require_once( 'includes/settings/class-alg-wc-pq-settings-section.php' );
		$this->settings = array();
		$this->settings['general']      = require_once( 'includes/settings/class-alg-wc-pq-settings-general.php' );
		$this->settings['min']          = require_once( 'includes/settings/class-alg-wc-pq-settings-min.php' );
		$this->settings['max']          = require_once( 'includes/settings/class-alg-wc-pq-settings-max.php' );
		$this->settings['default']      = require_once( 'includes/settings/class-alg-wc-pq-settings-default.php' );
		$this->settings['step']         = require_once( 'includes/settings/class-alg-wc-pq-settings-step.php' );
		$this->settings['fixed']        = require_once( 'includes/settings/class-alg-wc-pq-settings-fixed.php' );
		$this->settings['dropdown']     = require_once( 'includes/settings/class-alg-wc-pq-settings-dropdown.php' );
		$this->settings['price_by_qty'] = require_once( 'includes/settings/class-alg-wc-pq-settings-price-by-qty.php' );
		$this->settings['price_unit'] 	= require_once( 'includes/settings/class-alg-wc-pq-settings-price-unit.php' );
		$this->settings['qty_info']     = require_once( 'includes/settings/class-alg-wc-pq-settings-qty-info.php' );
		$this->settings['styling']      = require_once( 'includes/settings/class-alg-wc-pq-settings-styling.php' );
		$this->settings['admin']        = require_once( 'includes/settings/class-alg-wc-pq-settings-admin.php' );
		$this->settings['advanced']     = require_once( 'includes/settings/class-alg-wc-pq-settings-advanced.php' );
		
		
		// Version updated
		if ( get_option( 'alg_wc_pq_version', '' ) !== $this->version ) {
			add_action( 'admin_init', array( $this, 'version_updated' ) );
		}
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 * @param   mixed $links
	 * @return  array
	 */
	function action_links( $links ) {
		$custom_links = array();
		$custom_links[] = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=alg_wc_pq' ) . '">' . __( 'Settings', 'woocommerce' ) . '</a>';
$custom_links[] = '<a style=" font-weight: bold;" target="_blank" href="' . esc_url( 'https://wordpress.org/support/plugin/product-quantity-for-woocommerce/reviews/#new-post"' ) . '">' .
				__( 'Review Us', 'product-quantity-for-woocommerce' ) . '</a>';
		if ( 'product-quantity-for-woocommerce.php' === basename( __FILE__ ) ) {
			$custom_links[] = '<a style="color: green; font-weight: bold;" target="_blank" href="' . esc_url( 'https://wpfactory.com/item/product-quantity-for-woocommerce/"' ) . '">' .
				__( 'Go Pro', 'product-quantity-for-woocommerce' ) . '</a>';
		}
		return array_merge( $custom_links, $links );
	}

	/**
	 * Add Product Quantity settings tab to WooCommerce settings.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function add_woocommerce_settings_tab( $settings ) {
		$settings[] = require_once( 'includes/settings/class-alg-wc-settings-pq.php' );
		return $settings;
	}

	/**
	 * version_updated.
	 *
	 * @version 1.3.0
	 * @since   1.2.0
	 */
	function version_updated() {
		update_option( 'alg_wc_pq_version', $this->version );
	}

	/**
	 * Get the plugin url.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_url() {
		return untrailingslashit( plugin_dir_url( __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  string
	 */
	function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

}

endif;

if ( ! function_exists( 'alg_wc_pq' ) ) {
	/**
	 * Returns the main instance of Alg_WC_PQ to prevent the need to use globals.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 * @return  Alg_WC_PQ
	 */
	function alg_wc_pq() {
		return Alg_WC_PQ::instance();
	}
}

alg_wc_pq();

/* Added By Me - START */

if ( ! function_exists( 'mp_sync_on_product_save' ) ) {
	add_action('woocommerce_update_product', 'mp_sync_on_product_save', 10, 1);
	function mp_sync_on_product_save( $post_id ) {
		$product = wc_get_product( $post_id );
		if ( $product->is_type( 'variable' ) ) {
			$main_product_meta = get_post_meta( $post_id );
			$main_product_min_quantity_to_all = get_post_meta( $post_id, 'main_product_min_quantity_to_all' , true );
			$main_product_max_quantity_to_all = get_post_meta( $post_id, 'main_product_max_quantity_to_all' , true );
			$main_product_step_quantity_to_all = get_post_meta( $post_id, 'main_product_step_quantity_to_all' , true );
			
			$_alg_wc_pq_min = get_post_meta( $post_id, '_alg_wc_pq_min' , true );
			$_alg_wc_pq_max = get_post_meta( $post_id, '_alg_wc_pq_max' , true );
			$_alg_wc_pq_step = get_post_meta( $post_id, '_alg_wc_pq_step' , true );
			$available_variations = $product->get_available_variations();
		
			foreach($available_variations as $res) {
				$variation_id = $res['variation_id'];
				$variation_meta = get_post_meta( $variation_id );
				if($main_product_min_quantity_to_all == 'yes') {
					update_post_meta( $variation_id, '_alg_wc_pq_min', $_alg_wc_pq_min );
				}
				if($main_product_max_quantity_to_all == 'yes') {
					update_post_meta( $variation_id, '_alg_wc_pq_max', $_alg_wc_pq_max );
				}
				if($main_product_step_quantity_to_all == 'yes') {
					update_post_meta( $variation_id, '_alg_wc_pq_step', $_alg_wc_pq_step );
				}

			}
		}
	}
}

if ( ! function_exists( 'misha_adv_product_options' ) ) {
	add_action( 'woocommerce_product_options_advanced', 'misha_adv_product_options');
	function misha_adv_product_options(){
	 
		echo '<div class="options_group custom_quantity_options_group">';
	 
		woocommerce_wp_checkbox( array(
			'id'      => 'main_product_min_quantity_to_all',
			'value'   => get_post_meta( get_the_ID(), 'main_product_min_quantity_to_all', true ),
			'label'   => 'Add Main product min quantity to all',
			'desc_tip' => true,
			'description' => 'Add Main product quantity for all variations',
		) );
		woocommerce_wp_checkbox( array(
			'id'      => 'main_product_max_quantity_to_all',
			'value'   => get_post_meta( get_the_ID(), 'main_product_max_quantity_to_all', true ),
			'label'   => 'Add Main product max quantity to all',
			'desc_tip' => true,
			'description' => 'Add Main product quantity for all variations',
		) );
		woocommerce_wp_checkbox( array(
			'id'      => 'main_product_step_quantity_to_all',
			'value'   => get_post_meta( get_the_ID(), 'main_product_step_quantity_to_all', true ),
			'label'   => 'Add Main product step quantity to all',
			'desc_tip' => true,
			'description' => 'Add Main product quantity for all variations',
		) );
	 
		echo '</div>';
	 
	}
}
 

if ( ! function_exists( 'misha_save_fields' ) ) {
	add_action( 'woocommerce_process_product_meta', 'misha_save_fields', 10, 2 );
	function misha_save_fields( $id, $post ){
		if( !empty( $_POST['main_product_min_quantity_to_all'] ) ) {
			$alg_wc_pq_min_name = 'alg_wc_pq_min_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_min_quantity_to_all', $_POST['main_product_min_quantity_to_all'] );
			update_post_meta( $id, $alg_wc_pq_min_name, $_POST['main_product_min_quantity_to_all'] );
		}  else {
			$alg_wc_pq_min_name = 'alg_wc_pq_min_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_min_quantity_to_all', 'no' );
			update_post_meta( $id, $alg_wc_pq_min_name, 'no' );
		}
		if( !empty( $_POST['main_product_max_quantity_to_all'] ) ) {
			$alg_wc_pq_max_name = 'alg_wc_pq_max_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_max_quantity_to_all', $_POST['main_product_max_quantity_to_all'] );
			update_post_meta( $id, $alg_wc_pq_max_name, $_POST['main_product_max_quantity_to_all'] );
		} else {
			$alg_wc_pq_max_name = 'alg_wc_pq_max_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_max_quantity_to_all', 'no' );
			update_post_meta( $id, $alg_wc_pq_max_name, 'no' );
		}
		if( !empty( $_POST['main_product_step_quantity_to_all'] ) ) {
			$alg_wc_pq_step_name = 'alg_wc_pq_step_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_step_quantity_to_all', $_POST['main_product_step_quantity_to_all'] );
			update_post_meta( $id, $alg_wc_pq_step_name, $_POST['main_product_step_quantity_to_all'] );
		} else {
			$alg_wc_pq_step_name = 'alg_wc_pq_step_'.$id.'_to_all';
			update_post_meta( $id, 'main_product_step_quantity_to_all', 'no' );
			update_post_meta( $id, $alg_wc_pq_step_name, 'no' );
		} 
	}
}

if ( ! function_exists( 'quantity_to_all' ) ) {
	add_action('admin_head','quantity_to_all');

	function quantity_to_all() {
		?>
		<style>
		.custom_quantity_options_group {
			display: none;
		}
		</style>
		<script>
		jQuery(document).ready(function() {
			var product_id = jQuery('#post_ID').val();
			var alg_wc_pq_min_name = 'alg_wc_pq_min_'+product_id+'_to_all';
			var alg_wc_pq_min_to_all = jQuery("input[type='checkbox'][name='main_product_min_quantity_to_all']");
			var alg_wc_pq_min = jQuery("input[type='checkbox'][name='"+alg_wc_pq_min_name+"']");
			alg_wc_pq_min.on('change', function(){
				alg_wc_pq_min_to_all.prop('checked',this.checked);
			});
			var alg_wc_pq_max_name = 'alg_wc_pq_max_'+product_id+'_to_all';
			var alg_wc_pq_max_to_all = jQuery("input[type='checkbox'][name='main_product_max_quantity_to_all']");
			var alg_wc_pq_max = jQuery("input[type='checkbox'][name='"+alg_wc_pq_max_name+"']");
			alg_wc_pq_max.on('change', function(){
				alg_wc_pq_max_to_all.prop('checked',this.checked);
			});
			var alg_wc_pq_step_name = 'alg_wc_pq_step_'+product_id+'_to_all';
			var alg_wc_pq_step_to_all = jQuery("input[type='checkbox'][name='main_product_step_quantity_to_all']");
			var alg_wc_pq_step = jQuery("input[type='checkbox'][name='"+alg_wc_pq_step_name+"']");
			alg_wc_pq_step.on('change', function(){
				alg_wc_pq_step_to_all.prop('checked',this.checked);
			});
		});
		</script>
		<?php
	}
}

/* Added By Me - END */

if ( ! function_exists( 'pq_select_footer_scripts' ) ) :
add_action( 'wp_footer', 'pq_select_footer_scripts', 99 );
function pq_select_footer_scripts(){
  ?>
  <script>
  jQuery(document).ready(function() {
	  var qty_select = jQuery("select.qty");
	  if(qty_select.length > 0)
	  {
		  /*qty_select.on('change', function(){*/
		  jQuery(document).on('change', 'select.qty', function(){
				var input = jQuery(this).closest('div.quantity').find('input.qty');
				if(input.length > 0)
				{
					  sync_classes( input );
					// alert(jQuery(this).val());
					input.val(jQuery(this).val()).change();
				}
				
				var add_to_cart = jQuery(this).closest('div.quantity').siblings( ".add-to-cart" );
				if(add_to_cart.length > 0)
				{
					add_to_cart.find('a.add_to_cart_button').attr( "data-quantity", jQuery(this).val() );
				}
		  });
		  
		  qty_select.change();
		 
	  }
  });
  
  jQuery( '[name="quantity"]' ).on( 'change', function() { 
	var current_val = parseFloat(jQuery(this).val());
	if ( Number.isInteger( current_val ) === false )
	{
		current_val = current_val.toFixed(4);
		current_val = parseFloat(current_val);
		jQuery(this).val( current_val );
	} else {
		current_val = parseInt( current_val );
		jQuery(this).val( current_val );
	}
  });
  
  function sync_classes( input ) {
	  var classList = input.attr('class').split(/\s+/);
		jQuery(classList).each(function( index, item){
			if( !jQuery("select.qty").hasClass(item) ) {
				jQuery("select.qty").addClass(item);
			}
		});
  }
  </script>
  <?php
}
endif;