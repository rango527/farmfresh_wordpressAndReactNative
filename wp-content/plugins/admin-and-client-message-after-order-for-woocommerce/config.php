<?php
/*
 * this file contains pluing meta information and then shared
 * between pluging and admin classes
 * 
 * [1]
 * TODO: change this meta as plugin needs
 */


define('WOOCONVO_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define('WOOCONVO_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
	define('NM_DIR_SEPERATOR', '\\');
} else {
	define('NM_DIR_SEPERATOR', '/');
}

function get_plugin_meta_wooconvo(){
	
	$plugin_meta		= array('name'			=> 'WooConvo',
							'shortname'		=> 'wooconvo', //without nm_ will be shortname
							'path'			=> untrailingslashit(plugin_dir_path( __FILE__ )),
							'url'			=> untrailingslashit(plugin_dir_url( __FILE__ )),
							'db_version'	=> 2.0,
							'logo'			=> untrailingslashit(plugin_dir_url( __FILE__ )) . '/images/logo.png',
							'menu_position'	=> 999);
	
	return $plugin_meta;
}

/**
 * get shop owner/admin name
 * @since 3.4
 **/
function wooconvo_get_vendor_name($order_id, $is_admin='no', $vendor_email='') {
	
	$order_admin_name = '';
	if( defined('WCMp_PLUGIN_TOKEN') && $is_admin == 'yes') {
		
		// If is_admin then $vendor_email will have only one email
		$vendor_email = $vendor_email[0];
		
		$vendors = get_vendor_from_an_order($order_id);

        if ($vendors) {
            foreach ($vendors as $vendor) {

                $vendor_obj = get_wcmp_vendor_by_term($vendor);
                if( $vendor_obj->user_data->user_email == $vendor_email) {
                	$order_admin_name = $vendor_obj->page_title;
                }
            }
        }
	}
	
	if( empty($order_admin_name) ) {
		$order_admin_name = get_bloginfo('name');
	}
	
	return apply_filters('wooconvo_shop_admin_name', $order_admin_name, $order_id);
}


function wooconvo_get_order_admin_email( $order_id, $is_admin='no') {
    
    // if WCMp pluign installed
    $order_admin_emails = array();
    if( defined('WCMp_PLUGIN_TOKEN') ) {    
        $user_id = get_current_user_id();
        
        if( $is_admin == 'yes' ) {
            
           if (is_user_wcmp_vendor($user_id)) {
                $vendor = get_wcmp_vendor($user_id);
                $order_admin_emails[] = $vendor->user_data->user_email;
           }else{
               $user_info = get_userdata( $user_id );
                $order_admin_emails[] = $user_info->user_email;
           }

        } else {
        	
            $vendors = get_vendor_from_an_order($order_id);
            
            $wcmp_suborders = get_wcmp_suborders($order_id);
			
 
            if ($vendors && empty($wcmp_suborders)) {
                foreach ($vendors as $vendor) {
    
                    $vendor_obj = get_wcmp_vendor_by_term($vendor);
                    $order_admin_emails[] = $vendor_obj->user_data->user_email;
                }
            }else{
                
                $order_admin_emails[] = get_bloginfo('admin_email');
            }
        }
    } else {
        
        $order_admin_emails[] = get_bloginfo('admin_email');
    }
    
    return apply_filters('order_admin_email', $order_admin_emails);
}

function wooconvo_is_pro_installed() {

	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	if( is_plugin_active('nm-wooconvo-pro/nm-wooconvo-pro.php') ){
		return true;
	}else{
		return false;
	}
	
}


// Get order detail URL
function wooconvo_get_order_detail_url( $order, $is_admin ) {
	
	$order_url = '';
		
	if( current_user_can('administrator')){
		$order_url = $order -> get_view_order_url();
		$user = wp_get_current_user();
		
		$order_url = $order -> get_view_order_url();
	
	}elseif(defined('WCMp_PLUGIN_TOKEN') && current_user_can( 'dc_vendor' )){
			$order_url = $order -> get_view_order_url();
	}else{
		
		$order_url = admin_url( 'post.php?post='.$order->get_id().'&action=edit');
		if(defined('WCMp_PLUGIN_TOKEN')) {
			$wcmp_suborders = get_wcmp_suborders($order->get_id());
			if(empty($wcmp_suborders)){
				$order_url = wcmp_get_vendor_dashboard_endpoint_url(get_wcmp_vendor_settings('wcmp_vendor_orders_endpoint', 'vendor', 'general', 'vendor-orders'), $order->get_id());
			}
			
		}
	}
	
	return $order_url;
}

// function wooconvo_pa( $arr ) {
	
// 	echo '<pre>';
// 	print_r($arr);
// 	echo '</pre>';
// }

function wooconvo_set_order_unread_msg($order_id){
	if(!empty($order_id)){
		update_post_meta($order_id, 'wooconvo_new_msg', 1);
	}
}

function wooconvo_set_order_read_msg($order_id){
	if(!empty($order_id)){
		update_post_meta($order_id, 'wooconvo_new_msg', 0);
	}
}


function wooconvo_admin_user_columns_cpt($column){

    foreach ($column as $column_name => $column_info) {
        $new_columns[$column_name] = $column_info;
        if ('order_status' === $column_name) {
            $new_columns['wooconvo_new_msg'] = sprintf(__('%s', 'wooconvo'), 'WooConvo Msgs');
        }
    }
    return $new_columns;

}

function wooconvo_admin_user_columns_data_cpt($column){

	global $post;
	$html   = '';

    if ( 'wooconvo_new_msg' === $column ) {
        
        $is_msg   = get_post_meta($post->ID, 'wooconvo_new_msg', true);
      
        if(!$is_msg){
			$html   .= '';
		}else{
			$html.= '<i style="text-align: center;margin: 0 auto;width: 50%;font-size: 23px;color: #7dbd7d;" class="fa fa-commenting" aria-hidden="true"></i>';	
		}
		echo  $html;
	
	
    }
	
}

function wooconvo_after_suborder_details($suborder){
	$html   	= '';
	$is_msg   = get_post_meta($suborder->get_id(), 'wooconvo_new_msg', true);
	if($is_msg){
		if(is_admin()){
			$order_uri = apply_filters('wcmp_admin_vendor_shop_order_edit_url', esc_url('post.php?post=' . $suborder->get_id() . '&action=edit'), $suborder->get_id());
			$html.= '<a href="'.$order_uri.'"><i style="text-align: center;margin-left: 7px;font-size: 16px;color: #7dbd7d;" class="fa fa-commenting" aria-hidden="true"></i></a>';	
			
		}else{
			$order			  = new WC_Order($suborder->get_id());
			$order_url = $order -> get_view_order_url();
			$html.= '<a href="'.$order_url.'"><i style="text-align: center;margin-left: 7px;font-size: 16px;color: #7dbd7d;" class="fa fa-commenting" aria-hidden="true"></i></a>';	
		}
	}
	
	if(is_admin()){
		$order_uri = apply_filters('wcmp_admin_vendor_shop_order_edit_url', esc_url('post.php?post=' . $suborder->get_id() . '&action=edit'), $suborder->get_id());
		$html.= '<a class="" style="padding-right: 6px; padding-left: 6px; margin-left: 6px;font-size: 12px; background: #7dbd7d;color: white;border-color: #ffffff !important;" href="'.$order_uri.'">Chat</a>';	
		
	}else{
		$order			  = new WC_Order($suborder->get_id());
		$order_url = $order -> get_view_order_url();
		$html.= '<a class="button" style="padding-right:6px;padding-left 6px;margin-left: 3px;line-height:12px;font-size: 9px;" href="'.$order_url.'">Chat</a>';	
	}
	
	
	echo  $html;
	
}

function wooconvo_get_order_status($order_id){
	$order = wc_get_order( $order_id );
	$order_status  = $order->get_status();
	
	return $order_status;
}