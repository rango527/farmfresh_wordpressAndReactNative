<?php
/**
 * WCFM Delivery plugin core
 *
 * Plugin Capability Controller
 *
 * @author 		WC Lovers
 * @package 	wcfmd/core
 * @version   1.0.1
 */
 
class WCFMd_Capability {
	
	private $wcfm_capability_options = array();

	public function __construct() {
		global $WCFM, $WCFMd;
		
		$this->wcfm_capability_options = apply_filters( 'wcfm_capability_options_rules', (array) get_option( 'wcfm_capability_options' ) );
		
		add_filter( 'wcfm_is_allow_delivery', array( &$this, 'wcfmcap_is_allow_delivery' ), 500 );		
	}
	
  // WCFM wcfmcap Analytics
  function wcfmcap_is_allow_delivery( $allow ) {
  	$delivery = ( isset( $this->wcfm_capability_options['delivery'] ) ) ? $this->wcfm_capability_options['delivery'] : 'no';
  	if( $delivery == 'yes' ) return false;
  	return $allow;
  }
}