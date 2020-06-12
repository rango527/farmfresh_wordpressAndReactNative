<?php
/**
 * Product Quantity for WooCommerce - Settings
 *
 * @version 1.7.0
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_Settings_PQ' ) ) :

class Alg_WC_Settings_PQ extends WC_Settings_Page {

	/**
	 * Constructor.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function __construct() {
		$this->id    = 'alg_wc_pq';
		$this->label = __( 'Product Quantity', 'product-quantity-for-woocommerce' );
		parent::__construct();
		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'maybe_unsanitize_option' ), PHP_INT_MAX, 3 );
	}

	/**
	 * maybe_unsanitize_option.
	 *
	 * @version 1.2.0
	 * @since   1.2.0
	 */
	function maybe_unsanitize_option( $value, $option, $raw_value ) {
		return ( ! empty( $option['alg_wc_pq_raw'] ) ? $raw_value : $value );
	}

	/**
	 * get_settings.
	 *
	 * @version 1.2.0
	 * @since   1.0.0
	 */
	function get_settings() {
		global $current_section;
		return array_merge( apply_filters( 'woocommerce_get_settings_' . $this->id . '_' . $current_section, array() ), array(
			array(
				'title'     => __( 'Reset Section', 'product-quantity-for-woocommerce' ),
				'type'      => 'title',
				'id'        => $this->id . '_' . $current_section . '_reset_options',
			),
			array(
				'title'     => __( 'Reset section settings', 'product-quantity-for-woocommerce' ),
				'desc'      => '<strong>' . __( 'Reset', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'        => $this->id . '_' . $current_section . '_reset',
				'default'   => 'no',
				'type'      => 'checkbox',
			),
			array(
				'type'      => 'sectionend',
				'id'        => $this->id . '_' . $current_section . '_reset_options',
			),
		) );
	}

	/**
	 * maybe_reset_settings.
	 *
	 * @version 1.3.0
	 * @since   1.0.0
	 */
	function maybe_reset_settings() {
		global $current_section;
		if ( 'yes' === get_option( $this->id . '_' . $current_section . '_reset', 'no' ) ) {
			foreach ( $this->get_settings() as $value ) {
				if ( isset( $value['id'] ) ) {
					$id = explode( '[', $value['id'] );
					delete_option( $id[0] );
				}
			}
			add_action( 'admin_notices', array( $this, 'admin_notice_settings_reset' ) );
		}
		if ( count( $this->get_settings() ) > 0 ) {
			foreach ( $this->get_settings() as $value ) {
				if( isset( $value['id'] ) && $value['id']== 'alg_wc_pq_disable_urls' ) {
					$ids = [];
					$urls = $_POST['alg_wc_pq_disable_urls'];
					$urls = array_map( 'trim', explode( PHP_EOL, $urls ) );
					if ( !empty($urls) && count( $urls ) ) {
						foreach ( $urls as $url ) {
							$id = url_to_postid( $url );
							if(!empty($id)) {
								$ids[] = $id;
							}
						}
					}
					if( count($ids) > 0 ) {
						update_option('alg_wc_pq_disable_urls_excluded_pids',implode(',',$ids));
					}else {
						update_option('alg_wc_pq_disable_urls_excluded_pids','');
					}
				}
			}
		}
	}

	/**
	 * admin_notice_settings_reset.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function admin_notice_settings_reset() {
		echo '<div class="notice notice-warning is-dismissible"><p><strong>' .
			__( 'Your settings have been reset.', 'product-quantity-for-woocommerce' ) . '</strong></p></div>';
	}

	/**
	 * Save settings.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function save() {
		parent::save();
		$this->maybe_reset_settings();
	}

}

endif;

return new Alg_WC_Settings_PQ();
