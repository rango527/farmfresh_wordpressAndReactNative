<?php
/**
 * Product Quantity for WooCommerce - Price by Qty Section Settings
 *
 * @version 1.7.3
 * @since   1.7.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Settings_Price_By_Qty' ) ) :

class Alg_WC_PQ_Settings_Price_By_Qty extends Alg_WC_PQ_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function __construct() {
		$this->id   = 'price_by_qty';
		$this->desc = __( 'Total Price by Quantity', 'product-quantity-for-woocommerce' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.7.3
	 * @since   1.7.0
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Total Price by Quantity Options', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'With this section you can display product price for different quantities in real time (i.e. price is automatically updated when customer changes product\'s quantity).', 'product-quantity-for-woocommerce' ) . ' ' .
					__( 'Please note that this section works for both <strong>simple type products</strong> and <strong>variable products</strong> as well, but you have to enable it from the tick below', 'product-quantity-for-woocommerce' ) . '<br>' .
					sprintf( __( 'Please note that this section is not designed to change product prices - if you need to change product\'s price depending on quantity in cart, we suggest using our %s plugin.', 'product-quantity-for-woocommerce' ),
						'<a href="https://wordpress.org/plugins/wholesale-pricing-woocommerce/" target="_blank">Wholesale Pricing for WooCommerce</a>' ),
				'type'     => 'title',
				'id'       => 'alg_wc_pq_qty_price_by_qty_options',
			),
			array(
				'title'    => __( 'Total Price by Quantity', 'product-quantity-for-woocommerce' ),
				'desc'     => '<strong>' . __( 'Enable section', 'product-quantity-for-woocommerce' ) . '</strong>',
				'id'       => 'alg_wc_pq_qty_price_by_qty_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Total Price by Quantity for variable product', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ) ,
				'id'       => 'alg_wc_pq_qty_price_by_qty_enabled_variable',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Allow defining unit on product level', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_price_by_qty_unit_input_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Allow defining unit on category level', 'product-quantity-for-woocommerce' ),
				'desc'     => __( 'Enable', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_price_by_cat_qty_unit_input_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Global label template: Singular', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Use string value here.', 'product-quantity-for-woocommerce' ),
				'desc'     => '',
				'id'       => 'alg_wc_pq_qty_price_by_qty_unit_singular',
				'type'     => 'text',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Global label template: Plural', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'Use string value here.', 'product-quantity-for-woocommerce' ),
				'desc'     => '',
				'id'       => 'alg_wc_pq_qty_price_by_qty_unit_plural',
				'type'     => 'text',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Template', 'product-quantity-for-woocommerce' ),
				'desc_tip' => __( 'You can use HTML here.', 'product-quantity-for-woocommerce' ),
				'desc'     => sprintf( __( 'Placeholders: %s. %s', 'product-quantity-for-woocommerce' ),
					'<code>' . implode( '</code>, <code>', array( '%price%', '%qty%', '%unit%' ) ) . '</code>', __('(The %unit% placeholder will read from 3 places, with priority-level defined: First, it will read if a unit is defined on Product Level, if not defined, then it will check if defined on Category Level, if not defined, it will read from Global level defined on this page. If your store is using the same unit for all products, you can use the unit here in the field without any placeholder)','product-quantity-for-woocommerce') ),
				'id'       => 'alg_wc_pq_qty_price_by_qty_template',
				'default'  => __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ),
				'type'     => 'textarea',
				'css'      => 'width:100%;',
				'alg_wc_pq_raw' => true,
			),
			array(
				'title'    => __( 'Position', 'product-quantity-for-woocommerce' ),
				'id'       => 'alg_wc_pq_qty_price_by_qty_position',
				'default'  => 'instead',
				'type'     => 'select',
				'class'    => 'chosen_select',
				'options'  => array(
					'before'  => __( 'Before the price', 'product-quantity-for-woocommerce' ),
					'instead' => __( 'Instead of the price', 'product-quantity-for-woocommerce' ),
					'after'   => __( 'After the price', 'product-quantity-for-woocommerce' ),
					'before_add_to_cart'   => __( 'Before add to cart', 'product-quantity-for-woocommerce' ),
					'after_add_to_cart'   => __( 'After add to cart', 'product-quantity-for-woocommerce' ),
					
				),
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_pq_qty_price_by_qty_options',
			),
		);
	}

}

endif;

return new Alg_WC_PQ_Settings_Price_By_Qty();
