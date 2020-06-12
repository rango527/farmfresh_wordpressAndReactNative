<?php
/*
 * Admin side function control
*/

/* == Direct access not allowed == */
if( ! defined('ABSPATH' ) ){ exit; }


class WOOH_Admin{
	
	private static $ins = null;

	function __construct(){

		/* == Woohero menu added in menu ==*/
		add_action('admin_menu', array($this, 'wooh_admin_option_settings')); 
		
		if(class_exists('WOOH_MAIN_PRO')){
			
			/* == admin bar menu added ==*/
			add_action( 'wp_print_styles', array( $this, 'wooh_add_builder_edit_button_css' ) );
			add_action( 'admin_print_styles', array( $this, 'wooh_add_builder_edit_button_css' ) );
			add_action( 'admin_bar_menu',   array($this, 'wooh_admin_bar_menu'), 1000 );
			
		}
		
		/* == Woohero save settings hook ==*/
		add_action( 'wp_ajax_wooh_save_settings', array($this, 'save_settings'));
		add_action( 'wp_ajax_nopriv_wooh_save_settings', array($this, 'save_settings'));
		
		/* == Woohero save Product tabs hook ==*/
		add_action( 'wp_ajax_wooh_save_tabs' ,array($this, 'wooh_product_save_tabs') );
		add_action( 'save_post', array($this, 'wooh_save_tabs') );
	}

	

	/*=== Woohero menu added in menu function ===*/
	function wooh_admin_option_settings(){

    	add_submenu_page('woocommerce','WooHero', 'WooHero', 'manage_options', 'wooh_settings', array($this , 'wooh_admin_option_settings_template'));

	}

	function wooh_admin_option_settings_template(){
		
		$this->wooh_admin_settings_script();
		
		wooh_load_templates("admin/settings.php");
	}
	
	/*=== Wooh all admin side script ===*/
	function wooh_admin_settings_script(){
	
		/*== CSS Files ==*/
		wp_enqueue_style('wooh-style', WOOH_URL.'/css/style.css');
		wp_enqueue_style('wooh-sweetalet', WOOH_URL.'/css/sweetalert.css');
		wp_enqueue_style('wooh-easytabs', WOOH_URL.'/js/easytabs/tabs.css');
		wp_enqueue_style('wooh-jqueryui-css', WOOH_URL.'/js/jqueryui/jquery-ui.css');
	
	
		/*== JS Files ==*/
		wp_enqueue_script('wooh-scripts-sweetalert', WOOH_URL.'/js/sweetalert.js', true);
		wp_enqueue_script('wooh-scripts-admin', WOOH_URL.'/js/admin.js', true);
		wp_enqueue_script('wwoh-scripts-global', WOOH_URL.'/js/nm-global.js', true);
		// wp_enqueue_script('wooh-scripts-block-admin', WOOH_URL.'/js/block-ui.js', true);
		wp_enqueue_script('wooh-tabs', WOOH_URL.'/js/easytabs/jquery.easytabs.js', true);
		wp_enqueue_script('wooh-jquery-ui', WOOH_URL.'/js/jqueryui/jquery-ui.js', true);
		wp_enqueue_script('wooh-new-scripts-admin', WOOH_URL.'/js/wooh-admin.js',  array('jquery'), WOOH_VERSION,  true);
		
		$js_vars = array('ajaxurl' => admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http') ));
		wp_localize_script('wooh-new-scripts-admin', 'wooh_vars', $js_vars);
	}

	
	/*=== saving admin setting in wp option data table ===*/
  	function save_settings(){
  		
	    $nonce = $_REQUEST['_wpnonce_id'];
	    
	    if ( !wp_verify_nonce( $nonce, 'wooh_nonce' ) ){
			
			$response = array( 'status'   => 'error',
	                           'message'  => __('Sorry for security reason.', 'wooh'),
	                    );  
	    }else{

		      $settings = array();
		      
		      if(!array_key_exists('wooh_enable_enquiry', $_REQUEST)){
		      		
		      		$settings['wooh_enable_enquiry'] = 'no';
		      }
		      if(!array_key_exists('wooh_disabledrelatedproduct', $_REQUEST)){
		      		
		      		$settings['wooh_disabledrelatedproduct'] = 'no';
		      }
	      
	    		foreach($_REQUEST as $key => $value) {
	      
	    	switch($key) {
	    		
	    		case 'wooh_share':
	    		case 'wooh_orderitemstable':
	    		case 'wooh_beforecarttable':
	    		case 'wooh_beforecartcontents':
	    		case 'wooh_cartcontents':
	    		case 'wooh_aftercartcontents':
	    		case 'wooh_aftercarttable':
	    		case 'wooh_aftercart':
	    		case 'wooh_proceedtocheckout':
	    		case 'wooh_cartcoupon':
	    		case 'wooh_beforecarttotals':
	    		case 'wooh_aftercarttotals':
	    		case 'wooh_beforeminicart':
	    		case 'wooh_widgetshoppingcartbeforebuttons':
	    		case 'wooh_afterminicart':
	    		case 'wooh_carttotalsbeforeshipping':
	    		case 'wooh_carttotalsaftershipping':
	    		case 'wooh_carttotalsafterordertotal':
	    		case 'wooh_carttotalsbeforeordertotal':
	    		case 'wooh_beforeshippingcalculator':
	    		case 'wooh_aftershippingcalculator':
	    		case 'wooh_beforeaddtocartbutton':
	    		case 'wooh_afteraddtocartbutton':
	    		case 'wooh_productmetastart':
	    		case 'wooh_beforeaddtocartform':
	    		case 'wooh_afteraddtocartform':
	    		case 'wooh_productmetaend':
	    		case 'wooh_productfeaturedimage':
	    		case 'wooh_addtocartmessage':
	    		case 'wooh_beforecheckoutbillingform':
	    		case 'wooh_aftercheckoutbillingform':
	    		case 'wooh_beforecheckoutregistrationform':
	    		case 'wooh_aftercheckoutregistrationform':
	    		case 'wooh_beforecheckoutform':
	    		case 'wooh_checkoutbeforecustomerdetails':
	    		case 'wooh_checkoutbilling':
	    		case 'wooh_checkoutshipping':
	    		case 'wooh_checkoutaftercustomerdetails':
	    		case 'wooh_aftercheckoutform':
	    		case 'wooh_beforecheckoutshippingform':
	    		case 'wooh_aftercheckoutshippingform':
	    		case 'wooh_beforeordernotes':
	    		case 'wooh_revieworderaftershipping':
	    		case 'wooh_afterordernotes':
	    		case 'wooh_revieworderbeforeshipping':
	    		case 'wooh_revieworderbeforeordertotal':
	    		case 'wooh_revieworderafterordertotal':
	    		case 'wooh_revieworderbeforecartcontents':
	    		case 'wooh_revieworderaftercartcontents':
	    		case 'wooh_revieworderbeforepayment':
	    		case 'wooh_revieworderafterpayment':
	    		case 'wooh_revieworderbeforesubmit':
	    		case 'wooh_revieworderaftersubmit':
	    		case 'wooh_beforecustomerloginform':
	    		case 'wooh_loginformstart':
	    		case 'wooh_loginform':
	    		case 'wooh_loginformend':
	    		case 'wooh_registerformstart':
	    		case 'wooh_registerform':
	    		case 'wooh_registerformend':
	    		case 'wooh_aftercustomerloginform':
	    		case 'wooh_beforemyaccount':
	    		case 'wooh_aftermyaccount':
	    		case 'wooh_myaccountmyorderstitle':
	    		case 'wooh_orderdetailsafterordertable':
	    		case 'wooh_orderdetailsaftercustomerdetails':
	    		case 'wooh_emailheader':
	    		case 'wooh_emailbeforeordertable':
	    		case 'wooh_emailafterordertable':
	    		case 'wooh_emailordermeta':
	    		case 'wooh_emailfooter':
	    		case 'wooh_shortdescription':
	    		case 'wooh_beforetermcondition':
	    		case 'wooh_beforetermcondition':
	    		case 'wooh_anytext':
	    
					$settings[$key] =  wp_kses_post($value);
				break;
				
				default:
					$settings[$key] =  sanitize_text_field($value);
				break;
	    			
	    	}
	      }
	      
	      
		      foreach ($settings as $s_key => $s_value) {
		        update_option($s_key, $s_value);
		        
		      }
	      
	      $response = array( 'status'   => 'success',
	                          'message'  => __('All options are updated.', 'wooh'),
	                      );
	    }
	    
	      wp_send_json( $response );
	}
	
	/* == Woohero save Product tabs hook ==*/
	function wooh_product_save_tabs(){
		
		if(isset( $_REQUEST)) {
			
			update_option('wooh_tabs', $_REQUEST['tabs']);
			wp_send_json('success');
			
		}

	}
	
	
	/*=== rendering the tabs in product post Called from pro ===*/
	function wooh_product_tabs_metabox($post_type, $post){
		
		if( $post_type == 'product' )
			add_meta_box( 'wooh-meta-box', __( 'Additional Product Tabs', 'nm-woostore' ), array($this,'render_woo_product_tabs'), 'product', 'normal', 'default' );
	}
	
	function render_woo_product_tabs($post){
		
		// create a nonce field
		$defined_tabs = get_option('wooh_tabs');
		
		if(!$defined_tabs){
			echo "Tabs not defined yet. ";
			echo '<a href="'.admin_url('?page=wooh_settings#product-tabs').'">Click to add Tabs</a>';
			return;
		}
		
		wp_nonce_field( 'wooh_tabs', 'wooh_tabs_metabox_nonce' );
		
		echo '<div class="panel-wrap product_data woocommerce">';
		$disable_default_checked = get_post_meta( $post->ID, '_disable_default_tabs', true );		
		?>
		
	
		<div class="wc-tabs-back"></div>
	
		<?php
		if($defined_tabs){
			echo "<style>ul.wc-tabs:after {height: auto !important;}</style>";			
			echo '<ul class="product_data_tabs wc-tabs" style="margin:0px; padding: 0px;">';
			foreach ($defined_tabs as $tab):
			$tab_id = sanitize_key($tab['title']);
			$tab_title = esc_html($tab['title']);
			
			?>
			
				<li class="<?php echo $tab_id;?>_tab">
				<a href="#tab_<?php echo $tab_id;?>"><?php echo $tab_title; ?></a></li>
				
			<?php endforeach;
			echo '</ul>';
		
			if($defined_tabs) {
				foreach ($defined_tabs as $tab):
				$tab_id = sanitize_key($tab['title']);
				$tab_title = esc_html($tab['title']);
				
				$settings = array(
						'textarea_name'	=> $tab_id,
						'quicktags' 	=> array( 'buttons' => 'em,strong,link' ),
						'tinymce' 	=> array(
								'theme_advanced_buttons1' => 'bold,italic,strikethrough,separator,bullist,numlist,separator,blockquote,separator,justifyleft,justifycenter,justifyright,separator,link,unlink,separator,undo,redo,separator',
								'theme_advanced_buttons2' => '',
						),
						'editor_css'	=> '<style>#wp-excerpt-editor-container .wp-editor-area{height:175px; width:100%;}</style>',
						'wpautop' => false,
				);
		
				$tab_content = get_post_meta( $post->ID, "_tab_{$tab_id}", true);
		
				?>
				
				<div class="panel woocommerce_options_panel" id="tab_<?php echo $tab_id;?>" style="float:right;width:75%; margin-top:-26px;">
					<div class="options_group">
						<p>
							<?php wp_editor( htmlspecialchars_decode( $tab_content ), $tab_id, $settings );?>
					    </p>
					</div>
				</div>
				
						    
				<?php
				endforeach;
			}
		}
		echo '<div class="clear"></div>';
		echo '</div>'; ?>
		<span style="font-weight: bold;"> 
			<label for="_disable_default_tabs" class="tips" style="display: inline;" data-tip="<?php _e('It will disable the default product tabs otherwise following tabs will be append to default tabs', 'nm-woostore');?>"><?php _e('Disable default tabs?', 'nm-woostore');?>: 
			<input type="checkbox" name="_disable_default_tabs" id="_disable_default_tabs" <?php checked( $disable_default_checked, 'yes', true);?>></label>
		</span> <?php
	
	}
	
	/**
	 * When the post is saved, saves our custom data.
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	function wooh_save_tabs( $post_id ) {
	
		/*
		 * We need to verify this came from our screen and with proper authorization,
		* because the save_post action can be triggered at other times.
		*/
	
		// Check if our nonce is set.
		if ( ! isset( $_POST['wooh_tabs_metabox_nonce'] ) ) {
			return;
		}
	
		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $_POST['wooh_tabs_metabox_nonce'], 'wooh_tabs' ) ) {
			return;
		}
	
		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
	
		// Check the user's permissions.
		if ( isset( $_POST['post_type'] ) && 'product' == $_POST['post_type'] ) {
	
			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}
	
		} else {
	
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}
	
		/* OK, it's safe for us to save the data now. */
		// woostore_pa($_POST); exit;
		
		$disable_default = isset( $_POST['_disable_default_tabs'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_disable_default_tabs', wc_clean( $disable_default ) );
		
		$defined_tabs = get_option('wooh_tabs');
		
		foreach( $defined_tabs as $tab){

			$tab_id = sanitize_key($tab['title']);
			$tab_title = esc_html($tab['title']);
			
			// Make sure that it is set.
			if ( isset( $_POST[$tab_id] ) ) {

				// Update the meta field in the database.
				update_post_meta( $post_id, "_tab_{$tab_id}", $_POST[$tab_id] );
			}
		}
	}
	
	
	/*== Showing Woohero Edit on Product Page ==*/
	function wooh_admin_bar_menu($wp_admin_bar) {
		
		if(is_admin()) return '';
		
		if( is_shop() ) return '';
	
		global $wp_admin_bar, $product;
	
		$css = '';
		$wooh_setting_url = admin_url('cart');
		$wooh_setting_url = add_query_arg(array('is_active'=>'yes'));
	    $preview_url      = strtok($_SERVER["REQUEST_URI"],'?');
	    $is_active        = isset($_REQUEST['is_active']) ? $_REQUEST['is_active'] : 'no';
		
		$bar_title = '<span class="ab-icon"></span><span class="ab-label">' . __( 'Enable WooHero ' , 'wooh' ) . '</span>';
		
		if($is_active == 'yes') {
			$bar_title = '<span class="ab-icon"></span><span class="ab-label display-woohero">' . __( 'Disable WooHero' , 'simply-show-hooks' ) . '</span>';
			$css = "ssh-hooks-on ssh-hooks-sidebar";
			$wooh_setting_url = $preview_url;
		 }
		
	
		$wp_admin_bar->add_menu( array(
			'title'  => sprintf(__( "%s", "wooh"), $bar_title ),
			'id'     => 'wooh-main-menu',
			'href'  => $wooh_setting_url,
			'parent'	=> false,

		) );
		
		$wp_admin_bar->add_menu(array(
			'title'		=> 'Preview Changes',
			'id'		=> 'wooh-setting-pre',
			'parent'	=> 'wooh-main-menu',
			'href'		=> $preview_url,
			'meta'		=> array( 'target' => '_blank',
								'class' => '' )
		));
		
	}

	function wooh_add_builder_edit_button_css() {
		?>
		<style>
		#wp-admin-bar-wooh-main-menu .ab-icon:before{
			font-family: "dashicons" !important;
			content: "\f309" !important;
			font-size: 20px !important;
		}
		</style>
		<?php
	}
	
	
	
	
	public static function get_instance() {
	    // create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}

}

admin_class();
function admin_class() {
	return WOOH_Admin::get_instance();
}