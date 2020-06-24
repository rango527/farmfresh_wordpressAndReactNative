<?php defined( 'ABSPATH' ) || exit;

use \Automattic\WooCommerce\Admin\Notes\WC_Admin_Note;

class WWP_WWS_Bundle {
	
	/**
	 * WC Admin Note unique name
	 * @since 1.11.5
	 */
	const NOTE_NAME = 'wc-admin-wwp-wws-bundle';
	
	/**
	 * Cron hook to be fired
	 * @since 1.11.5
	 */
	const CRON_HOOK = 'wwp_wc_admin_note_wws_bundle';

	/**
	 * WWP_WWS_Bundle constructor.
	 *
	 * @since 1.11.5
	 * @access public
	 */
	public function __construct() {
		
		// Trigger adding bundle note
		add_action( self::CRON_HOOK , array( $this , 'wws_bundle_note' ) );

		// Dismiss note if there are bundle plugins installed
		add_action( 'plugins_loaded' 	, array( $this , 'dismiss_note' ) , 11 );

	}

	/**
	 * Init cron hook to be fired after 30 days since activation.
	 *
	 * @since 1.11.5
	 * @access public
	 */
	public static function init_cron_hook() {

		if( !wp_next_scheduled( self::CRON_HOOK ) )
			wp_schedule_single_event( strtotime( '+7 days' ) , self::CRON_HOOK );

	}

	/**
	 * WWS Bundle WC Admin Note.
	 *
	 * @since 1.11.5
	 * @access public
	 */
	public function wws_bundle_note() {
		
		// If WC Admin is not active then don't proceed
		if( !WWP_Helper_Functions::is_wc_admin_active() ) return;

		// Don't add note if any WWS plugin is installed
		if( $this->check_bundled_plugins() ) return;

		$data_store = \WC_Data_Store::load( 'admin-note' );
		
		// We already have this note? Then exit, we're done.
		$note_ids = $data_store->get_notes_with_name( self::NOTE_NAME );
		if ( ! empty( $note_ids ) ) {
			return;
		}
		
		$bundle_url = 'https://wholesalesuiteplugin.com/bundle/?utm_source=wwp&utm_medium=wcinbox&utm_campaign=wcinboxwwsbundleupsell';

		$note_content = __(
			'Get the #1 rated wholesale solution for WooCommerce. Solve the big 3 problems facing store owners when wholesaling in WooCommerce.',
			'woocommerce-wholesale-prices'
		);

		$note = new WC_Admin_Note();
		$note->set_title( __( 'Wholesale Suite for WooCommerce', 'woocommerce-wholesale-prices' ) );
		$note->set_content( $note_content );
		$note->set_content_data( (object) array() );
		$note->set_type( WC_Admin_Note::E_WC_ADMIN_NOTE_INFORMATIONAL );
		$note->set_icon( 'trophy' );
		$note->set_name( self::NOTE_NAME );
		$note->set_source( 'woocommerce-admin' );
		$note->add_action( 'features-pricing', __( 'See Features & Pricing', 'woocommerce-wholesale-prices' ), $bundle_url, WC_Admin_Note::E_WC_ADMIN_NOTE_ACTIONED, true );
		$note->save();
        
	}

	/**
	 * Check if atleast 1 bundle plugin is installed then we dismiss the note.
	 *
	 * @since 1.11.5
	 * @access public
	 */
	public function dismiss_note() {

		// If WC Admin is not active then don't proceed
		if( !WWP_Helper_Functions::is_wc_admin_active() ) return;
		
		if( $this->check_bundled_plugins() ) {
			
			$data_store = \WC_Data_Store::load( 'admin-note' );
			$note_ids 	= $data_store->get_notes_with_name( self::NOTE_NAME );

			if ( ! empty( $note_ids ) ) {

				$note_id = current( $note_ids );
				$note = new WC_Admin_Note( $note_id );
				$note->set_status( WC_Admin_Note::E_WC_ADMIN_NOTE_ACTIONED );
				$note->save();

			}

		}
		
	}

	/**
	 * Check if atleast 1 bundle plugin is installed.
	 *
	 * @since 1.11.5
	 * @access public
	 */
	public function check_bundled_plugins() {

		return WWP_Helper_Functions::is_wwpp_installed() || WWP_Helper_Functions::is_wwof_installed() || WWP_Helper_Functions::is_wwlc_installed();
		
	}
	
    
}

return new WWP_WWS_Bundle();