<?php

class WCFM_REST_Store_Vendors_Controller extends WCFM_REST_Controller {

    /**
     * Endpoint namespace
     *
     * @var string
     */
    protected $namespace = 'wcfmmp/v1';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'store-vendors';

    /**
     * Stores the request.
     * @var array
     */
    protected $request = array();

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function __construct() {
        
    }

    /**
     * Register the routes for settings.
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_store_vendors' ),
                'permission_callback' => array( $this, 'get_store_vendors_permissions_check' ),
                'args'                => $this->get_collection_params(),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', array(
            'args'   => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.', 'wcfm-marketplace-rest-api' ),
                    'type'        => 'integer',
                )
            ),
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_store_vendor' ),
                'permission_callback' => array( $this, 'get_store_vendor_permissions_check' ),
                'args'                => $this->get_collection_params(),
            ),
            'schema' => array( $this, 'get_public_item_schema' ),
        ) );
    }

    public function get_store_vendors( $request ) {
        //print_r('testing');
        global $WCFM;
        $_POST["controller"] = 'wcfm-vendors';
        $_POST['length'] = ! empty( $request['per_page'] ) ? intval( $request['per_page'] ) : 10;
        $_POST['start'] = ! empty( $request['page'] ) ? ( intval( $request['page'] ) - 1 ) * $_POST['length'] : 0;
        $_POST['filter_date_form'] = ! empty( $request['after'] ) ? $request['after'] : '';
        $_POST['filter_date_to'] = ! empty( $request['before'] ) ? $request['before'] : '';
        $queries_data = array();
        parse_str($_SERVER['QUERY_STRING'], $queries_data);

        $_POST['search_data'] = array();
        foreach( $queries_data as $query_key => $query_value ) {
          if( in_array( $query_key, apply_filters( 'wcfmmp_vendor_list_exclude_search_keys', array( 'v', 'search_term', 'wcfmmp_store_search', 'wcfmmp_store_category', 'wcfmmp_radius_addr', 'wcfmmp_radius_lat', 'wcfmmp_radius_lng', 'wcfmmp_radius_range', 'excludes', 'orderby', 'lang' ) ) ) )
          $_POST['search_data'][$query_key] =  $query_value;
        }

        define( 'WCFM_REST_API_CALL', TRUE );
        $WCFM->init();
        $wcfm_vendors_array = array();
        $wcfm_vendors_json_arr = array();
        $response = array();
        $wcfm_vendors_array = $WCFM->ajax->wcfm_ajax_controller();
//        return rest_ensure_response( $wcfm_vendors_array );
        if ( ! empty( $wcfm_vendors_array ) ) {
            $index = 0;
            foreach ( $wcfm_vendors_array as $wcfm_vendors_id => $wcfm_vendors_name ) {
                $response[$index] = $this->get_formatted_item_data( $wcfm_vendors_id, $wcfm_vendors_name );
                $index ++;
            }
            //print_r($response);
            return rest_ensure_response( apply_filters( "wcfmapi_rest_prepare_store_vendors_objects", $response, $request ) );
        } else {
            return rest_ensure_response( $response );
        }
    }

    public function get_store_vendors_permissions_check() {
        return true;
    }

    public function get_store_vendor( $request ) {
        $wcfm_vendor_data = apply_filters( "wcfmapi_rest_prepare_store_vendor_object", $this->get_formatted_item_data( $request['id'] ), $request );
        return rest_ensure_response( $wcfm_vendor_data );
    }

    public function get_store_vendor_permissions_check() {
        return true;
    }

    public function get_formatted_item_data( $wcfm_vendors_id, $formatted_name = '' ) {
        global $WCFM, $WCFMmp;

        $user = get_user_by( 'id', (int) $wcfm_vendors_id );
        $is_vendor_disabled = $user ? get_user_meta( $user->ID, '_disable_vendor', true ) : false;
        if ( ! $user || ( ! wcfm_is_vendor( $user->ID ) && ! $is_vendor_disabled ) ) {
            return new WP_Error( "wcfmapi_rest_invalid_vendor_id", __( 'Vendor id specified is incorrect', 'wcfm-marketplace-rest-api' ), array( 'status' => 404 ) );
        }

        $wcfm_vendor_data = array();
        $wcfm_vendor_data['vendor_id'] = $user->ID;
        $wcfm_vendor_data['vendor_display_name'] = $user->display_name;
        $wcfm_vendor_data['vendor_shop_name'] = wcfm_get_vendor_store_name( $user->ID );

        if ( $formatted_name ) {
            $wcfm_vendor_data['formatted_display_name'] = $formatted_name;
        } else {
            $wcfm_vendor_data['formatted_display_name'] = $wcfm_vendor_data['vendor_shop_name'] . ' - ' . $user->display_name . ' (#' . $user->ID . ' - ' . $user->user_login . ')';
        }

        $store_user = wcfmmp_get_store( $user->ID );
        $store_info = $store_user->get_shop_info();
        $wcfm_vendor_data['store_hide_email'] = isset( $store_info['store_hide_email'] ) ? $store_info['store_hide_email'] : 'no';
        $wcfm_vendor_data['store_hide_phone'] = isset( $store_info['store_hide_phone'] ) ? $store_info['store_hide_phone'] : 'no';
        $wcfm_vendor_data['store_hide_address'] = isset( $store_info['store_hide_address'] ) ? $store_info['store_hide_address'] : 'no';
        $wcfm_vendor_data['store_hide_description'] = isset( $store_info['store_hide_description'] ) ? $store_info['store_hide_description'] : 'no';
        $wcfm_vendor_data['store_hide_policy'] = isset( $store_info['store_hide_policy'] ) ? $store_info['store_hide_policy'] : 'no';
        $wcfm_vendor_data['store_products_per_page'] = isset( $store_info['store_ppp'] ) ? (int) $store_info['store_ppp'] : 10;

        $email = $store_user->get_email();
        if ( $email && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_email' ) ) {
            $wcfm_vendor_data['vendor_email'] = $email;
        }
        $phone = $store_user->get_phone();
        if ( $phone && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_phone' ) ) {
            $wcfm_vendor_data['vendor_phone'] = $phone;
        }
        $address = $store_user->get_address_string();
        if ( $address && $WCFM->wcfm_vendor_support->wcfm_vendor_has_capability( $store_user->get_id(), 'vendor_address' ) ) {
            $wcfm_vendor_data['vendor_address'] = $address;
        }

        $wcfm_vendor_data['disable_vendor'] = wc_bool_to_string( $is_vendor_disabled );
        $wcfm_vendor_data['is_store_offline'] = wc_bool_to_string( get_user_meta( $user->ID, '_wcfm_store_offline', true ) );

        $wcfm_vendor_data['vendor_shop_logo'] = $store_user->get_avatar();
        $banner_type = $store_user->get_banner_type();
        $default_banner = ! empty( $WCFMmp->wcfmmp_marketplace_options['store_default_banner'] ) ? wcfm_get_attachment_url( $WCFMmp->wcfmmp_marketplace_options['store_default_banner'] ) : $WCFMmp->plugin_url . 'assets/images/default_banner.jpg';

        if ( $banner_type == 'slider' ) {
            $slider = array();
            $slider_img_ids = $store_user->get_banner_slider();
            if ( ! empty( $slider_img_ids ) ) {
                foreach ( $slider_img_ids as $slide ) {
                    if ( empty( $slide['image'] ) )
                        continue;
                    $img_url = isset( $slide['image'] ) && is_numeric( $slide['image'] ) ? wcfm_get_attachment_url( $slide['image'] ) : '';
                    array_push( $slider, array(
                        'link'  => isset( $slide['link'] ) ? $slide['link'] : '',
                        'image' => $img_url,
                    ) );
                }
            }
            $wcfm_vendor_data['vendor_banner_type'] = 'slider';
            $wcfm_vendor_data['vendor_banner'] = $slider;
        } elseif ( $banner_type == 'video' ) {
            $wcfm_vendor_data['vendor_banner_type'] = 'video';
            $wcfm_vendor_data['vendor_banner'] = $store_user->get_banner_video();
        } else {
            $wcfm_vendor_data['vendor_banner_type'] = 'image';
            $banner = $store_user->get_banner();
            $wcfm_vendor_data['vendor_banner'] = $banner;
        }
        if ( ! $wcfm_vendor_data['vendor_banner'] ) {
            $banner = apply_filters( 'wcfmmp_store_default_banner', $default_banner );
            $wcfm_vendor_data['vendor_banner'] = $banner;
        }

        $mobile_banner = $store_user->get_mobile_banner();
        if ( ! $mobile_banner ) {
            $mobile_banner = $store_user->get_banner();
            if ( ! $mobile_banner ) {
                $mobile_banner = apply_filters( 'wcfmmp_store_default_banner', $default_banner );
            }
        }
        $wcfm_vendor_data['mobile_banner'] = $mobile_banner;

        $list_banner_type = $store_user->get_list_banner_type();
        if ( $list_banner_type == 'video' ) {
            $wcfm_vendor_data['vendor_list_banner_type'] = 'video';
            $wcfm_vendor_data['vendor_list_banner'] = $store_user->get_list_banner_video();
        } else {
            $list_banner = $store_user->get_list_banner();
            if ( ! $list_banner ) {
                $list_banner = ! empty( $WCFMmp->wcfmmp_marketplace_options['store_list_default_banner'] ) ? wcfm_get_attachment_url( $WCFMmp->wcfmmp_marketplace_options['store_list_default_banner'] ) : $WCFMmp->plugin_url . 'assets/images/default_banner.jpg';
                $list_banner = apply_filters( 'wcfmmp_list_store_default_bannar', $list_banner );
            }
            $wcfm_vendor_data['vendor_list_banner_type'] = 'image';
            $wcfm_vendor_data['vendor_list_banner'] = $list_banner;
        }

        if ( apply_filters( 'wcfm_is_pref_vendor_reviews', true ) ) {
            $wcfm_vendor_data['store_rating'] = $WCFMmp->wcfmmp_reviews->get_vendor_review_rating( $user->ID );
        }

        if ( apply_filters( 'wcfm_is_allow_email_verification', true ) ) {
            $user_email = $user->user_email;
            $email_verified = get_user_meta( $user->ID, '_wcfm_email_verified', true );
            $wcfm_email_verified_for = get_user_meta( $user->ID, '_wcfm_email_verified_for', true );
            if ( $email_verified && ( $user_email != $wcfm_email_verified_for ) )
                $email_verified = false;
            $wcfm_vendor_data['email_verified'] = $email_verified;
        }

        $wcfmvm_registration_custom_fields = get_option( 'wcfmvm_registration_custom_fields', array() );
        $wcfmvm_custom_infos = get_user_meta( $user->ID, 'wcfmvm_custom_infos', true );

        $wcfm_vendor_data['vendor_additional_info'] = array();

        if ( ! empty( $wcfmvm_registration_custom_fields ) ) {
            foreach ( $wcfmvm_registration_custom_fields as $key => $wcfmvm_registration_custom_field ) {
                $wcfmvm_registration_custom_field['name'] = sanitize_title( $wcfmvm_registration_custom_field['label'] );
                $field_value = '';
                if ( ! empty( $wcfmvm_custom_infos ) ) {
                    if ( $wcfmvm_registration_custom_field['type'] == 'checkbox' ) {
                        $field_value = isset( $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] ) ? $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] : 'no';
                    } elseif ( $wcfmvm_registration_custom_field['type'] == 'upload' ) {
                        $field_name = 'wcfmvm_custom_infos[' . $wcfmvm_registration_custom_field['name'] . ']';
                        $field_id = md5( $field_name );
                        $field_value = isset( $wcfmvm_custom_infos[$field_id] ) ? $wcfmvm_custom_infos[$field_id] : '';
                    } else {
                        $field_value = isset( $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] ) ? $wcfmvm_custom_infos[$wcfmvm_registration_custom_field['name']] : '';
                    }
                }
                $wcfm_vendor_data['vendor_additional_info'][$key] = $wcfmvm_registration_custom_field;
                $wcfm_vendor_data['vendor_additional_info'][$key]['value'] = $field_value;
            }
        }

        $wcfm_membership = get_user_meta( $user->ID, 'wcfm_membership', true );
        if ( $wcfm_membership && function_exists( 'wcfm_is_valid_membership' ) && wcfm_is_valid_membership( $wcfm_membership ) ) {
            $wcfm_vendor_data['membership_details']['membership_title'] = get_the_title( $wcfm_membership );
            $wcfm_vendor_data['membership_details']['membership_id'] = $wcfm_membership;

            $next_schedule = get_user_meta( $user->ID, 'wcfm_membership_next_schedule', true );
            if ( $next_schedule ) {
                $subscription = (array) get_post_meta( $wcfm_membership, 'subscription', true );
                $is_free = isset( $subscription['is_free'] ) ? 'yes' : 'no';
                $subscription_type = isset( $subscription['subscription_type'] ) ? $subscription['subscription_type'] : 'one_time';

                if ( ( $is_free == 'no' ) && ( $subscription_type != 'one_time' ) ) {
                    $wcfm_vendor_data['membership_details']['membership_next_payment'] = date_i18n( wc_date_format(), $next_schedule );
                }

                $member_billing_period = get_user_meta( $user->ID, 'wcfm_membership_billing_period', true );
                $member_billing_cycle = get_user_meta( $user->ID, 'wcfm_membership_billing_cycle', true );
                if ( $member_billing_period && $member_billing_cycle ) {
                    $billing_period = isset( $subscription['billing_period'] ) ? $subscription['billing_period'] : '1';
                    $billing_period_count = isset( $subscription['billing_period_count'] ) ? $subscription['billing_period_count'] : '';
                    $billing_period_type = isset( $subscription['billing_period_type'] ) ? $subscription['billing_period_type'] : 'M';
                    $period_options = array( 'D' => 'days', 'M' => 'months', 'Y' => 'years' );

                    if ( $billing_period_count ) {
                        if ( $member_billing_period )
                            $member_billing_period = absint( $member_billing_period );
                        else
                            $member_billing_period = absint( $billing_period_count );
                        if ( ! $member_billing_cycle )
                            $member_billing_cycle = 1;
                        $remaining_cycle = ( $member_billing_period - $member_billing_cycle );
                        if ( $remaining_cycle == 0 ) {
                            $wcfm_vendor_data['membership_details']['membership_expiry_on'] = date_i18n( wc_date_format(), $next_schedule );
                        } else {
                            $expiry_time = strtotime( '+' . $remaining_cycle . ' ' . $period_options[$billing_period_type], $next_schedule );
                            $wcfm_vendor_data['membership_details']['membership_expiry_on'] = date_i18n( wc_date_format(), $expiry_time );
                        }
                    } else {

                        if ( $is_free == 'yes' ) {
                            $wcfm_vendor_data['membership_details']['membership_expiry_on'] = date_i18n( wc_date_format(), $next_schedule );
                        } else {
                            $wcfm_vendor_data['membership_details']['membership_expiry_on'] = __( 'Never Expire', 'wc-frontend-manager' );
                        }
                    }
                } else {
                    $wcfm_vendor_data['membership_details']['membership_expiry_on'] = __( 'Never Expire', 'wc-frontend-manager' );
                }
            }
        }

        return $wcfm_vendor_data;
    }

}
