<?php
class WCFM_REST_WC_Checkout_Controller extends WCFM_REST_Controller {
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
  protected $base = 'allowed-countries';
  

    /**
     * Stores the request.
     * @var array
     */
    protected $request = array();
    protected $required_checkout_fields;
    protected $oUserMeta;
    protected $allowed_countries;

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
   * Register the routes for notifications.
   */
  public function register_routes() {
    register_rest_route( $this->namespace, '/' . $this->base , array(
      array(
          'methods'             => WP_REST_Server::READABLE,
          'callback'            => array( $this, 'get_allowed_supported_countries' ),
          'permission_callback' => array( $this, 'get_checkout_field_permissions_check' ),
          'args'                => $this->get_collection_params(),
      )
    ) );


    // register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', array(
    //         'args' => array(
    //             'id' => array(
    //                 'description' => __( 'Unique identifier for the object.', 'wcfm-marketplace-rest-api' ),
    //                 'type'        => 'integer',
    //             ),
    //         ),
    //         array(
    //             'methods'             => WP_REST_Server::CREATABLE,
    //             'callback'            => array( $this, 'review_manage' ),
    //             'args'                => $this->get_collection_params(),
    //             'permission_callback' => array( $this, 'review_manage_permissions_check' ),
    //         )
    //       )
    //     );
  }

  /**
     * Checking if have any permission to view enquiry
     *
     * @since 1.0.0
     *
     * @return boolean
     */
  public function get_checkout_field_permissions_check() {     
    if( !is_user_logged_in() )
      return false;

    return true;
  }

    
    /**
     * @param string $key
     *
     * @return mixed
     */
  public function get_allowed_supported_countries()
  {
      if (!empty($this->allowed_countries)) {
          return $this->allowed_countries;
      }

      $return_object = array();
      
      $countries_object    = new \WC_Countries();
      $raw_selling_counries = $countries_object->get_allowed_countries();
      $raw_selling_counries_state = $countries_object->get_allowed_country_states();

      $raw_shipping_counries = $countries_object->get_shipping_countries();
      $raw_shipping_counries_state = $countries_object->get_shipping_country_states();

      foreach ($raw_selling_counries as $country_code => $country_name) {
        $return_object['billing'][$country_code]['name'] = $country_name;
        $return_object['billing'][$country_code]['states'] = $raw_selling_counries_state[$country_code];
      }

      foreach ($raw_shipping_counries as $country_code => $country_name) {
        $return_object['shipping'][$country_code]['name'] = $country_name;
        $return_object['shipping'][$country_code]['states'] = $raw_shipping_counries_state[$country_code];
      }

      return $return_object;
  }

  // public function review_manage($request) {
    
  // }
  
}
