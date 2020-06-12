<?php
/*
 * this is main settings apply plugin class
*/

/* == Direct access not allowed == */
if( ! defined('ABSPATH' ) ){ exit; }


class WOOH_Settings {

	var $actions, $filters, $product_type;
	private static $ins = null;

	function __construct(){

		/**
		* Lets add the woocommerce relation hooks
		* filters
		* =============== woocommerce filters hooks ===============*/
		
		

		$this -> filters =  array(	'woocommerce_product_add_to_cart_text'	=> 
			array(	'simple'	=> $this -> wooh_get_option('_addtocartsimple'),
				 	'variable'	=> $this -> wooh_get_option('_addtocartvariable'),
				 	'grouped'	=> $this -> wooh_get_option('_addtocartgrouped'),
					'external'	=> $this -> wooh_get_option('_addtocartexternal'),
				),

			'woocommerce_product_single_add_to_cart_text' => $this -> wooh_get_option('_addtocartsingle'),
			'woocommerce_get_availability'	=> $this -> wooh_get_option('_addtocartout'),
			'woocommerce_order_button_text'	=> $this -> wooh_get_option('_orderbuttontext'),
			'woocommerce_my_account_my_orders_title' => $this -> wooh_get_option('_myaccountmyorderstitle'),
			'woocommerce_sale_flash'		=> $this -> wooh_get_option('_saleflash'),
			'woocommerce_short_description'	=> $this -> wooh_get_option('_shortdescription'),
			'loop_shop_per_page'	        => $this -> wooh_get_option('_productdisplayedperpage'),
			'loop_shop_columns'          	=> $this -> wooh_get_option('_productcolumnsdisplayedperpage'),
			'woocommerce_product_thumbnails_columns' => $this -> wooh_get_option('_productthumbnailcolumnsperpage'),
			'woocommerce_output_related_products_args' => $this -> wooh_get_option('_relatedproductperpage'),
									
		);

		
		foreach ($this -> filters as $filter => $label) {
			
			switch ($filter) {
				case 'woocommerce_product_add_to_cart_text':
					add_filter($filter, array($this, 'loop_product_add_to_cart_text'), 10, 2);
					break;

				case 'woocommerce_product_single_add_to_cart_text':
			
					if($label != '')
						add_filter($filter, array($this, 'product_single_add_to_cart_text'));
					break;

				case 'woocommerce_get_availability':
					if($label != '')
						add_filter($filter, array($this, 'check_if_out_of_stock'), 10, 2);
					break;
				
				case 'woocommerce_order_button_text':
					if($label != '')
						add_filter($filter, array($this, 'order_button_text'));
					break;
				case 'woocommerce_my_account_my_orders_title':
					if($label != '')
						add_filter($filter, array($this, 'my_account_my_orders_title'));
					break;
				
				case 'woocommerce_sale_flash':
					if($label != '')
						add_filter($filter, array($this, 'sale_flash'),40,3);
					break;
				
				case 'woocommerce_short_description':
					if($label != '')
						add_filter($filter, array($this, 'short_description'),10,1);
					break;
					
				case 'wc_add_to_cart_message_html':
					if($label != '')
						add_filter($filter, array($this, 'add_to_cart_message'),10,1);
					break;
				case 'loop_shop_per_page':
					if($label != '')
					
						add_filter($filter, array($this, 'wooh_loop_shop_per_page'),40, 1);
					break;
				case 'loop_shop_columns':
					if($label != '')
						add_filter($filter, array($this, 'wooh_loop_shop_columns'), 40);
					break;
				case 'woocommerce_product_thumbnails_columns':
					if($label != '')
						add_filter($filter, array($this, 'wooh_wc_product_thumbnails_columns'), 40, 1);
					break;
				case 'woocommerce_output_related_products_args':
					if($label != '' || $this->wooh_get_option('_relatedproductcolumns') !='')
						add_filter($filter, array($this, 'wooh_output_related_products_args'), 150);
					break;
			}
			
		}

		/* == Products tabs ==*/
		add_filter( 'woocommerce_product_tabs', array($this, 'wooh_product_tabs'), 98 );
		
		
		/* == Text without filters will be changed with 'gettext' filer ==*/
		add_filter( 'gettext', array($this, 'change_non_filter_text'), 20, 3 );


		/**
		* Lets add the woocommerce relation hooks
		* actions
		* =============== woocommerce action hooks ===============*/

		$this -> the_actions = array(
			/* ==== Cart Page ==== */
			'woocommerce_before_cart_table'		   			=> '_beforecarttable',
			'woocommerce_before_cart_contents'	     		=> '_beforecartcontents',
			'woocommerce_cart_contents'						=> '_cartcontents',
			'woocommerce_after_cart_contents'				=> '_aftercartcontents',
			'woocommerce_after_cart_table'					=> '_aftercarttable',
			'woocommerce_after_cart'						=> '_aftercart',
			'woocommerce_proceed_to_checkout'				=> '_proceedtocheckout',
			'woocommerce_cart_coupon'						=> '_cartcoupon',
			'woocommerce_after_cart_totals'					=> '_aftercarttotals',
			'woocommerce_before_cart_totals'				=> '_beforecart_totals',
			'woocommerce_cart_is_empty'						=> '_cartis_empty',
			'woocommerce_before_mini_cart'					=> '_beforeminicart',
			'woocommerce_widget_shopping_cart_before_buttons'	=> '_widgetshoppingcartbeforebuttons',
			'woocommerce_after_mini_cart'					=> '_afterminicart',
			'woocommerce_cart_totals_before_shipping'		=> '_carttotalsbeforeshipping',
			'woocommerce_cart_totals_after_shipping'		=> '_carttotalsaftershipping',
			'woocommerce_cart_totals_before_order_total'	=> '_carttotalsbeforeordertotal',
			'woocommerce_cart_totals_after_order_total'		=> '_carttotalsafterordertotal',
			'woocommerce_before_shipping_calculator'		=> '_beforeshippingcalculator',
			'woocommerce_after_shipping_calculator'			=> '_aftershippingcalculator',

			/* ==== Checkout Page ==== */
			'woocommerce_before_checkout_billing_form'		=> '_beforecheckoutbillingform',
			'woocommerce_after_checkout_billing_form'		=> '_aftercheckoutbillingform',
			'woocommerce_before_checkout_registration_form'	=> '_beforecheckoutregistrationform',
			'woocommerce_after_checkout_registration_form'	=> '_aftercheckoutregistrationform',
			'woocommerce_before_checkout_form'				=> '_beforecheckoutform',
			'woocommerce_checkout_before_customer_details'	=> '_checkoutbeforecustomerdetails',
			'woocommerce_checkout_billing'					=> '_checkoutbilling',
			'woocommerce_checkout_shipping'					=> '_checkoutshipping',
			'woocommerce_checkout_after_customer_details'	=> '_checkoutaftercustomerdetails',
			//'woocommerce_checkout_order_review'			=> '_checkoutorderreview',
			'woocommerce_after_checkout_form'				=> '_aftercheckoutform',
			'woocommerce_before_checkout_shipping_form'		=> '_beforecheckoutshippingform',
			'woocommerce_after_checkout_shipping_form'		=> '_aftercheckoutshippingform',
			'woocommerce_before_order_notes'				=> '_beforeordernotes',
			'woocommerce_after_order_notes'					=> '_afterordernotes',
			'woocommerce_review_order_before_shipping'		=> '_revieworderbeforeshipping',
			'woocommerce_review_order_after_shipping'		=> '_revieworderaftershipping',
			'woocommerce_review_order_before_order_total'	=> '_revieworderbeforeordertotal',
			'woocommerce_review_order_after_order_total'	=> '_revieworderafterordertotal',
			'woocommerce_review_order_before_cart_contents'	=> '_revieworderbeforecartcontents',
			'woocommerce_review_order_after_cart_contents'	=> '_revieworderaftercartcontents',
			'woocommerce_review_order_before_payment'		=> '_revieworderbeforepayment',
			'woocommerce_review_order_before_submit'		=> '_revieworderbeforesubmit',
			'woocommerce_review_order_after_submit'			=> '_revieworderaftersubmit',
			'woocommerce_review_order_after_payment'		=> '_revieworderafterpayment',	
			'woocommerce_checkout_before_terms_and_conditions'		=> '_beforetermcondition',	
			'woocommerce_checkout_after_terms_and_conditions'		=> '_aftertermcondition',	

			/* ==== My Account Page ==== */
			'woocommerce_before_customer_login_form'	=> '_beforecustomerloginform',	
			'woocommerce_login_form_start'				=> '_loginformstart',	
			'woocommerce_login_form'					=> '_loginform',	
			'woocommerce_login_form_end'				=> '_loginformend',	
			'woocommerce_register_form_start'			=> '_registerformstart',	
			'woocommerce_register_form'					=> '_registerform',	
			'woocommerce_register_form_end'				=> '_registerformend',	
			'woocommerce_after_customer_login_form'		=> '_aftercustomerloginform',	
			'woocommerce_before_my_account'				=> '_beforemyaccount',	
			'woocommerce_after_my_account'				=> '_aftermyaccount',

			/* ==== Order Details Page ==== */
			'woocommerce_order_details_after_order_table_items'	=> '_orderitemstable',
			'woocommerce_order_details_after_order_table'	    => '_orderdetailsafterordertable',
			'woocommerce_order_details_after_customer_details'	=> '_orderdetailsaftercustomerdetails',

			/* ==== Single Product Page ==== */
			'woocommerce_before_add_to_cart_button'			=> '_beforeaddtocartbutton',
			'woocommerce_after_add_to_cart_button'			=> '_afteraddtocartbutton',
			'woocommerce_before_add_to_cart_form'			=> '_beforeaddtocartform',
			'woocommerce_grouped_product_list_before_price'	=> '_groupedproductlistbeforeprice',
			'woocommerce_after_add_to_cart_form'			=> '_afteraddtocartform',
			'woocommerce_product_meta_start'				=> '_productmetastart',
			'woocommerce_product_meta_end'					=> '_productmetaend',
			'woocommerce_product_thumbnails'				=> '_productfeaturedimage',

			/* ==== Email Template ==== */
			'woocommerce_email_header'				=> '_emailheader',
			'woocommerce_email_before_order_table'	=> '_emailbeforeordertable',
			'woocommerce_email_after_order_table'	=> '_emailafterordertable',
			'woocommerce_email_order_meta'			=> '_emailordermeta',
			'woocommerce_email_footer'				=> '_emailfooter',


			'woocommerce_share'	=> '_share',

		);
		
		if($this->wooh_get_option('_disabledrelatedproduct') == 'yes'){
				remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
		}

		foreach ($this -> the_actions as $filter => $key) {
			add_action($filter, array($this, 'render_text'));
		}

		/*
		 * hooking up scripts for front-end
		*/
		add_action('wp_enqueue_scripts', array($this, 'load_scripts_styles'));


		add_action( 'wp_ajax_wooh_send_inquiry' ,array($this, 'wooh_send_inquiry') );
		add_action( 'wp_ajax_nopriv_wooh_send_inquiry', array($this, 'wooh_send_inquiry') );
		
		add_action( 'wp_ajax_wooh_action_settings_save_frontend' ,array($this, 'wooh_action_settings_save_frontend') );
		add_action( 'wp_ajax_nopriv_wooh_action_settings_save_frontend' ,array($this, 'wooh_action_settings_save_frontend') );
		
		
		

	}

	public static function get_instance() {
	    // create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}

	/*==== Woocommerce filters callbacks start  =====*/
	/*== to render labels on loop ==*/
	function loop_product_add_to_cart_text($text, $product) {

		$product_type = $product->get_type();

		if($this -> filters['woocommerce_product_add_to_cart_text'][$product_type] != '')
			return sprintf( __("%s", 'wooh'), wooh_wpml_translate($this->filters['woocommerce_product_add_to_cart_text'][$product_type]));
		else
			return $text;
	}

	/*== render label on single product page ==*/
	function product_single_add_to_cart_text(){
		
		
		return	sprintf( __("%s", 'wooh'), wooh_wpml_translate($this -> filters['woocommerce_product_single_add_to_cart_text']));
		
	}


	/*== render label on single product page when out of stock ==*/
	function check_if_out_of_stock($availability, $_product) {
  
	    if ( !$_product->is_in_stock() ) {

			$out_of_stock = wooh_wpml_translate($this -> filters['woocommerce_get_availability']);
	    	$availability['availability'] = sprintf( __("%s", 'wooh'), $out_of_stock);	        					
	    	return $availability;
	    	
	    }
	}

	/*== render label on place order button on checkout page ==*/
	function order_button_text(){

		return wooh_wpml_translate($this -> filters['woocommerce_order_button_text']);
	}

	/*== render recent products text on my account page ==*/
	function my_account_my_orders_title(){

		return wooh_wpml_translate($this -> filters['woocommerce_my_account_my_orders_title']);
	}
	
	/*== change sale text on loop ==*/
	function sale_flash($content, $post, $product){
	
		$sale_flash = wooh_wpml_translate($this -> filters['woocommerce_sale_flash']);
	
		return ! empty( $sale_flash ) ? "<span class='onsale'>{$sale_flash}</span>" : $content;
	}	


	/*== Product short description ==*/
	function short_description($description){

		$description = $description. ' '. wooh_wpml_translate($this -> filters['woocommerce_short_description']);
		return sprintf( __("%s", 'wooh'), $description );
	}
	
	/*== Product add to cart message ==*/
	function add_to_cart_message( $message ){

		if( isset($this -> filters['wc_add_to_cart_message_html']) && $this -> filters['wc_add_to_cart_message_html'] !=''){
			
			$message = wooh_wpml_translate($this -> filters['wc_add_to_cart_message_html']);
		}
		
		return sprintf( __("%s", 'wooh'), $message );
	}
	
	
	function wooh_loop_shop_per_page($e4olve_loop_shop_per_page){
		
	
		if( isset($this -> filters['loop_shop_per_page']) && $this -> filters['loop_shop_per_page'] !=''){
			
			$message = wooh_wpml_translate($this -> filters['loop_shop_per_page']);
			
			$evolve_loop_shop_per_page = $message;
		}
		
	
		return $evolve_loop_shop_per_page;
	}
	
	function wooh_loop_shop_columns(){
		
		if( isset($this -> filters['loop_shop_columns']) && $this -> filters['loop_shop_columns'] !=''){
			
			$evolve_loop_shop_per_colums = wooh_wpml_translate($this -> filters['loop_shop_columns']);
		}
	
		return $evolve_loop_shop_per_colums;
	}
	
	function wooh_wc_product_thumbnails_columns($int){
		
		if( isset($this -> filters['woocommerce_product_thumbnails_columns']) && $this -> filters['woocommerce_product_thumbnails_columns'] !=''){
			
			$thumbnails_columns = wooh_wpml_translate($this -> filters['woocommerce_product_thumbnails_columns']);
			$int = $thumbnails_columns;
		}
	
		return $int;
	}
	
	function wooh_output_related_products_args(){
			
		$posts_per_page = 4;
		$columns		= $this->wooh_get_option('_relatedproductcolumns');
			
		if( isset($this -> filters['woocommerce_output_related_products_args']) && $this -> filters['woocommerce_output_related_products_args'] !=''){
			
			$posts_per_page = wooh_wpml_translate($this -> filters['woocommerce_output_related_products_args']);
		}
		
		return array(
				'posts_per_page' => $posts_per_page,
				'columns'        => $columns,
			);
	}
	

	/* == setting woo tabs ==*/
	function wooh_product_tabs( $tabs ){
		
		
		global $product;
		
		/**
		 * we will use it for inqury form too
		*/
		 
		$defined_tabs = get_option('wooh_tabs');
		
		$disable_default_checked = get_post_meta( $product->get_id(), '_disable_default_tabs', true );
		
		
		//if default tabe are disabled
		
		$priority = 1;
		if($defined_tabs) {
			foreach ($defined_tabs as $tab){

				$tab_id       = sanitize_key($tab['title']);
				$tab_title    = esc_html($tab['title']);
				$default_desc = esc_html($tab['default_desc']);
				
				$tab_title = wooh_wpml_translate($tab_title);
				
				// Adds the new tab

				$tabs[$tab_id] = array(
						'title' 	=> sprintf(__( '%s', 'wooh'), $tab_title),
						'default_desc'=> sprintf(__( '%s', 'wooh'), $default_desc),
						'priority' 	=> ($priority * 100),
						'tab_id'	=> $tab_id,
						'callback' 	=> array($this, "wooh_product_tabs_contents"),
				);

					
				$priority++;
			}
		}
		
		if($disable_default_checked == 'yes'){
			
			unset( $tabs['description'] );      	// Remove the description tab
    		unset( $tabs['reviews'] ); 			// Remove the reviews tab
    		unset( $tabs['additional_information'] );
    		
		}
		
		
		/* ==Inquiry Tab Settings ==*/
		
		 
		if($this -> wooh_get_option('_enable_enquiry') == 'yes'){

			if ($this -> wooh_get_option('_enquiry_title')) {
				$enq_title = $this -> wooh_get_option('_enquiry_title');
			} else {
				$enq_title = 'Enquiry';
			}
			
			
			$tabs['inquiry_form'] = array(
					'title' 	=> sprintf(__( '%s', 'wooh'), $enq_title ),
					'priority' 	=> ($priority * 100),
					'callback' 	=> array($this, "wooh_product_enquiry_form"),
			);
		}
		
		return $tabs;
	}
	

	/*==== Woocommerce filters callbacks end =====*/


	
	/*==== Woocommerce action callbacks =====*/
	function render_text(){
			
		global $product;
		$current_filter = current_filter();
		
		$the_option_key = $this -> the_actions[$current_filter];
		$message        = $this -> wooh_get_option( $the_option_key );
		
		$action_enable = isset($_GET['is_active'])? $_GET['is_active'] : '';
		
		
		if($message){
			
			$message_arr = explode('|', $message);
			
			$plain_text = trim($message_arr[0]);
			
			if(isset($message_arr[1])){

				$ids_text = trim($message_arr[1]);
				$the_IDs  = explode(',', $ids_text);

				if( in_array( $product -> get_id(), $the_IDs) ){
					
						$content      = apply_filters('the_content', $plain_text);
						$action_html = '';
						
					if($action_enable == 'yes'){
						$action_html = $this->wooh_edit_action_textarea($the_option_key, $current_filter,$message);
					
					}

					printf( __("%s", 'wooh'), $content.=$action_html);
				}
				
			}else {
				
				$content = apply_filters('the_content', wooh_wpml_translate($message) );
				$action_html = '';
				
				if($action_enable == 'yes'){
					$action_html = $this->wooh_edit_action_textarea($the_option_key, $current_filter,$message);
					
				}
					printf( __("%s", 'wooh'), $content.=$action_html);
			}
		}
	}

	/*==== Woocommerce action callbacks =====*/
	
	
	function wooh_edit_action_textarea($the_option_key, $current_filter,$message){
		
		$label = 'Show text on '.str_replace('_', '  ', $current_filter);
		$label = str_replace('woocommerce','  ', $label);
		
		$html = '';
		$html = '<div class="wooh-action-edit" data-key =" '.$the_option_key.'; ">';
		$html .= '<label>'.$label.'</label>';
		$html .= '<textarea placeholder="Type here..." cols="45" rows="3" >'.wooh_wpml_translate($message).'</textarea>';
		$html .= '<input type="button" class="btn btn-info input-action-btn" value="'.esc_attr('Save Setting', 'wooh').'"/></br>';
		$html .= '</div>';
		
		return  $html;
	}


	/*== new tab contents ==*/
	function wooh_product_tabs_contents($key, $tab){
		
		global $product;
		
		// wooh_pa($tab);
		$default_desc = isset($tab['default_desc']) ? stripslashes($tab['default_desc']) : '';
		
		$content = get_post_meta($product->get_id(), "_tab_{$key}", true);
		
		if( empty( $content ) ){
			$content = $default_desc;
		}
		
		echo apply_filters('the_content', $content);
	}
	
	/*== this is rendering the enquiry form ==*/
	function wooh_product_enquiry_form(){
		
		global $product;
		
		ob_start ();
			
		wooh_load_templates( 'admin/inquiry-tab.php', array('product_id'=>$product->get_id()) );
			
		$output_string = ob_get_contents ();
		ob_end_clean ();
		
		echo $output_string;
	}
	
	
	function save_inquiry_settings(){

		update_option('wooh_inquiry', $_REQUEST['inquiry_data']);

	}

	function change_non_filter_text( $translated_text, $text, $domain ) {

	    switch ( $translated_text ) {
	
            case 'Proceed to checkout' :

				$proceedcheckout = $this->wooh_get_option('_proceedtocheckouttext');
				
				if(empty($proceedcheckout)) $proceedcheckout="Proceed to checkout";
                
                $translated_text = wooh_wpml_translate($proceedcheckout);
                break;

        }
        
        
        // Since 2.6 Any text can be changable
        $anytexts = $this->wooh_get_option('_anytext');
        $anytexts = preg_split('/\r\n|[\r\n]/', $anytexts);
        // wooh_pa($anytexts);
        
        if( count($anytexts) > 0 ) {
        	
        	foreach( $anytexts as $text ) {
        		
        		$text_line = explode('|', $text);
        		$text_line = array_map('trim', $text_line);
        		$current_t = isset($text_line[0]) ? $text_line[0] : '';
        		$new_t		= isset($text_line[1]) ? $text_line[1] : '';
        		
        		if( $current_t == $translated_text ) {
        			$translated_text = $new_t;
        		}
        		
        		// woostore_pa($text_line);
        	}
        }
        
        
        
	    return $translated_text;
	}
	


	/*==  this function is getting single ==*/
	function wooh_get_option($key){
	
		return $the_option =  get_option('wooh'.$key);
		
	}


	function load_scripts_styles() {
		
		// if( ! is_product() ) return;

		wp_enqueue_style('wooh-frontend-style', WOOH_URL.'/css/inquiry-form.css');
		wp_enqueue_style('wooh-sweetalet', WOOH_URL.'/css/sweetalert.css');
		
		
		wp_enqueue_script('wooh-scripts-sweetalert', WOOH_URL.'/js/sweetalert.js', true);
		wp_enqueue_script('wooh-frontend-js' , WOOH_URL .'/js/wooh-front-end.js', array('jquery'));
		
		$js_vars = array('ajaxurl' => admin_url( 'admin-ajax.php', (is_ssl() ? 'https' : 'http') ));
		wp_localize_script('wooh-frontend-js', 'wooh_vars', $js_vars);
	}

	/*== this is sending mail according to inquiry ==*/
	function wooh_send_inquiry(){
		
		if(isset($_REQUEST)){
		
			$wooh_product_id   = sanitize_key($_REQUEST['wooh_product_id']);
			$wooh_sender 	   = esc_textarea($_REQUEST['wooh_sender']);
			$wooh_sender_email = sanitize_email($_REQUEST['wooh_sender_email']);
			$wooh_enquiry_text = esc_textarea($_REQUEST['wooh_enquiry_text']);
		
			$site_title   = get_bloginfo();
			$admin_emails = $this -> wooh_get_option('_enquiry_receivers');

			$headers[] = "From: {$site_title}<{$wooh_sender_email}>";
			$headers[] = "Content-Type: text/html";
			$headers[] = "MIME-Version: 1.0\r\n";
		
			$product		= wc_get_product($wooh_product_id);
			$product_title = $product->get_title();

			$subject = "Enquiry About {$product_title}";

			$message = $wooh_enquiry_text;

			$message .= '<br><br><b>From: </b>'.$wooh_sender.'<br><b>Email: </b>'.$wooh_sender_email;
			

			if(wp_mail($admin_emails, $subject, $message, $headers)) {
				
				if ($this -> wooh_get_option('_enquiry_success_message')) {
					echo $this -> wooh_get_option('_enquiry_success_message');
				} else {
					echo _e( 'Enquiry Sent!', 'nm-woostore' );
				}
				
			}
			else {
				echo _e( 'Failed!', 'nm-woostore' );
			}

			die(0);

		}	
	}
	
	
	
	function wooh_action_settings_save_frontend(){
		
		if(isset($_REQUEST)){
		
			$wooh_key     = sanitize_key($_REQUEST['wooh_key']);
			$text_value   = $_REQUEST['text_value'];
			
			$key = 'wooh'.$wooh_key;
			
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
	    		case 'wooh_anytext':
	    			
					$text_value =  wp_kses_post($text_value);
				break;
				
				default:
					$text_value =  sanitize_text_field($text_value);
				break;
	    			
	    	}
	      
			if(update_option($key,$text_value)){
				remove_query_arg('is_active');
				wp_send_json('success');
			}
			
		}
		
	}
		
}

setting_class();
function setting_class() {
	return WOOH_Settings::get_instance();
}