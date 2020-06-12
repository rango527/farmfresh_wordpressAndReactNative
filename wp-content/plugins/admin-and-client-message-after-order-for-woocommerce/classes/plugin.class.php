<?php
/*
 * this is main plugin class
*/


/* ======= the model main class =========== */
if(!class_exists('NM_Framwork_V1_wooconvo')){
	$_framework = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'nm-framework.php';
	if( file_exists($_framework))
		include_once($_framework);
	else
		die('Reen, Reen, BUMP! not found '.$_framework);
}


/*
 * [1]
* TODO: change the class name of your plugin
*/
class NM_PLUGIN_WooConvo extends NM_Framwork_V1_wooconvo {

	static $tbl_convo = 'nm_wooconvo';

	var $order_id, $order_valid, $upload_dir_name;
	
	var $inputs;
	/*
	 * plugin constructur
	*/
	function __construct(){

		//setting plugin meta saved in config.php
		$this -> plugin_meta = get_plugin_meta_wooconvo();

		//getting saved settings
		$this -> plugin_settings = get_option($this->plugin_meta['shortname'].'_settings');

		//setting current order id and order_email
		$this -> order_id = isset($_REQUEST['order']) ? intval($_REQUEST['order']) : true;

		if((isset($_REQUEST['email'] ) && $_REQUEST['email'] == '') || (isset($_REQUEST['email']) && $_REQUEST['email'] != get_post_meta($this -> order_id, '_billing_email', true))){
			$this -> order_valid = false;
		}
		else{
			$this -> order_valid = true;
		}

		//file upload dir name
		$this -> upload_dir_name = 'order_files';

		// populating $inputs with NM_Inputs_wooconvo object
		$this->inputs = $this->get_all_inputs ();
		
		/*
		 * [2]
		* TODO: update scripts array for SHIPPED scripts
		* only use handlers
		*/
		//setting shipped scripts
		$this -> wp_shipped_scripts = array('jquery');


		/*
		 * [3]
		* TODO: update scripts array for custom scripts/styles
		*/
		//setting plugin settings
		$this -> plugin_scripts =  array(
				array(	'script_name'	=> 'convojs',
						'script_source'	=> '/js/convo.js',
						'localized'		=> true,
						'type'			=> 'js',
						'depends'	=> array('jquery-effects-core', 'jquery-effects-shake'),
				),

				array(	'script_name'	=> 'convo-css',
						'script_source'	=> '/css/wooconvo.css',
						'localized'		=> false,
						'type'			=> 'style',
						'depends' => '',
				),
		);


		/*
		 * update scripts array for custom scripts/styles
		*/
		//setting plugin settings
		$this -> plugin_scripts_admin =  array(
				array(	'script_name'	=> 'scripts_admin',
						'script_source'	=> '/js/convo.js',
						'localized'		=> true,
						'type'			=> 'js',
						'depends'	=> array('jquery-effects-core', 'jquery-effects-shake'),
				),
				array(	'script_name'	=> 'modal-js',
						'script_source'	=> '/js/modal/iziModal.min.js',
						'localized'		=> false,
						'type'			=> 'js',
						'depends' => array (
								'jquery',
						) 
				),
				array(	'script_name'	=> 'modal-css',
						'script_source'	=> '/js/modal/iziModal.min.css',
						'localized'		=> false,
						'type'			=> 'style',
				),
			
				array(	'script_name'	=> 'convo-css',
						'script_source'	=> '/css/wooconvo.css',
						'localized'		=> false,
						'type'			=> 'style'
				),
				array(	'script_name'	=> 'convo-css-fontawesome',
						'script_source'	=> '/css/font-awesome/css/font-awesome.css',
						'localized'		=> false,
						'type'			=> 'style'
				),
				
				
		);
		
		$app_text = $this->get_option('_app_text');  
	    $app_text = $app_text != '' ? $app_text : 'Approved';
	
	    $app_alert_text = $this->get_option('_app_alert_text');  
	    $app_alert_text = $app_alert_text != '' ? $app_alert_text : 'Your image is approved now'; 
	    
	    
	    $file_field_required = $this->get_option('_file_field_required') !='' ? $this->get_option('_file_field_required')[0] : 'no';

		/*
		 * [4]
		* TODO: localized array that will be used in JS files
		* Localized object will always be your pluginshortname_vars
		* e.g: pluginshortname_vars.ajaxurl
		*/
		$this -> localized_vars = array('ajaxurl' => admin_url( 'admin-ajax.php' ),
				'plugin_url' 		=> $this->plugin_meta['url'],
				'settings'			=> $this -> plugin_settings,
				'order_id'			=> $this -> order_id,
				'order_email'		=> (isset($this -> order_email)) ? $this -> order_email : '' ,
				'file_upload_path_thumb' => $this->get_file_dir_url ( true ),
				'file_upload_path' => $this->get_file_dir_url (),
				'expand_all'		=> __('Expand all', 'wooconvo'),
				'collapse_all'		=> __('Collapse all', 'wooconvo'),
				'message_max_files_limit'	=> __(' files allowed only', 'wooconvo'),
				'app_text'	=> __($app_text, 'wooconvo'),
				'app_alert_text'	=> __($app_alert_text, 'wooconvo'),
				'file_field_required'	=> __($file_field_required, 'wooconvo'),
		);


		/*
		 * [5]
		* TODO: this array will grow as plugin grow
		* all functions which need to be called back MUST be in this array
		* setting callbacks
		*/
		//following array are functions name and ajax callback handlers
		$this -> ajax_callbacks = array(
				'save_settings',		//do not change this action, is for admin
				'upload_file',
				'send_message',
				'delete_file',
		);

		/*
		 * plugin localization being initiated here
		*/
		add_action('init', array($this, 'wpp_textdomain'));


		/*
		 * plugin main shortcode if needed
		*/
		add_shortcode('nm-wooconvo-orders', array($this , 'load_my_orders'));
		
		/**
		 * laoding convo template on order pages
		 * */
		 $convo_location = $this->get_option('_convo_location') == 'after_table' ? 'after' : 'before';
		
		 add_action("wooconvo_convo_location{$convo_location}_order_table", array($this , 'render_wooconvo_myaccount'));


		/*
		 * hooking up scripts for front-end
		*/
		add_action('wp_enqueue_scripts', array($this, 'load_scripts'));

		/*
		 * hooking up scripts for admin
		*/
		add_action('admin_enqueue_scripts', array($this, 'load_scripts_admin'));

		/*
		 * registering callbacks
		*/
		$this -> do_callbacks();

		/*
		 * wp hook to render stuff after payment
		*/
		global $avada_woocommerce;
		
		remove_action( 'woocommerce_view_order', array( $avada_woocommerce, 'view_order' ), 10 );
		
		add_action("woocommerce_order_details_{$convo_location}_order_table", array($this, 'render_wooconvo_frontend'), 10, 1);

		/*
		 * another panel in orders to display conversation
		* against each Order
		*/
		add_action( 'admin_init', array($this, 'render_convos_in_orders') );

		/*
		 * for secure download
		*/
		add_action('pre_get_posts', array($this, 'wooconvo_do_download'));
		
		/**
		 * change order label view to view and messsage
		 **/
		 add_filter('woocommerce_my_account_my_orders_actions', array($this, 'change_order_text'), 10, 2);
		 
		 // Checking wooconvo_shop_admin_name filter
		 //add_filter('wooconvo_shop_admin_name', array($this, 'change_admin_name'));
		 
		 // WC Vendor Template Override
		 add_filter('wcpv_vendor_order_page_template', array($this, 'wc_vendor_orders') );
		 // WooCommerce Market Place (WCMp) - Vendor Plugin
		 add_action('wcmp_vendor_dashboard_order_details_table_info', array($this, 'wcmp_vendor_dashboard'), 99, 2);
		 // WCMp Latest version
		 add_action('wcmp_vendor_dash_order_details_before_top_left_data', array($this, 'wcmp_vendor_dashboard_2'), 99, 2);
		 
		 add_action( 'wp_ajax_get_wooconvo', array($this, 'get_wooconvo') );
		 
		 if( wooconvo_is_pro_installed() ) {
		 
			add_filter( 'woocommerce_email_classes', array($this, 'wooconvo_vendor_msg_class_include') );
		 }
	}
	

	function wooconvo_vendor_msg_class_include( $email_classes ) {
		
	    $default_path =   WOOCONVO_PATH . "/classes/wooemail.class.php";
	    
	    require_once( $default_path);
	    
	    // add the email class to the list of email classes that WooCommerce loads
	    $email_classes['Wooemail_Vendor_Msg'] = new Wooemail_Vendor_Msg();
		
	    return $email_classes;

	}
	
	function get_wooconvo(){
		$this -> order_id = $_REQUEST['order_id'];
		$this -> load_template('convo-history.php', array('convo_order_admin'=>'yes'));
		$this -> load_template('send-message.php', array('convo_order_admin'=>'yes'));

		die(0);
	}

	
	function change_admin_name($owner_name) {
		
		return 'Admin';
	}


	/*
	 * =============== NOW do your JOB ===========================
	*
	*/

	/*
	 * rendering meta box in orders for convos
	*/
	function render_convos_in_orders() {

		add_meta_box( 'orders_convo', 'Conversation',
				array($this,'render_convo_admin'),
				'shop_order', 'normal', 'default');
	}


	/*
	 * saving admin setting in wp option data table
	*/
	function save_settings(){

		$existingOptions = get_option($this->plugin_meta['shortname'].'_settings');

		update_option($this->plugin_meta['shortname'].'_settings', $_REQUEST);
		_e('All options are updated', 'wooconvo');
		die(0);
	}


	/*
	 * pulling all order's detail
	*/
	function load_my_orders($atts){

		extract(shortcode_atts(array(
		), $atts));

		//saving page permalink
		update_option('wooconvo_page_permalink', get_permalink());

		ob_start();

		$this -> load_template('load-order-convo.php');
		//$this -> load_template('contact-form.php', $template_vars);

		$output_string = ob_get_contents();
		ob_end_clean();
			
		return $output_string;
	}
	
	
	

	/*
	 * rendering convo after payment
	*/
	function render_wooconvo_frontend( $order ){
		
		$this->order_id = $order->get_id();
		
	
		$show_afterorder = $this->get_option('_show_afterorder');
		

		if( isset($show_afterorder[0]) == 'hide' ) return '';
		
		if( defined('WCMp_PLUGIN_TOKEN') ) {
			wooconvo_set_order_read_msg($this->order_id);
			$wcmp_suborders = get_wcmp_suborders($this->order_id);
			if(isset($this->get_option('_chat_box_hide')[0]) && $this->get_option('_chat_box_hide')[0]  == 'yes' && defined('WCMp_PLUGIN_TOKEN') && !empty($wcmp_suborders)){
    			return '';
			}
			
		}	
		
		
		$msgarea_orderstatus = $this->wooconvo_get_msg_orderstatus_setting();
		$order_status		 = wooconvo_get_order_status($this->order_id);
		
		if( $order_status == $msgarea_orderstatus || $msgarea_orderstatus == 'all' ) {
		
		
			$admin = 'no';
			if ( current_user_can( 'administrator' ) ) {$admin = 'yes';}
			/*
			 * NOTE: $this -> order_id is being set in constructor
			*/
			
		
			$this -> load_template('convo-history.php', array('convo_order_admin'=>$admin));
			$this -> load_template('send-message.php', array('convo_order_admin'=>$admin));
		}
	}
	
	
	/*
	 * rendering convo after payment
	*/
	function render_wooconvo_myaccount($order){
		
		$admin = 'no';
		if ( current_user_can( 'administrator' ) ) {$admin = 'yes';}
	
		$this -> order_id = $order->get_id();
		$this -> load_template('convo-history.php', array('convo_order_admin'=>$admin));
		$this -> load_template('send-message.php', array('convo_order_admin'=>$admin));
	}

	/*
	 * function saving wooconvo
	*/
	function send_message()
	{
	

		if ( empty($_POST) || !wp_verify_nonce($_POST['wooconvo_nonce'], 'doing_wooconvo') )
		{
			print 'Sorry, You are not HUMANE.';
			exit;
		}
		extract($_REQUEST);
		
		$current_user = get_current_user_id();
		
		$enable_revision = $this->get_option('_enable_revision') != '' ? $this->get_option('_enable_revision')[0] : 'no' ;
		
		if(!in_array( 'administrator', $current_user->roles ) && class_exists('WOOCONVO_REV') && $enable_revision == 'yes'){
			$rev_count = 0;
			$rev_count = get_post_meta($order_id, 'revision_msg', true);
			if(empty($rev_count)){
				$rev_count = 0;
			}
			update_post_meta($order_id, 'revision_msg', ++$rev_count);
		}
		
	
		$message = sanitize_text_field($message);
	
		$email_from = '';
		$email_to	= array();

		$order_admin_email = wooconvo_get_order_admin_email($order_id, $is_admin);
		$order_admin_name = wooconvo_get_vendor_name($order_id, $is_admin, $order_admin_email);
		
		
		if($is_admin == 'yes'){

			$email_to[]		= get_post_meta($order_id, '_billing_email', true);
			$sent_by 		= $order_admin_name;
			$email_from 	= implode(',', $order_admin_email);
			$user 			= $email_from;
				/*== set key in order meta of unread msg ==*/
			wooconvo_set_order_unread_msg($order_id);

		}else{

			$sender_name = get_post_meta($order_id, '_billing_first_name', true).' '.get_post_meta($order_id, '_billing_last_name', true);

			$email_to 		= $order_admin_email;
			$sent_by 		= $sender_name;
			$email_from 	= get_post_meta($order_id, '_billing_email', true);
			$user 			= get_post_meta($order_id, '_billing_email', true);
			
			
			/*== set key in order meta of unread msg ==*/
			wooconvo_set_order_unread_msg($order_id);
		}
	
		$res = '';
		if ($existing_convo_id != '' &&	 $existing_convo_id != 'undefined')
		{
			//updating
			$select = array(self::$tbl_convo	=> '*');
			$where = array('d'	=>	array('order_id'	=> $order_id));

			$order_convos = $this -> get_row_data($select, $where);

			$existing_thread = json_decode($order_convos -> convo_thread, true);

			//appending new thread to existing

			$existing_thread[] = array(
					'sent_by'	=> $sent_by,
					'message'	=> $message,
					'files'		=> $files,
					'user'		=> $user,
					'senton'	=> current_time('mysql'),
			);

			// print_r($existing_thread); exit;

			$data = array(
					'unread_by'			=> $email_to,
					'convo_thread'		=> json_encode($existing_thread),
			);

			$format = array('%d','%s','%s');
			$where = array('order_id'	=> $order_id);
			$where_format = array('%d');
			$res = $this -> update_table(self::$tbl_convo, $data, $where, $format, $where_format);
			
		}else{

			$thread[] = array(
					'sent_by'	=> $sent_by,
					'message'	=> $message,
					'files'		=> $files,
					'user'		=> $user,
					'senton'	=> current_time('mysql'),
			);
			//new convo
			$data = array(
					'order_id'			=> $order_id,
					'unread_by'			=> $email_to,
					'convo_thread'		=> json_encode($thread),
			);

			$format = array('%d','%s','%s');
			$res = $this -> insert_table(self::$tbl_convo, $data, $format);
			
		}
	
		$notification_sent = true;
		
		
		
		if( $this->wooconvo_send_email() ) {
			$image_download_url = '';
			
			$link_enbaled = $this -> get_option('_file_link_email') != '' ? $this -> get_option('_file_link_email')[0] : 'no';
		
			if($link_enbaled == 'yes'){
				$image_download_url = array();
				foreach($files as $filename){
					$args = array('do_download'=>'file', 'filename'=>$filename);
					$image_download_url[$filename] =  add_query_arg( $args, site_url());
				}
			}
			
			if( ! $this -> send_email_alert($email_to, $email_from, $sent_by, $order_id, $message, $is_admin, $image_download_url) ) {
				$notification_sent = false;
			}
		}
		
		
		$response = array();
		if ($res){
			$message_sent = $this -> get_option('_message_sent');
			$message_sent = ($message_sent == '') ? __('Message sent successfully', 'wooconvo') : $message_sent;
			if( ! $notification_sent ) {
				$message_sent .= __("<br> Email notification couldn't be sent",'wooconvo');
			}
			$response['status'] = 'success';
			$response['message'] = $message_sent;

			$response['last_message'] = $this->get_last_message_html($email_from, $sent_by, $message, current_time('mysql'), $files);

		}else{
			$response['status'] = 'error';
			$response['message'] = __('Please try again', 'wooconvo');
		}
		
		wp_send_json( $response );		
			


		die(0);
	}

	/**
	 * Last Message for Front Response
	 */
	function get_last_message_html($sender_email, $sender_name, $msg, $time, $files = ''){
		ob_start();
		?>
            <li class="self">
                <div class="avatar">
					<?php echo get_avatar( $sender_email, 128 ) ?>
                </div>
                <div class="msg">
                    <p><strong></strong></p>
                    <p>
                        <?php echo stripslashes($msg); ?>
						<?php if ($files != '') {
							$this -> render_attachments($files);
						} ?>
                    </p>
                    <time><span class="dashicons dashicons-clock"></span> <?php echo $this->time_difference($time); ?></time>
                </div>
            </li> 		
		<?php

		return ob_get_clean();
	}

	/*
	 ** Get Conversations againser order_id
	*/

	function get_order_convos()
	{

		//check if this order belongs to email
		if(!$this -> order_valid){
			return NULL;
		}else{
			
			$select = array(self::$tbl_convo	=> '*');
			$where = array('d'	=>	array('order_id'	=> $this -> order_id));

			$order_convos = $this -> get_row_data($select, $where);
			
			return $order_convos;
		}

	}

	/*
	 ** Get Convo Detail
	*/

	function get_convo_detail($order_id)
	{
		//echo "hello";
		global $wpdb;

		$myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix . self::$tbl_convo."
				WHERE order_id = $order_id
				ORDER BY sent_on DESC");
		return $myrows[0];
	}

	/*
	 ** It is making title with suject and latest message excerpt
	*/
	function convo_title($subject, $thread)
	{
		$thread = json_decode($thread);

		//Getting last message array
		$lastChunk = end($thread);
		$lastMessage = stripslashes(self::fix_length_words($lastChunk -> message, 6));
		//print_r($lastMessage);

		$html = "<strong>".stripslashes($subject)."</strong>";
		$html .= "<span style=\"color:#999\"> - $lastMessage</span>";
		return $html;
	}


	/*
	 ** Helper: getting fix lenght of string
	*/
	function fix_length_words($pStr,$pLength)
	{
		$length = $pLength; // The number of words you want

		$text = strip_tags($pStr);
		/*echo $text;
		 exit;*/
		$words = explode(' ', $text); // Creates an array of words
		$words = array_slice($words, 0, $length);
		$str = implode(' ', $words);

		$str .= (count($words) < $pLength) ? '' : '...';

		return $str;
	}

	/*
	 * rendering convos
	* in admin
	*/
	function render_convo_admin($order){
	
	
		$order_id = '';
		if( is_a($order, 'WC_Order') ) {
			$order_id = $order->get_id();
		} else {
			$order_id = $order->ID;
		}
		
		wooconvo_set_order_read_msg($order_id);
		
		if(isset($this->get_option('_chat_box_hide')[0]) && $this->get_option('_chat_box_hide')[0]  == 'yes' && defined('WCMp_PLUGIN_TOKEN')){
				$wcmp_suborders = get_wcmp_suborders($order_id);
        		if ($wcmp_suborders) {
        		}else{
        			
					$this -> order_id = $order_id;
					$this -> load_template('convo-history.php', array('convo_order_admin'=>'yes'));
					$this -> load_template('send-message.php', array('convo_order_admin'=>'yes'));
        		}
		}else{
					$this -> order_id = $order_id;
					$this -> load_template('convo-history.php', array('convo_order_admin'=>'yes'));
					$this -> load_template('send-message.php', array('convo_order_admin'=>'yes'));
		}

	}

	/*
	 * sending email about every convo
	* Admin or Customer
	** $to: array
	*/
	function send_email_alert($to, $from_email, $from_name, $order_id, $convo_message, $is_admin, $image_download_url){

		$order			  = new WC_Order($order_id);
		$order_detail_url = wooconvo_get_order_detail_url($order, $is_admin);
		
		
		$message		  = $this->get_option('_email_message');
		$message		  = str_replace('%sender_name%', $from_name, $message);
		$message		  = str_replace('%sender_email%', $from_email, $message);
		$message		  = str_replace('%message_text%', $convo_message, $message);
		
		$message		  = nl2br($message);
		if(is_array($image_download_url)){
			$message		 .= '<br><br>';
			foreach($image_download_url as $name => $link){
				$message		 .= '<br><br>';
				$message		 .= '<a href="'.esc_url($link).'">'.$name.'</a>';
				
			}
		}
		
		$message		 .= '<br><br>';
		$message		 .= '<a href="'.esc_url($order_detail_url).'">'.__('Click here to reply', 'wooconvo').'</a>';
		$message		 .= '<br><br>';
		
		$to 			  = apply_filters('wooconvo_message_receivers', $to, $is_admin);
		$message		  = apply_filters('wooconvo_message_text', $message, $is_admin);
	
		
		$success_email = WC()->mailer()->emails['Wooemail_Vendor_Msg']->trigger($order_id, $to, $message, $order_detail_url);
		
		if ($success_email){
			return true;

		}else{
			return false;
		}
	
	
		// $headers   = apply_filters('wooconvo_email_headers', $headers, $order_id, $is_admin);

		// $subject = isset($_REQUEST['_subject']) ? $_REQUEST['_subject'] : 'Message sent by '.$from_name.' - order:# '.$order_id;
		// $subject = apply_filters('wooconvo_message_subject', $subject, $order_id, $from_name, $is_admin);


	}


	

	/*
	 * uploading file here
	*/
	/*function upload_file(){

		$dirPath = $this -> setup_file_directory();
		$response = array();

		if($dirPath == 'errDirectory'){

			$response['status']		= 'error';
			$response['message']	= __('Error while creating directory', $this -> plugin_shortname);
			die(0);

		}

		if (!empty($_FILES)) {

			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $dirPath;
			$new_filename = strtotime("now").'-'.preg_replace("![^a-z0-9.]+!i", "_", $_FILES['Filedata']['name']);
			$targetFile = rtrim($targetPath,'/') . '/' .$new_filename;

			$thumb_size = $this -> get_option('_thumb_size');
			$thumb_size = ($thumb_size == '') ? 75 : $thumb_size;

			$type = strtolower(substr(strrchr($new_filename,'.'),1));

			if(move_uploaded_file($tempFile,$targetFile)){

				if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg") )
					$this -> create_thumb($targetPath, $this -> setup_file_directory_thumbs(), $new_filename, $thumb_size);

				$response['status']		= 'uploaded';
				$response['filename']	= $new_filename;
			}

			else{
				$response['status']		= 'error';
				$response['message']	= __('Error while uploading file', $this -> plugin_shortname);
			}
		}
		echo json_encode($response);
		die(0);
	}*/
	
	function upload_file() {
		
		
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
		header ( "Cache-Control: no-store, no-cache, must-revalidate" );
		header ( "Cache-Control: post-check=0, pre-check=0", false );
		header ( "Pragma: no-cache" );
		
		// setting up some variables
		$file_dir_path = $this->setup_file_directory ();
		$response = array ();
		if ($file_dir_path == 'errDirectory') {
			
			$response ['status'] = 'error';
			$response ['message'] = __ ( 'Error while creating directory', 'wooconvo' );
			die ( 0 );
		}
		
		/* ========== Invalid File type checking ========== */
		$file_type = pathinfo($_REQUEST ["name"], PATHINFO_EXTENSION);
		
		$allowed_types = array('php', 'exe');
		
		if( in_array($file_type, $allowed_types) ){
			$response ['status'] = 'error';
			$response ['message'] = __ ( 'File type not valid - '.$file_type, 'wooconvo' );
			wp_send_json( $response );
			die();
		}
		/* ========== Invalid File type checking ========== */
		
		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds
		                        
		// 5 minutes execution time
		@set_time_limit ( 5 * 60 );
		
		// Uncomment this one to fake upload time
		// usleep(5000);
		
		// Get parameters
		$chunk = isset ( $_REQUEST ["chunk"] ) ? intval ( $_REQUEST ["chunk"] ) : 0;
		$chunks = isset ( $_REQUEST ["chunks"] ) ? intval ( $_REQUEST ["chunks"] ) : 0;
		$file_name = isset ( $_REQUEST ["name"] ) ? $_REQUEST ["name"] : '';
		
		// Clean the fileName for security reasons
		//$file_name = sanitize_file_name($file_name); 		//preg_replace ( '/[^\w\._]+/', '_', $file_name );
		$file_name = wp_unique_filename($file_dir_path, $file_name);
		$file_name = strtolower($file_name);
		
		// Make sure the fileName is unique but only if chunking is disabled
		if ($chunks < 2 && file_exists ( $file_dir_path . $file_name )) {
			$ext = strrpos ( $file_name, '.' );
			$file_name_a = substr ( $file_name, 0, $ext );
			$file_name_b = substr ( $file_name, $ext );
			
			$count = 1;
			while ( file_exists ( $file_dir_path . $file_name_a . '_' . $count . $file_name_b ) )
				$count ++;
			
			$file_name = $file_name_a . '_' . $count . $file_name_b;
		}
		
		// Remove old temp files
		if ($cleanupTargetDir && is_dir ( $file_dir_path ) && ($dir = opendir ( $file_dir_path ))) {
			while ( ($file = readdir ( $dir )) !== false ) {
				$tmpfilePath = $file_dir_path . $file;
				
				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match ( '/\.part$/', $file ) && (filemtime ( $tmpfilePath ) < time () - $maxFileAge) && ($tmpfilePath != "{$file_path}.part")) {
					@unlink ( $tmpfilePath );
				}
			}
			
			closedir ( $dir );
		} else
			die ( '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}' );
		
		$file_path = $file_dir_path . $file_name;
		
		// Look for the content type header
		if (isset ( $_SERVER ["HTTP_CONTENT_TYPE"] ))
			$contentType = $_SERVER ["HTTP_CONTENT_TYPE"];
		
		if (isset ( $_SERVER ["CONTENT_TYPE"] ))
			$contentType = $_SERVER ["CONTENT_TYPE"];
			
			// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos ( $contentType, "multipart" ) !== false) {
			if (isset ( $_FILES ['file'] ['tmp_name'] ) && is_uploaded_file ( $_FILES ['file'] ['tmp_name'] )) {
				// Open temp file
				$out = fopen ( "{$file_path}.part", $chunk == 0 ? "wb" : "ab" );
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen ( $_FILES ['file'] ['tmp_name'], "rb" );
					
					if ($in) {
						while ( $buff = fread ( $in, 4096 ) )
							fwrite ( $out, $buff );
					} else
						die ( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
					fclose ( $in );
					fclose ( $out );
					@unlink ( $_FILES ['file'] ['tmp_name'] );
				} else
					die ( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
			} else
				die ( '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}' );
		} else {
			// Open temp file
			$out = fopen ( "{$file_path}.part", $chunk == 0 ? "wb" : "ab" );
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen ( "php://input", "rb" );
				
				if ($in) {
					while ( $buff = fread ( $in, 4096 ) )
						fwrite ( $out, $buff );
				} else
					die ( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
				
				fclose ( $in );
				fclose ( $out );
			} else
				die ( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
		}
		
		// Check if file has been uploaded
		if (! $chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off
			rename ( "{$file_path}.part", $file_path );
			
			// making thumb if images
			if($this -> is_image($file_name))
			{
				$thumb_size = 100;
				$this->create_thumb($file_dir_path, $file_name, $thumb_size);
				
				if(file_exists($this->get_file_dir_path(true) . $file_name))
					list($fw, $fh) = getimagesize($this->get_file_dir_path(true) . $file_name);
					
				$response = array(
						'file_name'			=> $file_name,
						'file_w'			=> $fw,
						'file_h'			=> $fh);
			}else{
				$response = array(
						'file_name'			=> $file_name,
						'file_w'			=> 'na',
						'file_h'			=> 'na');
			}
		}
			
		// Return JSON-RPC response
		//die ( '{"jsonrpc" : "2.0", "result" : '. json_encode($response) .', "id" : "id"}' );
		die ( json_encode($response) );
		
		/*
		 * if (! empty ( $_FILES )) { $tempFile = $_FILES ['Filedata'] ['tmp_name']; $targetPath = $file_dir_path; $new_filename = strtotime ( "now" ) . '-' . preg_replace ( "![^a-z0-9.]+!i", "_", $_FILES ['Filedata'] ['name'] ); $targetFile = rtrim ( $targetPath, '/' ) . '/' . $new_filename; $thumb_size = $this->get_option ( '_thumb_size' ); $thumb_size = ($thumb_size == '') ? 75 : $thumb_size; $type = strtolower ( substr ( strrchr ( $new_filename, '.' ), 1 ) ); if (move_uploaded_file ( $tempFile, $targetFile )) { if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg")) $this->create_thumb ( $targetPath, $new_filename, $thumb_size ); $response ['status'] = 'uploaded'; $response ['filename'] = $new_filename; } else { $response ['status'] = 'error'; $response ['message'] = __ ( 'Error while uploading file', $this->plugin_shortname ); } } echo json_encode ( $response );
		 */
	}
	
	/*
	 * deleting uploaded file from directory
	 */
	function delete_file() {
		$dir_path = $this->setup_file_directory ();
		$file_path = $dir_path . $_REQUEST ['file_name'];
		
		if(file_exists($file_path)){
			if (unlink ( $file_path )) {
				echo __ ( 'File removed', $this->plugin_shortname );
					
				// if image
				$thumb_path = $dir_path . 'thumbs/' . $_REQUEST ['file_name'];
				if(file_exists($thumb_path))
					unlink ( $thumb_path );
			} else {
				echo __ ( 'Error while deleting file ' . $file_path );
			}
		}
		
		
		die ( 0 );
	}
	


	// ================================ SOME HELPER FUNCTIONS =========================================


	function insert_sample_data(){


		$data = array('userID'	=> 1,
				'userName'	=> 'Najeeb Ahmad',
				'fileName'	=> 'Abc.jpg');
		$format = array('%d','%s','%s');

		$this -> insert_table(self::$tbl_list, $data, $format, true);
	}
	
	
	function get_all_inputs() {
		if (! class_exists ( 'NM_Inputs_wooconvo' )) {
			$_inputs = dirname ( __FILE__ ) . DIRECTORY_SEPARATOR . 'input.class.php';
			if (file_exists ( $_inputs ))
				include_once ($_inputs);
			else
				die ( 'Reen, Reen, BUMP! not found ' . $_inputs );
		}
		
		$NM_Inputs_wooconvo = new NM_Inputs_wooconvo ();
		// webcontact_pa($this->plugin_meta);
		
		// registering all inputs here
		
		return array (
					
				'file' 		=> $NM_Inputs_wooconvo->get_input ( 'file' ),
				
		);
		
		// return new NM_Inputs_wooconvo($this->plugin_meta);
	}



	public static function activate_plugin(){

		global $wpdb,$plugin_meta;

		/*
		 * NOTE: $plugin_meta is not object of this class, it is constant
		* defined in config.php
		*/
			
		$sql = "CREATE TABLE `".$wpdb->prefix . self::$tbl_convo."` (
		`convo_id` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		`order_id` INT( 7 ) NOT NULL,
		`unread_by` VARCHAR( 100 ) NOT NULL,
		`convo_thread` MEDIUMTEXT NOT NULL);";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		dbDelta($sql);

		add_option("nm_plugin_db_version", $plugin_meta['db_version']);

	}

	public static function deactivate_plugin(){

		//do nothing so far.
	}
	
	// i18n and l10n support here
	// plugin localization
	function wpp_textdomain() {
		
		$locale_dir = dirname( plugin_basename( dirname(__FILE__) ) ) . '/languages/';
		load_plugin_textdomain('wooconvo', false, $locale_dir);
		
	}
	
	
	function change_order_text($actions, $order){
		
		$msgarea_orderstatus = $this->wooconvo_get_msg_orderstatus_setting();
		// var_dump($msgarea_orderstatus);
		$order_status		 = wooconvo_get_order_status($order->get_id());
		// var_dump($order_status);
		
		if( $order_status == $msgarea_orderstatus || $msgarea_orderstatus == 'all' ) {
	
			$actions['view']['name'] = apply_filters('wooconvo_view_order_text', __('View and Message', 'wooconvo'));
		}
		
		return $actions;
	}
	
	function wc_vendor_orders( $vendor_order_page ) {
		
		$template_path = $this->plugin_meta['path'].'/templates/vendor/html-vendor-order-page.php';
		if( file_exists($template_path) ) {
			$vendor_order_page = $template_path;
		}
		
		return $vendor_order_page;
	}
	
	function wcmp_vendor_dashboard( $order, $vendor ) {
		
		$convojcss = $this->plugin_meta['url'].'/css/wooconvo.css';
		wp_enqueue_style('nmconvo-css', $convojcss);
		
		$this->render_convo_admin( $order );
	}
	
	function wcmp_vendor_dashboard_2( $order, $vendor ) {
		
		$convojcss = $this->plugin_meta['url'].'/css/wooconvo.css';
		wp_enqueue_style('nmconvo-css', $convojcss);
		
		$convo_html = '<tr><td colspan="2">';
		ob_start();
		$this->render_convo_admin( $order );
		$convo_html .= ob_get_clean();
		
		$convo_html .= '</tr></td>';
		
		echo $convo_html;
	}

	function time_difference($date)
	{
		if(empty($date)) {
			return "No date provided";
		}

		$periods         = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths         = array("60","60","24","7","4.35","12","10");

		$now             = current_time('timestamp');
		$unix_date       = strtotime($date);

		// check validity of date
		if(empty($unix_date)) {
			return "Bad date";
		}

		// is it future date or past date
		if($now > $unix_date) {
			$difference     = $now - $unix_date;
			$tense         = "ago";

		} else {
			$difference     = $unix_date - $now;
			$tense         = "from now";
		}

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
			$difference /= $lengths[$j];
		}

		$difference = round($difference);

		if($difference != 1) {
			$periods[$j].= "s";
		}

		return "$difference $periods[$j] {$tense}";
	}

	/*
	 * setting up user directory
	*/

	function setup_file_directory(){


		$upload_dir = wp_upload_dir ();
		
		$file_dir_path = $upload_dir ['basedir'] . '/' . $this->upload_dir_name . '/';
		
		if (! is_dir ( $file_dir_path )) {
			if (mkdir ( $file_dir_path, 0775, true ))
				$dirThumbPath = $file_dir_path . 'thumbs/';
			if (mkdir ( $dirThumbPath, 0775, true ))
				return $file_dir_path;
			else
				return 'errDirectory';
		} else {
			$dirThumbPath = $file_dir_path . 'thumbs/';
			if (! is_dir ( $dirThumbPath )) {
				if (mkdir ( $dirThumbPath, 0775, true ))
					return $file_dir_path;
				else
					return 'errDirectory';
			} else {
				return $file_dir_path;
			}
		}
		
	}
	
	/*
	 * setting up user directory
	*/
	
	function setup_file_directory_thumbs(){
	
		$upload_dir = wp_upload_dir();
	
		//creating thumbs dir
		$dirPath = $upload_dir['basedir'].'/'.$this -> upload_dir_name.'_thumbs/';
		if(!is_dir($dirPath))
		{
			if(mkdir($dirPath, 0775, true))
				return $dirPath;
			else
				return false;
		}else{
			return $dirPath;
		}
	
	
	}


	/*
	 * getting file URL
	*/

	function get_file_dir_url($thumbs=false){

		$content_url = content_url( 'uploads' );
		
		if ($thumbs)
			return $content_url . '/' . $this->upload_dir_name . '/thumbs/';
		else
			return $content_url . '/' . $this->upload_dir_name . '/';
	}

	/**
	 * using wp core image processing editor, 6 May, 2014
	 */
	function create_thumb($dest, $image_name, $thumb_size) {
	
		$image = wp_get_image_editor ( $dest . $image_name );
		$dest = $dest . 'thumbs/' . $image_name;
		if (! is_wp_error ( $image )) {
			$image->resize ( 150, 150, true );
			$image->save ( $dest );
		}
	}
	
	/*
	 * check if file is image and return true
	 */
	function is_image($file){
		
		$type = strtolower ( substr ( strrchr ( $file, '.' ), 1 ) );
		
		if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg"))
			return true;
		else 
			return false;
	}

	function get_file_dir_path(){

		$upload_dir = wp_upload_dir();
		return $upload_dir['basedir'].'/'.$this -> upload_dir_name.'/';
	}

	function fix_request_uri($vars, $the_uri = ''){

		$the_uri = isset($the_uri) ? $the_uri : $_SERVER['REQUEST_URI'];

		$uri = str_replace( '%7E', '~', $the_uri);
		$parts = explode("?", $uri);

		$qsArr = array();
		if(isset($parts[1])){	////// query string present explode it
			$qsStr = explode("&", $parts[1]);
			foreach($qsStr as $qv){
				$p = explode("=",$qv);
				$qsArr[$p[0]] = $p[1];
			}
		}

		//////// updatig query string
		foreach($vars as $key=>$val){
			if($val==NULL) unset($qsArr[$key]); else $qsArr[$key]=$val;
		}

		////// rejoin query string
		$qsStr="";
		foreach($qsArr as $key=>$val){
			$qsStr.=$key."=".urlencode($val)."&";
		}
		if($qsStr!="") $qsStr=substr($qsStr,0,strlen($qsStr)-1);
		$uri = $parts[0];
		if($qsStr!="") $uri.="?".$qsStr;
		return $uri;
		//echo($uri);
	}


	/*
	 * this function is rendernig attachemtn link in convo-history.php
	*/
	function render_attachments($files){
		
		if(!is_array($files)){
			
			$files = explode(',', $files);
		}


		$html = '<ul>';
		$html .= '<li><h4>'.__('Total files: ', 'wooconvo').count($files).'</h4></li>';
		
		foreach ($files as $file){

			$file_path_dir = $this -> get_file_dir_path() . $file;
			if( ! file_exists($file_path_dir) ) continue;
			
			$args = array('do_download'=>'file', 'filename'=>$file);
			$secure_download_url = add_query_arg( $args, site_url() );
		
			if( $this -> is_image($file) ){
				$thumb_url = $this->get_file_dir_url(true) . $file;
			}else{
				$thumb_url = $this->plugin_meta['url'] . '/images/file.png';
			}
		
			$file_size = size_format( filesize( $file_path_dir ));
				
			$html .= '<li class="wooconvo-file-item">';
			$html .= '<span><img width="70" src="'.esc_url($thumb_url).'"></span>';
			$html .= '<span><a href="'.esc_url($secure_download_url).'">'.$file.'</a></span>';
			$html .= '<span>'.$file_size.'</span>';
			$html .= '</li>';
		}
		
		$html .= '</ul>';
		// $html .= '</div>';

		echo apply_filters('wooconvo_render_attachments', $html, $files);
	}
	

	/*
	 * secure download url checking
	*/
	function wooconvo_do_download($query){

		if(isset($_REQUEST['do_download']) && $_REQUEST['do_download'] != '' && $_REQUEST['do_download'] == 'file'){

			$dir_path = $this -> get_file_dir_path();
			$filename = sanitize_text_field($_REQUEST['filename']);
			$file_path = $this -> get_file_dir_path() . $filename;
			
			// var_dump(filesize($file_path)); exit;

			if (file_exists($file_path)){

				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename='.basename($file_path));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file_path));
				@ob_end_flush();
				flush();
				
				$fileDescriptor = fopen($file_path, 'rb');
				
				while ($chunk = fread($fileDescriptor, 8192)) {
				    echo $chunk;
				    @ob_end_flush();
				    flush();
				}
				
				fclose($fileDescriptor);
				exit;
			}

		}
	}
	
	function wooconvo_send_email() {
	
		$notify = $this->get_option('_email_notifications');
		$notify_receiver = false;
		if (isset($notify[0]) && $notify[0] == 'yes') {
		    $notify_receiver = true;
		}
		
		return apply_filters('wooconvo_send_email', $notify_receiver);
	}
	
	function wooconvo_get_msg_orderstatus_setting(){
		$msgarea_orderstatus = $this->get_option('_msg_orderstatus');
		
		if(empty($msgarea_orderstatus)){ $msgarea_orderstatus = 'all'; }
		
		return $msgarea_orderstatus;
	}
}