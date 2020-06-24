<?php defined( 'ABSPATH' ) || exit;

use \Automattic\WooCommerce\Admin\Notes\WC_Admin_Note;

class WWP_Install_ACFW {
	
	/**
	 *  WC Admin Note unique name
	 * @since 1.11.5
	 */
	const NOTE_NAME = 'wc-admin-wwp-install-acfwf';
	
	/**
	 * WWP_Install_ACFW constructor.
	 *
	 * @since 1.11.5
	 * @access public
	 */
	public function __construct() {
		
		// Show Note
		add_action( 'plugins_loaded' , array( $this , 'install_acfwf_note' ) , 11 );

		// Hide Note
		add_action( 'plugins_loaded' , array( $this , 'dismiss_install_acfwf_note' ) , 11 );

		// Set flag to dismiss note
		add_action( 'woocommerce_note_action_install-acfw' 		, array( $this , 'dismiss_note_on_click' ) );
		
	}

	/**
	 * Check if WWP_SHOW_INSTALL_ACFWF_NOTICE is set to yes then show note.
	 * For some reason hooking into the cron action won't fire the install url so workaround is to use a flag and fire the add note on init action.
	 * Create Note Condition: - Current user is admin
	 * 						  - WWP_SHOW_INSTALL_ACFWF_NOTICE flag is 'yes'
	 * 						  - ACFWF is not installed
	 * 
	 * @since 1.11.5
	 * @access public
	 */
	public function install_acfwf_note() {

		// If WC Admin is not active then don't proceed
		if( !WWP_Helper_Functions::is_wc_admin_active() ) return;

		if( 
			get_option( WWP_SHOW_INSTALL_ACFWF_NOTICE ) === 'yes' &&
			current_user_can( 'administrator' ) &&
			!WWP_Helper_Functions::is_acfwf_installed() 
		) {
				
			$data_store = \WC_Data_Store::load( 'admin-note' );
			
			// We already have this note? Then exit, we're done.
			$note_ids = $data_store->get_notes_with_name( self::NOTE_NAME );
			if ( ! empty( $note_ids ) ) {
				return;
			}
			
			$learn_more			= 'https://advancedcouponsplugin.com/?utm_source=wwp&utm_medium=wcinbox&utm_campaign=wcinboxacfwflearnmorebutton';
			$install_acfw_url 	= htmlspecialchars_decode( wp_nonce_url( admin_url() . 'update.php?action=install-plugin&plugin=advanced-coupons-for-woocommerce-free', 'install-plugin_advanced-coupons-for-woocommerce-free') );
	
			$note_content = __(
				'This free plugin extends your coupon features. Market your store better with WooCommerce coupons. Install the free plugin now.',
				'woocommerce-wholesale-prices'
			);
	
			$note = new WC_Admin_Note();
			$note->set_title( __( 'Install Advanced Coupons (FREE PLUGIN)', 'woocommerce-wholesale-prices' ) );
			$note->set_content( $note_content );
			$note->set_content_data( (object) array() );
			$note->set_type( WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
			$note->set_icon( 'cloud-download' );
			$note->set_name( self::NOTE_NAME );
			$note->set_source( 'woocommerce-admin' );
			$note->add_action( 'learn-about-acfw', __( 'Lean more', 'woocommerce-wholesale-prices' ), $learn_more , WC_Admin_Note::E_WC_ADMIN_NOTE_UNACTIONED, false );
			$note->add_action( 'install-acfw', __( 'Install Now', 'woocommerce-wholesale-prices' ), $install_acfw_url, WC_Admin_Note::E_WC_ADMIN_NOTE_ACTIONED, true );
			$note->save();

		}
		
	}

	/**
	 * Dismisses the note.
	 * Conditions: 	- If notice is dismissed.
	 * 				- If user is not admin.
	 * 				- If ACFWF is installed.
	 * 
	 * Note: Added a condition to show the note again if WWP_SHOW_INSTALL_ACFWF_NOTICE equan to yes
	 *
	 * @since 1.11.5
	 * @access public
	 */
	public function dismiss_install_acfwf_note() {
		
		// If WC Admin is not active then don't proceed
		if( !WWP_Helper_Functions::is_wc_admin_active() ) return;
		
		$data_store = \WC_Data_Store::load( 'admin-note' );
		$note_ids 	= $data_store->get_notes_with_name( self::NOTE_NAME );

		if ( ! empty( $note_ids ) ) {

			$note_id = current( $note_ids );
			$note = new WC_Admin_Note( $note_id );

			if( 
				!current_user_can( 'administrator' ) || 
				get_option( WWP_SHOW_INSTALL_ACFWF_NOTICE ) === 'no' || 
				WWP_Helper_Functions::is_acfwf_installed() 
			)
				$note->set_status( WC_Admin_Note::E_WC_ADMIN_NOTE_ACTIONED );
			else if( get_option( WWP_SHOW_INSTALL_ACFWF_NOTICE ) === 'yes' )
				$note->set_status( WC_Admin_Note::E_WC_ADMIN_NOTE_UNACTIONED );

			$note->save();

		}
		
	}

	
	/**
	 * When "Install Now" button is clicked, then set flat that dismiss the note and notice.
	 *
	 * @since 1.11.5
	 * @param WC_Admin_Note $note Note being acted upon.
	 * @access public
	 */
	public function dismiss_note_on_click( $note ) {

		update_option( WWP_SHOW_INSTALL_ACFWF_NOTICE , 'no' );

	}
	
}

return new WWP_Install_ACFW();