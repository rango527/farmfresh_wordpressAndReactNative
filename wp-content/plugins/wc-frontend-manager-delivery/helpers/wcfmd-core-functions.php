<?php
if(!function_exists('wcfmd_woocommerce_inactive_notice')) {
	function wcfmd_woocommerce_inactive_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf( __( '%sWCFM - Delivery is inactive.%s The %sWooCommerce plugin%s must be active for the WCFM - Delivery to work. Please %sinstall & activate WooCommerce%s', WCFMd_TEXT_DOMAIN ), '<strong>', '</strong>', '<a target="_blank" href="http://wordpress.org/extend/plugins/woocommerce/">', '</a>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=woocommerce' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
		</div>
		<?php
	}
}

if(!function_exists('wcfmd_wcfm_inactive_notice')) {
	function wcfmd_wcfm_inactive_notice() {
		?>
		<div id="message" class="error">
		<p><?php printf( __( '%sWCFM - Delivery is inactive.%s The %sWooCommerce Frontend Manager%s must be active for the WCFM - Delivery to work. Please %sinstall & activate WooCommerce Frontend Manager%s', WCFMd_TEXT_DOMAIN ), '<strong>', '</strong>', '<a target="_blank" href="https://wordpress.org/plugins/wc-frontend-manager/">', '</a>', '<a href="' . admin_url( 'plugin-install.php?tab=search&s=wc+frontend+manager' ) . '">', '&nbsp;&raquo;</a>' ); ?></p>
		</div>
		<?php
	}
}

if( !function_exists( 'wcfm_get_delivery_boys' ) ) {
	function wcfm_get_delivery_boys( $limit = -1, $offset = 0 ) {
		$delivery_boy_role = apply_filters( 'wcfm_delivery_boy_user_role', 'wcfm_delivery_boy' );
		
		$args = array(
									'role__in'     => array( $delivery_boy_role ),
									'orderby'      => 'ID',
									'order'        => 'ASC',
									'offset'       => $offset,
									'number'       => $limit,
									'count_total'  => false
								 ); 
		$args = apply_filters( 'wcfmd_get_delivery_boys_args', $args );
		$wcfm_delivery_boys_array = get_users( $args );
		return apply_filters( 'wcfm_delivery_boys', $wcfm_delivery_boys_array );
	}
}

if( !function_exists( 'wcfm_is_delivery_boy' ) ) {
	function wcfm_is_delivery_boy( $delivery_boy_id = '' ) {
		if( !$delivery_boy_id && !is_user_logged_in() ) return false;
		
		if( !$delivery_boy_id && is_user_logged_in() ) {
			$delivery_boy_id = get_current_user_id();
		} 
		
		$delivery_boy_role = apply_filters( 'wcfm_delivery_boy_user_role', 'wcfm_delivery_boy' );
		
		if( $delivery_boy_id ) {
			$delivery_user = get_userdata( $delivery_boy_id );
			if( $delivery_user ) {
				if ( in_array( $delivery_boy_role, $delivery_user->roles ) ) {
					return apply_filters( 'wcfm_is_delivery_boy', true );
				} else {
					return apply_filters( 'wcfm_is_delivery_boy', false );
				}
			} else {
				return apply_filters( 'wcfm_is_delivery_boy', false );
			}
		}
		
		return apply_filters( 'wcfm_is_delivery_boy', false );
	}
}

if( !function_exists( 'wcfm_is_vendor_delivery_boy' ) ) {
	function wcfm_is_vendor_delivery_boy( $delivery_boy_id = '' ) {
		if( !$delivery_boy_id && !is_user_logged_in() ) return false;
		if( !wcfm_is_delivery_boy( $delivery_boy_id ) ) return false;
		
		if( !$delivery_boy_id ) $delivery_boy_id = get_current_user_id();
		$wcfm_vendor = get_user_meta( $delivery_boy_id, '_wcfm_vendor', true );
		if( $wcfm_vendor ) return apply_filters( 'wcfm_is_vendor_delivery_boy', true );
			
		return apply_filters( 'wcfm_is_vendor_delivery_boy', false );
	}
}

if( !function_exists( 'wcfm_get_delivery_boy_vendor' ) ) {
	function wcfm_get_delivery_boy_vendor( $delivery_boy_id = '' ) {
		if( !$delivery_boy_id && !is_user_logged_in() ) return 0;
		if( !wcfm_is_delivery_boy( $delivery_boy_id ) ) return 0;
		if( !wcfm_is_vendor_delivery_boy( $delivery_boy_id ) ) return 0;
		
		if( !$delivery_boy_id ) $delivery_boy_id = get_current_user_id();
		$wcfm_vendor = get_user_meta( $delivery_boy_id, '_wcfm_vendor', true );
		if( $wcfm_vendor ) return apply_filters( 'wcfm_delivery_boy_vendor_id', $wcfm_vendor );
			
		return 0;
	}
}

if( !function_exists( 'wcfm_get_delivery_boy_delivery_stat' ) ) {
	function wcfm_get_delivery_boy_delivery_stat( $delivery_boy_id = '', $status = '' ) {
		global $WCFM, $WCFMd, $wpdb;
		
		if( !$delivery_boy_id && !is_user_logged_in() ) return 0;
		if( !wcfm_is_delivery_boy( $delivery_boy_id ) ) return 0;
		
		if( !$delivery_boy_id ) $delivery_boy_id = get_current_user_id();
		
		$sql  = "SELECT COUNT(ID) FROM `{$wpdb->prefix}wcfm_delivery_orders`";
		$sql .= " WHERE 1=1";
		$sql .= " AND delivery_boy = {$delivery_boy_id}";
		$sql .= " AND is_trashed = 0";
		if( $status ) $sql .= " AND delivery_status = '{$status}'";
		$delivery_count = $wpdb->get_var( $sql );
		
		return $delivery_count;
			
		return 0;
	}
}

if( !function_exists( 'wcfm_get_order_delivery_boys' ) ) {
	function wcfm_get_order_delivery_boys( $order_id, $order_item_id = '' ) {
		global $WCFM, $WCFMd, $wpdb;
		
		$delivery_boys_array = array();
		
		if( !$order_id ) return $delivery_boys_array;
		
		$sql  = "SELECT * FROM `{$wpdb->prefix}wcfm_delivery_orders`";
		$sql .= " WHERE 1=1";
		$sql .= " AND order_id = {$order_id}";
		if( $order_item_id ) $sql .= " AND item_id = {$order_item_id}";
		
		$delivery_boys = $wpdb->get_results( $sql );
		if( !empty( $delivery_boys ) ) {
			foreach( $delivery_boys as $delivery_boy ) {
				$delivery_boys_array[] = array( 'order' => $order_id, 'item' => $delivery_boy->item_id, 'vendor' => $delivery_boy->vendor_id, 'delivery_boy' => $delivery_boy->delivery_boy, 'status' => $delivery_boy->delivery_status );
			}
		}
		
		return apply_filters( 'wcfm_delivery_boys', $delivery_boys_array, $order_id, $order_item_id, $delivery_boys );
	}
}

if( !function_exists( 'wcfm_update_order_delivery_boys_meta' ) ) {
	function wcfm_update_order_delivery_boys_meta( $order_id, $delivery_boys_array = array() ) {
		if( empty( $delivery_boys_array ) ) $delivery_boys_array = wcfm_get_order_delivery_boys( $order_id );
		$delivery_boys_string = '';
		if( !empty( $delivery_boys_array ) ) {
			foreach( $delivery_boys_array as $delivery_boy ) {
				if( !empty( $delivery_boy['delivery_boy'] ) ) {
					$delivery_boys_string .= ',' . $delivery_boy['delivery_boy'];
				}
			}
			update_post_meta( $order_id, '_wcfm_delivery_boys', $delivery_boys_string );
		}
	}
}

if( !function_exists( 'wcfm_get_order_delivery_boys_string' ) ) {
	function wcfm_get_order_delivery_boys_string( $order_id, $order_item_id = '', $stat_link = true ) {
		$delivery_boys_array  = wcfm_get_order_delivery_boys( $order_id, $order_item_id );
		$delivery_boys_string = '';
		
		$vendor_id = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
		
		if( !empty( $delivery_boys_array ) ) {
			foreach( $delivery_boys_array as $delivery_boy ) {
				if( !empty( $delivery_boy['delivery_boy'] ) ) {
					if( wcfm_is_vendor() && ( $vendor_id != $delivery_boy['vendor'] ) ) continue;
					$delivery_boys_string .= wcfm_get_delivery_boy_label( $delivery_boy['delivery_boy'], $delivery_boy['status'], $stat_link );
				}
			}
		}
		return apply_filters( 'wcfm_delivery_boys_string', $delivery_boys_string, $order_id, $order_item_id, $delivery_boys_array );
	}
}

if( !function_exists( 'wcfm_get_delivery_boy_label' ) ) {
	function wcfm_get_delivery_boy_label( $delivery_boy, $delivery_status = '', $stat_link = true, $show_status = true ) {
		if( !$delivery_boy ) return '';
		
		$delivery_boy_label     = '';
		$wcfm_delivery_boy_user = get_userdata( absint( $delivery_boy ) );
					
		if( $wcfm_delivery_boy_user ) {
						
			$vendor_id           = apply_filters( 'wcfm_current_vendor_id', get_current_user_id() );
			$delivery_boy_vendor = wcfm_get_delivery_boy_vendor( $delivery_boy );
			
			$delivery_boy_label .= '<div class="wcfm_order_delivery_boy">';
			
			if( $delivery_status && $show_status && apply_filters( 'wcfm_is_allow_order_delivery_boy_with_status', true ) ) {
				if( $delivery_status == 'pending' ) {
					$delivery_boy_label .= '<span class="tips wcicon-status-pending text_tip" data-tip="'. __( 'Pending', 'wc-frontend-manager-delivery' ) .'"></span>&nbsp;';
				} elseif( $delivery_status == 'delivered' ) {
					$delivery_boy_label .= '<span class="tips wcicon-status-completed text_tip" data-tip="'. __( 'Delivered', 'wc-frontend-manager-delivery' ) .'"></span>&nbsp;';
				}
			}
			
			if( !$stat_link || ( wcfm_is_vendor() && ( $vendor_id != $delivery_boy_vendor ) ) ) {
				$delivery_boy_label .= '<span class="wcfm_delivery_boy_label">';
			} else {
				$delivery_boy_label .= '<a href="' . get_wcfm_delivery_boys_stats_url($delivery_boy) . '" target="_blank" class="wcfm_dashboard_item_title wcfm_delivery_boy_label">';
			}
			
			$delivery_boy_label .= apply_filters( 'wcfm_delivery_boy_display', $wcfm_delivery_boy_user->first_name . ' ' . $wcfm_delivery_boy_user->last_name, $delivery_boy );
			
			if( !$stat_link || ( wcfm_is_vendor() && ( $vendor_id != $delivery_boy_vendor ) ) ) {
				$delivery_boy_label .= '</span>';
			} else {
				$delivery_boy_label .= '</a>';
			}
			
			$delivery_boy_label .= '</div>';
		}
		
		return $delivery_boy_label;
	}
}

if( !function_exists( 'wcfm_is_order_delivered' ) ) {
	function wcfm_is_order_delivered( $order_id, $order_item_id = '' ) {
		$delivery_boys_array  = wcfm_get_order_delivery_boys( $order_id, $order_item_id );
		$is_delivered = true;
		
		if( !empty( $delivery_boys_array ) ) {
			foreach( $delivery_boys_array as $delivery_boy ) {
				if( $delivery_boy['status'] == 'pending' ) {
					$is_delivered = false;
					break;
				}
			}
		} else {
			$is_delivered = false;
		}
		return apply_filters( 'wcfm_is_order_delivered', $is_delivered, $order_id, $order_item_id, $delivery_boys_array );
	}
}

if(!function_exists('get_wcfm_deliveries_url')) {
	function get_wcfm_deliveries_url() {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfmgs_deliveries_url = wcfm_get_endpoint_url( 'wcfm-deliveries', '', $wcfm_page );
		return $wcfmgs_deliveries_url;
	}
}

if(!function_exists('get_wcfm_delivery_boys_dashboard_url')) {
	function get_wcfm_delivery_boys_dashboard_url( ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfmgs_delivery_boys_url = wcfm_get_endpoint_url( 'wcfm-delivery-boys', '', $wcfm_page );
		return $wcfmgs_delivery_boys_url;
	}
}

if(!function_exists('get_wcfm_delivery_boys_manage_url')) {
	function get_wcfm_delivery_boys_manage_url( $delivery_boy_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfmgs_delivery_boys_manage_url = wcfm_get_endpoint_url( 'wcfm-delivery-boys-manage', $delivery_boy_id, $wcfm_page );
		return $wcfmgs_delivery_boys_manage_url;
	}
}

if(!function_exists('get_wcfm_delivery_boys_stats_url')) {
	function get_wcfm_delivery_boys_stats_url( $delivery_boy_id = '' ) {
		global $WCFM;
		$wcfm_page = get_wcfm_page();
		$wcfmgs_delivery_boys_stats_url = wcfm_get_endpoint_url( 'wcfm-delivery-boys-stats', $delivery_boy_id, $wcfm_page );
		return $wcfmgs_delivery_boys_stats_url;
	}
}

if(!function_exists('get_wcfmd_delivery_boys_manage_messages')) {
	function get_wcfmd_delivery_boys_manage_messages() {
		global $WCFMd;
		
		$messages = array(
											'no_username' => __( 'Please insert Delivery Boy Username before submit.', 'wc-frontend-manager-delivery' ),
											'no_email' => __( 'Please insert Delivery Boy Email before submit.', 'wc-frontend-manager-delivery' ),
											'username_exists' => __( 'This Username already exists.', 'wc-frontend-manager-delivery' ),
											'email_exists' => __( 'This Email already exists.', 'wc-frontend-manager-delivery' ),
											'delivery_boy_failed' => __( 'Delivery Boy Saving Failed.', 'wc-frontend-manager-delivery' ),
											'delivery_boy_saved' => __( 'Delivery Boy Successfully Saved.', 'wc-frontend-manager-delivery' ),
											);
		
		return $messages;
	}
}

?>