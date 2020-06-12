<?php
/**
 * Product Quantity for WooCommerce - Core Class
 *
 * @version 1.8.1
 * @since   1.0.0
 * @author  WPWhale
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_PQ_Core' ) ) :

class Alg_WC_PQ_Core {

	/**
	 * Excluded product ids.
	 *
	 * @var   array
	 * @since 1.0.0
	 */
	public $excluded_pids = array();

	/**
	 * Constructor.
	 *
	 * @version 1.8.1
	 * @since   1.0.0
	 * @todo    [fix] mini-cart number of items for decimal qty
	 * @todo    [dev] implement `is_any_section_enabled()`
	 * @todo    [dev] code refactoring: split this into more separate files (e.g. `class-alg-wc-pq-checker.php` etc.)
	 * @todo    [dev] (maybe) pre-load all options (i.e. `init_options()` and `$this->options`)
	 * @todo    [dev] (maybe) bundle products
	 * @todo    [feature] quantity per category (and/or tag) (i.e. not per individual products)
	 * @todo    [feature] implement `force_js_check_exact_qty()`
	 * @todo    [feature] add "treat variable as simple" option
	 * @todo    [feature] quantities by user roles
	 * @todo    [feature] add option to replace product's add to cart form on archives with form from the single product page
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_pq_enabled', 'yes' ) ) {
			// Disable plugin by URL
			if ( '' != ( $urls = get_option( 'alg_wc_pq_disable_urls', '' ) ) ) {
				$urls = array_map( 'trim', explode( PHP_EOL, $urls ) );
				$url  = $_SERVER['REQUEST_URI'];
				if ( in_array( $url, $urls ) ) {

					return;
				}
				
				$ids = get_option( 'alg_wc_pq_disable_urls_excluded_pids', '' );
				$ids = array_map( 'trim', explode( ',', $ids ) );
				$this->excluded_pids = $ids;
			}
			

			// Core
			$this->messenger = require_once( 'class-alg-wc-pq-messenger.php' );
			if (
				'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_exact_qty_allowed_section_enabled', 'no' ) ||
				'yes' === get_option( 'alg_wc_pq_exact_qty_disallowed_section_enabled', 'no' )
			) {
				if ( 'yes' === get_option( 'alg_wc_pq_validate_on_checkout', 'yes' ) ) {
					add_action( 'woocommerce_checkout_process',                                array( $this, 'check_order_quantities' ) );
				}
				add_action( 'woocommerce_before_cart',                                         array( $this, 'check_order_quantities' ) );
				if ( 'yes' === get_option( 'alg_wc_pq_stop_from_seeing_checkout', 'no' ) ) {
					add_action( 'wp',                                                          array( $this, 'block_checkout' ), PHP_INT_MAX );
				}
			}
			// Min/max
			if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) || 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
				add_filter( 'woocommerce_available_variation',                                 array( $this, 'set_quantity_input_min_max_variation' ), PHP_INT_MAX, 3 );
				if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
					add_filter( 'woocommerce_quantity_input_min',                              array( $this, 'set_quantity_input_min' ), PHP_INT_MAX, 2 );
				}
				if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
					add_filter( 'woocommerce_quantity_input_max',                              array( $this, 'set_quantity_input_max' ), PHP_INT_MAX, 2 );
				}
				// Force on archives
				if ( 'disabled' != ( $this->force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' ) ) ) {
					add_filter( 'woocommerce_loop_add_to_cart_args',                           array( $this, 'force_qty_on_loop' ), PHP_INT_MAX, 2 );
				}
			}
			// Step
			if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
				add_filter( 'woocommerce_quantity_input_step',                                 array( $this, 'set_quantity_input_step' ), PHP_INT_MAX, 2 );
			}
			// Scripts
			require_once( 'class-alg-wc-pq-scripts.php' );
			// For cart & for `input_value`
			add_filter( 'woocommerce_quantity_input_args',                                     array( $this, 'set_quantity_input_args' ), PHP_INT_MAX, 2 );
			// Decimal qty
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
				add_action( 'init',                                                            array( $this, 'float_stock_amount' ), PHP_INT_MAX );
			}
			// Sold individually
			if ( 'yes' === get_option( 'alg_wc_pq_all_sold_individually_enabled', 'no' ) ) {
				add_filter( 'woocommerce_is_sold_individually',                                '__return_true', PHP_INT_MAX );
			}
			// Styling
			if ( '' != get_option( 'alg_wc_pq_qty_input_style', '' ) ) {
				add_action( 'wp_head',                                                         array( $this, 'style_qty_input' ), PHP_INT_MAX );
			}
			// Hide "Update cart" button
			if ( 'yes' === get_option( 'alg_wc_pq_qty_hide_update_cart', 'no' ) ) {
				add_action( 'wp_head',                                                         array( $this, 'hide_update_cart_button' ), PHP_INT_MAX );
			}
			// "Add to cart" validation
			if ( 'notice' === get_option( 'alg_wc_pq_add_to_cart_validation', 'disable' ) ) {
				add_filter( 'woocommerce_add_to_cart_validation',                              array( $this, 'validate_on_add_to_cart' ), PHP_INT_MAX, 4 );
			} elseif ( 'correct' === get_option( 'alg_wc_pq_add_to_cart_validation', 'disable' ) ) {
				add_filter( 'woocommerce_add_to_cart_quantity',                                array( $this, 'correct_on_add_to_cart' ), PHP_INT_MAX, 2 );
			}
			// Qty rounding
			if ( 'no' != ( $this->round_on_add_to_cart = get_option( 'alg_wc_pq_round_on_add_to_cart', 'no' ) ) ) {
				add_filter( 'woocommerce_add_to_cart_quantity',                                array( $this, 'round_on_add_to_cart' ), PHP_INT_MAX, 2 );
			}
			// Dropdown
			if ( 'yes' === get_option( 'alg_wc_pq_qty_dropdown', 'no' ) ) {
				add_filter( 'wc_get_template',                                                 array( $this, 'replace_quantity_input_template' ), PHP_INT_MAX, 5 );
			}
			// Shortcodes
			require_once( 'class-alg-wc-pq-shortcodes.php' );
			// Quantity info
			require_once( 'class-alg-wc-pq-qty-info.php' );
			// Admin columns
			require_once( 'class-alg-wc-pq-admin.php' );
			// Price by Qty
			if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_enabled', 'no' ) ) {
				add_action( 'wp_ajax_'        . 'alg_wc_pq_update_price_by_qty',               array( $this, 'ajax_price_by_qty' ) );
				add_action( 'wp_ajax_nopriv_' . 'alg_wc_pq_update_price_by_qty',               array( $this, 'ajax_price_by_qty' ) );
			}
			// Price by Qty
			// if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_enabled_variable', 'no' ) ) {
			// 	add_action( 'wp_ajax_'        . 'alg_wc_pq_update_price_by_qty_variable',               array( $this, 'ajax_price_by_qty_variable' ) );
			// 	add_action( 'wp_ajax_nopriv_' . 'alg_wc_pq_update_price_by_qty_variable',               array( $this, 'ajax_price_by_qty_variable' ) );
			// }
			// Order item meta
			if ( 'yes' === get_option( 'alg_wc_pq_save_qty_in_order_item_meta', 'no' ) ) {
				add_action( 'woocommerce_new_order_item',                                      array( $this, 'add_qty_to_order_item_meta' ), PHP_INT_MAX, 3 );
			}
			
			add_filter( 'woocommerce_paypal_line_item', array( $this, 'change_paypal_line_item_quantity_type' ), 100, 5 );
		}
	}

	
	/**
	 * disable_product_id_by_url_option.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] maybe add option to overwrite the default `_qty` meta (as it's `absint( $order_item['qty'] )`)
	 */
	function disable_product_id_by_url_option( $product_id ) {
		if ( count($this->excluded_pids) > 0 ) {
			if ( in_array( $product_id, $this->excluded_pids ) ) {
				return true;
			}
		}
		return false;
	}
	
	function change_paypal_line_item_quantity_type( $item, $item_name, $quantity, $amount, $item_number ) {
		if (strpos($quantity, '.')) {
			$quantity = (float) $quantity;
			$totamount = $amount * $quantity;
			$item['quantity'] = 1;
			$item['amount'] = $totamount;
		}
		return $item;
	}

	/**
	 * add_qty_to_order_item_meta.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] maybe add option to overwrite the default `_qty` meta (as it's `absint( $order_item['qty'] )`)
	 */
	function add_qty_to_order_item_meta( $item_id, $order_item, $order_id ) {
		wc_add_order_item_meta( $item_id, get_option( 'alg_wc_pq_save_qty_in_order_item_meta_key', '_alg_wc_pq_qty' ), $order_item['qty'] );
	}

	/**
	 * round_on_add_to_cart.
	 *
	 * @version 1.6.2
	 * @since   1.6.2
	 * @todo    [feature] (maybe) add `precision` option
	 */
	function round_on_add_to_cart( $quantity, $product_id ) {
		$func = $this->round_on_add_to_cart;
		return $func( $quantity );
	}

	/**
	 * force_qty_on_loop.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function force_qty_on_loop( $args, $product ) {
		$args['quantity'] = ( 'min' === $this->force_on_loop ?
			$this->set_quantity_input_min( $args['quantity'], $product ) : $this->set_quantity_input_max( $args['quantity'], $product ) );
		return $args;
	}

	/**
	 * replace_quantity_input_template.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function replace_quantity_input_template( $located, $template_name, $args, $template_path, $default_path ) {
		if ( 'global/quantity-input.php' === $template_name ) {
			return alg_wc_pq()->plugin_path() . '/includes/templates/global/quantity-input.php';
		}
		return $located;
	}
	
	/**
	 * get_dropdown_option_label.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function get_dropdown_option_label( $id = 0 ) {
		if ( empty($id) ) {
			return '';
		}
		$product = wc_get_product( $id ); 
		$label_template_singular = '';
		$label_template_plural   = '';
		if ( $product && 'yes' === get_option( 'alg_wc_pq_qty_dropdown_label_template_is_per_product', 'no' ) ) {
			$product_or_parent_id    = ( $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id() );
			$label_template_singular = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_singular', true );
			$label_template_plural   = get_post_meta( $product_or_parent_id, '_alg_wc_pq_qty_dropdown_label_template_plural',   true );
		}
		if ( '' === $label_template_singular ) {
			$label_template_singular = get_option( 'alg_wc_pq_qty_dropdown_label_template_singular', '%qty%' );
		}
		if ( '' === $label_template_plural ) {
			$label_template_plural   = get_option( 'alg_wc_pq_qty_dropdown_label_template_plural',   '%qty%' );
		}
		return array( 'singular'=> $label_template_singular , 'plural'=> $label_template_plural );
	}

	/**
	 * get_cart_item_quantities.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 */
	function get_cart_item_quantities( $product_id = 0, $quantity = 0 , $sort_by_sumup_variation = false) {
		if ( ! isset( WC()->cart ) ) {
			$cart_item_quantities = array();
		} else {
			$cart_item_quantities = WC()->cart->get_cart_item_quantities();
			if ( empty( $cart_item_quantities ) || ! is_array( $cart_item_quantities ) ) {
				$cart_item_quantities = array();
			}
		}
		
		if ( count($cart_item_quantities) > 0 ) {
			if ( count( $this->excluded_pids ) > 0 ) {
				foreach ( $this->excluded_pids as $id ) {
					unset($cart_item_quantities[$id]);
				}
			}				
		}
		

		if ( 0 != $product_id ) {
			if ( ! isset( $cart_item_quantities[ $product_id ] ) ) {
				$cart_item_quantities[ $product_id ] = 0;
			}
			$cart_item_quantities[ $product_id ] += $quantity;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_sum_variations', 'no' ) || $sort_by_sumup_variation ) {
			if ( 0 != $product_id && ( $product = wc_get_product( $product_id ) ) ) {
				$children   = $product->get_children();
				$qty_to_add = 0;
				foreach ( $cart_item_quantities as $cart_item_product_id => $cart_item_quantity ) {
					if ( $cart_item_product_id != $product_id && in_array( $cart_item_product_id, $children ) ) {
						$qty_to_add += $cart_item_quantity;
						$cart_item_quantities[ $cart_item_product_id ] = 0;
					}
				}
				$cart_item_quantities[ $product_id ] += $qty_to_add;
			}
		}
		return $cart_item_quantities;
	}

	/**
	 * correct_on_add_to_cart.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [fix] (important) fix "X products have been added to your cart." notice
	 * @todo    [dev] (important) maybe need to add a notice on corrected qty = 0
	 * @todo    [dev] (important) (maybe) "Exact quantities" should be executed first? (same in `validate_on_add_to_cart()`, `block_checkout()` and `check_order_quantities()`)
	 */
	function correct_on_add_to_cart( $quantity, $product_id ) {
		// disable if url excluded
		if( $this->disable_product_id_by_url_option($product_id) ) {
			return $quantity;
		}
		// Prepare data
		$cart_item_quantities = $this->get_cart_item_quantities( $product_id, $quantity );
		$cart_total_quantity  = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
		$cart_item_quantity   = $cart_item_quantities[ $product_id ];
		// Min & Max
		foreach ( array( 'min', 'max' ) as $min_or_max ) {
			if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
				// Cart total quantity
				if ( ! $this->check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) ) {
					return ( $this->get_min_max_cart_total_qty( $min_or_max ) - ( $cart_total_quantity - $quantity ) );
				}
				// Per item quantity
				if ( ! $this->check_product_min_max( $product_id, $min_or_max, $cart_item_quantity ) ) {
					return $this->get_product_qty_min_max( $product_id, 0, $min_or_max );
				}
			}
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			// Cart total
			if ( $quantity != ( $fixed_qty = $this->check_step_cart_total_qty( $cart_total_quantity, true, $quantity ) ) ) {
				return $fixed_qty;
			}
			// Products
			if ( $quantity != ( $fixed_qty = $this->check_product_step( $product_id, $quantity, true ) ) ) {
				return $fixed_qty;
			}
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				if ( $quantity != ( $fixed_qty = $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity, true ) ) ) {
					return $fixed_qty;
				}
			}
		}
		return $quantity;
	}

	function get_cartitem_by_category()
	{
		$category = array();
		$cart_all_item_quantities = WC()->cart->get_cart_item_quantities();

		if (isset($cart_all_item_quantities) && !empty($cart_all_item_quantities))
		{
			foreach ($cart_all_item_quantities as $product_id => $product_count)
			{
				if( $this->disable_product_id_by_url_option($product_id) ) {
					continue;
				}
				$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'ids'));
				if(isset($term_list) && count($term_list) > 0)
				{
					foreach ($term_list as $term)
					{
						if(isset($category[$term]) && !empty($category[$term]))
						{
							$category[$term] = (int) $category[$term] + $product_count;
						}
						else
						{
							$category[$term] = $product_count;
						}
					}
				}
			}
		}

		if (count($category) > 0)
		{
			return $category;
		}
		else
		{
			return false;
		}
	}
	
	function get_cartitem_groupby_parent_id()
	{
		$main_products = array();
		$cart_all_item_quantities = WC()->cart->get_cart_item_quantities();

		if (isset($cart_all_item_quantities) && !empty($cart_all_item_quantities))
		{
			foreach ($cart_all_item_quantities as $product_id => $product_count)
			{
				if( $this->disable_product_id_by_url_option($product_id) ) {
					continue;
				}
				$product = wc_get_product( $product_id );
				$product_type = $product->post_type;
				if ( $product_type == 'product_variation' ) {
					$product_id = $product->parent_id;
					$allvar_cart_item_quantities = $this->get_cart_item_quantities( $product_id, 0, true );
					$cart_item_quantity = $allvar_cart_item_quantities[ $product_id ];
					
					if( ! in_array( $product_id, array_keys($main_products) ) ) {
						$main_products[$product_id] = $cart_item_quantity;
					}
					
				}
			}
		}

		if (count($main_products) > 0)
		{
			return $main_products;
		}
		else
		{
			return false;
		}
	}
	
	
	/**
	 * validate_on_add_to_cart.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] (maybe) separate messages for min/max (i.e. different from "cart" messages)?
	 */
	function validate_on_add_to_cart( $passed, $product_id, $quantity, $variation_id = 0 ) {
		// disable if url excluded
		if( $this->disable_product_id_by_url_option( $product_id ) ) {
			return $passed;
		}
		// Prepare data
		if ( ! isset( $cart_item_quantities ) ) {
			$cart_item_quantities = $this->get_cart_item_quantities( $product_id, $quantity );
			$cart_total_quantity  = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
			$cart_item_quantity   = $cart_item_quantities[ $product_id ];
		}
		
		$cartitem_by_category = $this->get_cartitem_by_category();

		// get categories 
		$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'ids'));
		
		foreach ( array( 'min', 'max' ) as $min_or_max ) {
			if ( 'yes' === get_option( 'alg_wc_pq_' .$min_or_max. '_section_enabled', 'no' ) && 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_per_cat_item_quantity_per_product' , 'no' ) ) {
				if(isset($term_list) && count($term_list) > 0)
				{
					foreach ($term_list as $term)
					{
						$t_id = $term;
						$term_meta = get_option( "taxonomy_product_cat_$t_id" );
						if (!empty($term_meta) && is_array($term_meta))
						{
							$term_qty = (isset($cartitem_by_category[$term]) ? (int) $cartitem_by_category[$term] + $quantity : $quantity);
							$alg_wc_pq_min_or_max = 'alg_wc_pq_'.$min_or_max;
							$cat_quantity = (int) $term_meta[$alg_wc_pq_min_or_max];
							if ($cat_quantity > 0)
							{
								$trm = get_term_by( 'id', $t_id, 'product_cat' );
								if ($min_or_max=='max')
								{
									if($cat_quantity < $term_qty)
									{
										$message_template = get_option( $alg_wc_pq_min_or_max.'_cat_message',
											__( 'Maximum allowed quantity for category '.$trm->name.' is '.$cat_quantity.'. Your current quantity for this category is '.$term_qty.'.', 'product-quantity-for-woocommerce' ) );
										$_notice = str_replace(array('%category_title%','%max_per_item_quantity%','%item_quantity%'),array($trm->name,$cat_quantity,$term_qty),$message_template);
										wc_add_notice( $_notice, 'error' );
										return false;
									}
								}
								
							}
						}
					}
				}
			}
			
		}
		
		// Min & Max
		foreach ( array( 'min', 'max' ) as $min_or_max ) {
			if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
				// Cart total quantity
				if ( ! $this->check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) ) {
					$this->messenger->print_message( $min_or_max . '_cart_total_quantity', false, $this->get_min_max_cart_total_qty( $min_or_max ), $cart_total_quantity );
					return false;
				}
				// Per item quantity
				if ( ! $this->check_product_min_max( $product_id, $min_or_max, $cart_item_quantity ) ) {
					$this->messenger->print_message( $min_or_max . '_per_item_quantity', false, $this->get_product_qty_min_max( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
					return false;
				}
			}
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			// Cart total
			if ( ! $this->check_step_cart_total_qty( $cart_total_quantity ) ) {
				$this->messenger->print_message( 'step_cart_total_quantity', false, $this->get_step_cart_total_qty(), $cart_total_quantity );
				return false;
			}
			// Products
			if ( ! $this->check_product_step( $product_id, $quantity ) ) {
				$this->messenger->print_message( 'step_quantity', false, $this->get_product_qty_step( $product_id ), $quantity, $product_id );
				return false;
			}
		}
		
		
		
		// Exact quantities variation
		if ($variation_id > 0)
		{
			foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
				if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
					if ( ! $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity, false, $variation_id ) ) {
						$this->messenger->print_message( 'exact_qty_' . $allowed_or_disallowed, false, $this->get_product_exact_qty( $product_id, $allowed_or_disallowed, '', $variation_id ), $quantity, $variation_id );
						return false;
					}
				}
			}

			
			// Min & Max
			
			$cart_item_quantities = $this->get_cart_item_quantities( $product_id, $quantity, true );
			$cart_total_quantity  = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
			$cart_item_quantity   = $cart_item_quantities[ $product_id ];
			
			foreach ( array( 'max' ) as $min_or_max ) {
				if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
					// Cart total quantity
					if ( ! $this->check_min_max_cart_total_allvar_qty( $min_or_max, $cart_total_quantity ) ) {
						$this->messenger->print_message( $min_or_max . '_cart_total_quantity', false, $this->get_min_max_cart_total_allvar_qty( $min_or_max ), $cart_total_quantity );
						return false;
					}
					// Per item quantity
					if ( ! $this->check_product_min_max_allvar( $product_id, $min_or_max, $cart_item_quantity ) ) {
						$this->messenger->print_message( $min_or_max . '_per_item_quantity', false, $this->get_product_qty_min_max_allvar( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
						return false;
					}
				}
			}
		} else {
			// Exact quantities
			foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
				if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
					if ( ! $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity ) ) {
						$this->messenger->print_message( 'exact_qty_' . $allowed_or_disallowed, false, $this->get_product_exact_qty( $product_id, $allowed_or_disallowed ), $quantity, $product_id );
						return false;
					}
				}
			}
		}
		
		
		
		// Passed
		return $passed;
	}

	/**
	 * style_qty_input.
	 *
	 * @version 1.6.0
	 * @since   1.3.0
	 */
	function style_qty_input() {
		echo '<style>' . 'input.qty,select.qty{' . get_option( 'alg_wc_pq_qty_input_style', '' ) . '}</style>';
	}

	/**
	 * hide_update_cart_button.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] add option to make qty input readonly
	 * @todo    [dev] add option to make "Update cart" button always enabled (i.e. even in case if quantities were not changed)
	 */
	function hide_update_cart_button() {
		echo '<style>' . '.cart button[name="update_cart"] { display: none; }</style>';
	}

	/**
	 * float_stock_amount.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function float_stock_amount() {
		remove_filter( 'woocommerce_stock_amount', 'intval' );
		add_filter(    'woocommerce_stock_amount', 'floatval' );
	}

	/**
	 * set_quantity_input_args.
	 *
	 * @version 1.7.0
	 * @since   1.2.0
	 * @todo    [dev] re-check do we really need to set `step` here?
	 */
	function set_quantity_input_args( $args, $product ) {
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			$args['min_value']   = $this->set_quantity_input_min(  $args['min_value'], $product );
		} elseif ( 'yes' === get_option( 'alg_wc_pq_force_cart_min_enabled', 'no' ) ) {
			$args['min_value']   = 1;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			$args['max_value']   = $this->set_quantity_input_max(  $args['max_value'], $product );
		}
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			$args['step']        = $this->set_quantity_input_step( $args['step'],      $product );
		}
		if ( 'disabled' != ( $force_on_single = get_option( 'alg_wc_pq_force_on_single', 'disabled' ) ) && is_product() ) {
			
			if('default' === ( $force_on_single = get_option( 'alg_wc_pq_force_on_single', 'disabled' ))) {
				$args['input_value'] = $this->get_product_qty_default( $product->get_id(), $args['input_value'] );
			} else {
				$args['input_value'] = ( 'min' === $force_on_single ?
				$this->set_quantity_input_min( $args['min_value'], $product ) : $this->set_quantity_input_max( $args['max_value'], $product ) );
			}
		}
		
		if ( 'disabled' === ( $force_on_single = get_option( 'alg_wc_pq_force_on_single', 'disabled' ) ) && is_product() && 'no' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
			$args['min_value']   = 1;
		}
		if( (is_shop() || is_product_tag() || is_product_category() ) ) {
			
			if ('yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' )) {
				if ( !isset($args['min_value']) || $args['min_value']<= 0  || 'disabled' == ( $force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' ) ) ) {
					$args['min_value'] = 1;
				}
			}
			
			if ('yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' )) {
				if ( !isset($args['max_value']) || $args['max_value'] <= 0) {
					$stock = $product->get_stock_quantity();
					if ( $stock > 0 ) {
						$args['max_value'] = $stock;
					}
				}
			}
			
			if ( 'disabled' != ( $force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' ) ) ) {
				if('default' === ( $force_on_loop = get_option( 'alg_wc_pq_force_on_loop', 'disabled' ))) {
					$args['input_value'] = $this->get_product_qty_default( $product->get_id(), $args['input_value'] );
				} else {
					$args['input_value'] = ( 'min' === $force_on_loop ?
						$this->set_quantity_input_min( $args['min_value'], $product ) : $this->set_quantity_input_max( $args['max_value'], $product ) );
				}
			}
		}
		$args['product_id'] = ( $product ? $product->get_id() : 0 );

		return $args;
	}

	/**
	 * get_product_qty_step.
	 *
	 * @version 1.8.0
	 * @since   1.1.0
	 */
	function get_product_qty_step( $product_id, $default_step = 0, $variation_id = 0 ) {
		$per_product_id = $product_id;
		if( $variation_id > 0 ) {
			$per_product_id = $variation_id;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			if ( 'yes' === apply_filters( 'alg_wc_pq_quantity_step_per_product', 'no' ) && 0 != ( $step_per_product = apply_filters( 'alg_wc_pq_quantity_step_per_product_value', 0, $per_product_id ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_step', $step_per_product, $per_product_id );
			} else if ( 'yes' === apply_filters( 'alg_wc_pq_quantity_step_per_product_cat', 'no' ) && 0 != ( $step_per_product = apply_filters( 'alg_wc_pq_quantity_step_per_product_cat_value', 0, $product_id ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_step', $step_per_product, $product_id );
			}  {
				return apply_filters( 'alg_wc_pq_get_product_qty_step', ( 0 != ( $step_all_products = get_option( 'alg_wc_pq_step', 0 ) ) ? $step_all_products : $default_step ), $product_id );
			}
		} else {
			return $default_step;
		}
	}

	/**
	 * set_quantity_input_step.
	 *
	 * @version 1.1.0
	 * @since   1.1.0
	 */
	function set_quantity_input_step( $step, $_product ) {
		return $this->get_product_qty_step( $this->get_product_id( $_product ), $step );
	}

    /**
     * Find matching product variation
     *
     * @param $product_id
     * @param $attributes
     * @return int
     */
    function find_matching_product_variation_id($product_id, $attributes)
    {
		if (is_array($attributes) && count($attributes) > 0)
		{
			$product = new WC_Product_Variable($product_id);
			$variations = $product->get_available_variations();

			if (count($variations) > 0)
			{
				foreach ($variations as $variation) 
				{
					$vAttributes =  $variation['attributes'];
					if ( count($vAttributes) > 0 && count($vAttributes) == count($attributes) )
					{
						$diff=array_diff($attributes,$vAttributes);
						if(empty($diff))
						{
							return $variation['display_price'];
						}
					}
				}
			}
		}
		return false;
    }
	
	/**
	 * get_category_unit_label.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 * @todo    [dev] non-simple products (i.e. variable, grouped etc.)
	 */
	function get_category_unit_label( $product_id ) {
		$return = array();
		$term_list = wp_get_post_terms($product_id,'product_cat',array('fields'=>'ids'));
		if(isset($term_list) && count($term_list) > 0)
		{
			foreach ($term_list as $term)
			{
				$term_meta = get_option( "taxonomy_product_cat_$term" );
				if (!empty($term_meta) && is_array($term_meta))
				{
					$singular_meta = 'alg_wc_pq_category_unit_singular';
					$plural_meta = 'alg_wc_pq_category_unit_plural';
					$singular_unit = $term_meta[$singular_meta];
					$plural_unit = $term_meta[$plural_meta];
					if ( !empty($singular_unit) && !empty($plural_unit) )
					{
						$return['singular'] = $singular_unit;
						$return['plural'] = $plural_unit;
						return $return;
					}
				}
			}
		}
		return false;
	}

	/**
	 * ajax_price_by_qty.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 * @todo    [dev] non-simple products (i.e. variable, grouped etc.)
	 * @todo    [dev] customizable position (instead of the price; after the price, before the price etc.) (NB: maybe do not display for qty=1)
	 * @todo    [dev] add option to disable "price by qty" on initial screen (i.e. before qty input was changed)
	 * @todo    [dev] (maybe) add sale price
	 * @todo    [dev] (maybe) add optional "in progress" message (for slow servers)
	 */
	function ajax_price_by_qty( $param ) {
		if ( isset( $_POST['alg_wc_pq_qty'] ) && '' !== $_POST['alg_wc_pq_qty'] && ! empty( $_POST['alg_wc_pq_id'] ) ) {
			$product = wc_get_product( $_POST['alg_wc_pq_id']  );
 			$product_id = $_POST['alg_wc_pq_id'];
			
            $pro_type =  $product->get_type();
            $selectedval = $_POST['selected_val'];
            $selectedattribute = $_POST['attribute'];
			
			$unit = get_option( 'alg_wc_pq_qty_price_by_qty_unit_singular', 'no' );
			$units = get_option( 'alg_wc_pq_qty_price_by_qty_unit_plural', 'no' );
			$unit = ( ( !empty($unit) ) ? $unit : 'pcs' );
			$units = ( ( !empty($units) ) ? $units : 'pcs' );
			
			$product_unit = get_post_meta($product_id, '_alg_wc_pq_qty_price_by_qty_unit_label_template_singular', true);
			$product_units = get_post_meta($product_id, '_alg_wc_pq_qty_price_by_qty_unit_label_template_plural', true);
				
			if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_qty_unit_input_enabled', 'no' ) && !empty($product_unit) && !empty($product_units) ) {
				if ( !empty($unit) && !empty($units) )
				{
					$unit = ( !empty($product_unit) ? $product_unit : 'pcs' );
					$units = ( !empty($product_units) ? $product_units : 'pcs' );
				}
			} else if ( 'yes' === get_option( 'alg_wc_pq_qty_price_by_cat_qty_unit_input_enabled', 'no' ) ) {
				$get_unit = $this->get_category_unit_label( $product_id );
				if($get_unit) {
					$unit = $get_unit['singular'];
					$units = $get_unit['plural'];
					$unit = ( ( !empty($unit) ) ? $unit : 'pcs' );
					$units = ( ( !empty($units) ) ? $units : 'pcs' );
				}
			}
			
			
			
			$arrangedArray = array();
			if(!empty($selectedattribute))
			{
				$selectedattribute = json_decode(stripslashes($selectedattribute),JSON_UNESCAPED_SLASHES);
				if(count($selectedattribute) > 0 && is_array($selectedattribute))
				{
					foreach($selectedattribute as $key=>$sel)
					{
						foreach($sel as $key=>$val)
						{
							if(!empty($val))
							{
								$arrangedArray[$key] = $val;
							}
						}
					}
				}
			}
			
            if($pro_type == 'variable' && ($selectedval != '' || $selectedval != 0))
            {
            	
        	$currency_symbol = get_woocommerce_currency_symbol();
        	$selectedval = $_POST['selected_val'];
			$quantity_fetch = $_POST['quantity_fetch'];
			$product_id =  $_POST['alg_wc_pq_id'] ;
			$variation_price = $this->find_matching_product_variation_id($product_id,$arrangedArray);
			/*
			$product = new WC_Product_Variable($product_id);
			$variations = $product->get_available_variations();
			// echo '<pre>';
			//print_r($variations);
			//echo '</pre>';
			$var_data = []; 
			foreach ($variations as $variation) 
			{
				$attribute_get =  $variation['attributes'];
				//$aKeys = array_keys($attribute_get);
				//$cKeys = array_keys($arrangedArray);
				$diff=array_diff($attribute_get,$arrangedArray);
				//print_r($diff);
				foreach ($attribute_get as $attribute_get_v)
				{

					if(is_array($arrangedArray) && count($arrangedArray) > 0)
					{
						// if ($attribute_get_v == $selectedval )
						// {
							// $price_get =  $variation['price_html'];
							// echo 'price_get' . $price_get;
							$display_regular_price = $variation['display_regular_price'].'<span class="currency">'. $currency_symbol .'</span>';
							$display_price = $variation['display_price'].'<span class="currency">'. $currency_symbol .'</span>';
							
							//echo 'displayp' . $display_price;
							//echo 'qty' . $_POST['alg_wc_pq_qty'];
							$placeholders = array();
							$placeholders = array(
								'%price%'   =>$currency_symbol.''.$display_price*$quantity_fetch,
								'%qty%'     => $quantity_fetch,
							);
							echo str_replace( array_keys( $placeholders ), $placeholders,
							get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ) ) );

						// }
					}
					// exit;
				}
				// exit;
			}
			*/
			if(!empty($variation_price))
			{
				$placeholders = array(
					/*'%price%'   =>$currency_symbol.''.$variation_price*$quantity_fetch,*/
					'%price%'   =>wc_price( $variation_price*$quantity_fetch ),
					'%qty%'     => $quantity_fetch,
					'%unit%'     => ( $quantity_fetch > 1 ? $units : $unit ),
				);
				echo str_replace( array_keys( $placeholders ), $placeholders,
				get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% %unit%.', 'product-quantity-for-woocommerce' ) ) );
			}
				
            }
            else if($pro_type == 'simple')
            {

			$placeholders = array(
				'%price%'   => wc_price( wc_get_price_to_display( wc_get_product( $_POST['alg_wc_pq_id'] ), array( 'qty' => $_POST['alg_wc_pq_qty'] ) ) ),
				'%qty%'     => $_POST['alg_wc_pq_qty'],
				'%unit%'     => ( $_POST['alg_wc_pq_qty'] > 1 ? $units : $unit ),
			);
			echo str_replace( array_keys( $placeholders ), $placeholders,
				get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% %unit%.', 'product-quantity-for-woocommerce' ) ) );
		   }
		}
		die();
	}


	// function ajax_price_by_qty_variable( $param ) {

	// 	if ( isset( $_POST['alg_wc_pq_qty'] ) && '' !== $_POST['alg_wc_pq_qty'] && ! empty( $_POST['alg_wc_pq_id'] ) ) {
	// 		$selectedval = $_POST['selected_val'];
	// 		$quantity_fetch = $_POST['quantity_fetch'];
	// 		$product_id =  $_POST['alg_wc_pq_id'] ;
	// 		$currency_symbol = get_woocommerce_currency_symbol();
	// 		$product = new WC_Product_Variable($product_id);
	// 		$variations = $product->get_available_variations();
	// 		//echo '<pre>';
	// 		//print_r($variations);
	// 		//echo '</pre>';
	// 		$var_data = []; 
	// 		foreach ($variations as $variation) {
	// 			/*print_r($variation);*/
	// 			$attribute_get =  $variation['attributes'];
	// 			foreach($attribute_get as $attribute_get_v)
	// 			{
					
	// 			if($attribute_get_v == $selectedval )
	// 			{
	// 				// $price_get =  $variation['price_html'];
	// 				// echo 'price_get' . $price_get;
	// 				$display_regular_price = $variation['display_regular_price'].'<span class="currency">'. $currency_symbol .'</span>';
	// 				$display_price = $variation['display_price'].'<span class="currency">'. $currency_symbol .'</span>';
					
	// 				//echo 'displayp' . $display_price;
	// 				//echo 'qty' . $_POST['alg_wc_pq_qty'];
	// 				$placeholders = array();
	// 				$placeholders = array(
	// 			'%price%'   =>$currency_symbol.''.$display_price*$quantity_fetch,
	// 			'%qty%'     => $quantity_fetch,
	// 		);
	// 		echo str_replace( array_keys( $placeholders ), $placeholders,
	// 			get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ) ) );

	// 			}
	// 				// exit;
	// 			}
	// 			// exit;
	// 		}
	// 		// $placeholders = array(
	// 		// 	'%price%'   => wc_price( wc_get_price_to_display( wc_get_product( $_POST['alg_wc_pq_id'] ), array( 'qty' => $_POST['alg_wc_pq_qty'] ) ) ),
	// 		// 	'%qty%'     => $_POST['alg_wc_pq_qty'],
	// 		// );
	// 		// echo str_replace( array_keys( $placeholders ), $placeholders,
	// 		// 	get_option( 'alg_wc_pq_qty_price_by_qty_template', __( '%price% for %qty% pcs.', 'product-quantity-for-woocommerce' ) ) );
	// 	}
	// 	die();
	// }

	/**
	 * get_product_id.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function get_product_id( $_product ) {
		if ( ! isset( $this->is_wc_version_below_3 ) ) {
			$this->is_wc_version_below_3 = version_compare( get_option( 'woocommerce_version', null ), '3.0.0', '<' );
		}
		if ( ! $_product || ! is_object( $_product ) ) {
			return 0;
		}
		if ( $this->is_wc_version_below_3 ) {
			return ( isset( $_product->variation_id ) ) ? $_product->variation_id : $_product->get_id();
		} else {
			return $_product->get_id();
		}
	}

	/**
	 * get_product_qty_default.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function get_product_qty_default( $product_id, $default ) {
		if ( 'yes' === get_option( 'alg_wc_pq_default_section_enabled', 'no' ) ) {
			// Check if "Sold individually" is enabled for the product
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_sold_individually() ) {
				return $default;
			}
			// Per product
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_default_quantity_per_product', 'no' ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_default_quantity_per_product_value', 'no', $product_id ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_default', $value, $product_id );
				}
			}
			
			// Per category
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_cat_default_quantity_per_product', 'no' ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_cat_default_quantity_per_product_value', 'no', $product_id ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_default', $value, $product_id );
				}
			}
			
			// All products
			if ( 0 != ( $value = get_option( 'alg_wc_pq_default_per_item_quantity', 0 ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_default', $value, $product_id );
			}
			
		}
		
		
		return $default;
	}
	
	/**
	 * get_product_qty_min_max.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function get_product_qty_min_max( $product_id, $default, $min_or_max, $variation_id = 0 ) {
		if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
			// Check if "Sold individually" is enabled for the product
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_sold_individually() ) {
				return $default;
			}
			$pid_per_product = $product_id;
			if( $variation_id > 0 ) {
				$pid_per_product = $variation_id;
			}
			// Per product
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_quantity_per_product', 'no', $min_or_max ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_quantity_per_product_value', 'no', $pid_per_product, $min_or_max ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $pid_per_product );
				}
			}
			
			// Per category
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_cat_quantity_per_product', 'no', $min_or_max ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_cat_quantity_per_product_value', 'no', $product_id, $min_or_max ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
				}
			}
			
			// All products
			if ( 0 != ( $value = get_option( 'alg_wc_pq_' . $min_or_max . '_per_item_quantity', 0 ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
			}
		}
		return $default;
	}
	
		/**
	 * get_product_qty_min_max_allvar.
	 *
	 * @version 1.8.0
	 * @since   1.0.0
	 */
	function get_product_qty_min_max_allvar( $product_id, $default, $min_or_max ) {
		if ( 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_section_enabled', 'no' ) ) {
			// Check if "Sold individually" is enabled for the product
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_sold_individually() ) {
				return $default;
			}
			// Per product
			if ( 'yes' === apply_filters( 'alg_wc_pq_per_item_quantity_per_product', 'no', $min_or_max ) ) {
				if ( 0 != ( $value = apply_filters( 'alg_wc_pq_per_item_quantity_per_product_value_allvar', 'no', $product_id, $min_or_max ) ) ) {
					return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
				}
			}
			// All products
			if ( 0 != ( $value = get_option( 'alg_wc_pq_' . $min_or_max . '_per_item_quantity', 0 ) ) ) {
				return apply_filters( 'alg_wc_pq_get_product_qty_' . $min_or_max, $value, $product_id );
			}
		}
		return $default;
	}

	/**
	 * set_quantity_input_min_max_variation.
	 *
	 * @version 1.4.0
	 * @since   1.0.0
	 */
	function set_quantity_input_min_max_variation( $args, $_product, $_variation ) {
		$variation_id = $this->get_product_id( $_variation );
		$args['min_qty'] = $this->get_product_qty_min_max( $variation_id, $args['min_qty'], 'min' );
		$args['max_qty'] = $this->get_product_qty_min_max( $variation_id, $args['max_qty'], 'max' );
		$_max = $_variation->get_max_purchase_quantity();
		
		/*
		if ( -1 != $_max && $args['min_qty'] > $_max ) {
			$args['min_qty'] = $_max;
		}
		*/
		
		if ( -1 != $_max && $args['max_qty'] > $_max ) {
			$args['max_qty'] = $_max;
		}
		
		if ( $args['min_qty'] < 0 ) {
			$args['min_qty'] = '';
		}
		if ( $args['max_qty'] < 0 ) {
			$args['max_qty'] = '';
		}
		return $args;
	}

	/**
	 * set_quantity_input_min_or_max.
	 *
	 * @version 1.7.0
	 * @since   1.6.0
	 * @todo    [dev] (important) rename this (and probably some other `set_...()` functions)
	 */
	function set_quantity_input_min_or_max( $qty, $_product, $min_or_max ) {
		$value = $this->get_product_qty_min_max( $this->get_product_id( $_product ), $qty, $min_or_max );
		$_max  = $_product->get_max_purchase_quantity();
		return ( -1 == $_max || $value < $_max ? $value : $_max );
	}

	/**
	 * set_quantity_input_min.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function set_quantity_input_min( $qty, $_product ) {
		return $this->set_quantity_input_min_or_max( $qty, $_product, 'min' );
	}

	/**
	 * set_quantity_input_max.
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 */
	function set_quantity_input_max( $qty, $_product ) {
		return $this->set_quantity_input_min_or_max( $qty, $_product, 'max' );
	}

	/**
	 * block_checkout.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function block_checkout() {
		if ( ! isset( WC()->cart ) ) {
			return;
		}
		if ( ! is_checkout() ) {
			return;
		}
		// $cart_item_quantities = WC()->cart->get_cart_item_quantities();
		$cart_item_quantities = $this->get_cart_item_quantities();
		
		if ( empty( $cart_item_quantities ) || ! is_array( $cart_item_quantities ) ) {
			return;
		} 
		$cart_total_quantity = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
		// Max quantity
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			if ( ! $this->check_min_max( 'max', $cart_item_quantities, $cart_total_quantity, false, true ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		// Min quantity
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			if ( ! $this->check_min_max( 'min', $cart_item_quantities, $cart_total_quantity, false, true ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			if ( ! $this->check_step( $cart_item_quantities, $cart_total_quantity, false, true ) ) {
				wp_safe_redirect( wc_get_cart_url() );
				exit;
			}
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				if ( ! $this->check_exact_qty( $allowed_or_disallowed, $cart_item_quantities, false, true ) ) {
					wp_safe_redirect( wc_get_cart_url() );
					exit;
				}
			}
		}
	}

	/**
	 * check_order_quantities.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 * @todo    [dev] code refactoring min/max (same in `block_checkout()`)
	 */
	function check_order_quantities() {
		if ( ! isset( WC()->cart ) ) {
			return;
		}
		// $cart_item_quantities = WC()->cart->get_cart_item_quantities();
		$cart_item_quantities = $this->get_cart_item_quantities();
		if ( empty( $cart_item_quantities ) || ! is_array( $cart_item_quantities ) ) {
			return;
		}
		$cart_total_quantity = apply_filters( 'alg_wc_pq_cart_total_quantity', array_sum( $cart_item_quantities ), $cart_item_quantities );
		$_is_cart = is_cart();
		// Max quantity
		if ( 'yes' === get_option( 'alg_wc_pq_max_section_enabled', 'no' ) ) {
			$this->check_min_max( 'max', $cart_item_quantities, $cart_total_quantity, $_is_cart, false );
		}
		// Min quantity
		if ( 'yes' === get_option( 'alg_wc_pq_min_section_enabled', 'no' ) ) {
			$this->check_min_max( 'min', $cart_item_quantities, $cart_total_quantity, $_is_cart, false );
		}
		// Step
		if ( 'yes' === get_option( 'alg_wc_pq_step_section_enabled', 'no' ) ) {
			$this->check_step( $cart_item_quantities, $cart_total_quantity, $_is_cart, false );
		}
		// Exact quantities
		foreach ( array( 'allowed', 'disallowed' ) as $allowed_or_disallowed ) {
			if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
				$this->check_exact_qty( $allowed_or_disallowed, $cart_item_quantities, $_is_cart, false );
			}
		}
	}

	/**
	 * get_min_max_cart_total_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function get_min_max_cart_total_qty( $min_or_max ) {
		return get_option( 'alg_wc_pq_' . $min_or_max . '_cart_total_quantity', 0 );
	}
	
	/**
	 * get_min_max_cart_total_allvar_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function get_min_max_cart_total_allvar_qty( $min_or_max ) {
		return get_option( 'alg_wc_pq_' . $min_or_max . '_cart_total_quantity', 0 );
	}

	/**
	 * check_min_max_cart_total_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) {
		if ( 0 != ( $min_or_max_cart_total_quantity = $this->get_min_max_cart_total_qty( $min_or_max ) ) ) {
			if (
				( 'max' === $min_or_max && $cart_total_quantity > $min_or_max_cart_total_quantity ) ||
				( 'min' === $min_or_max && $cart_total_quantity < $min_or_max_cart_total_quantity )
			) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * check_min_max_cart_total_allvar_qty.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_min_max_cart_total_allvar_qty( $min_or_max, $cart_total_quantity ) {
		if ( 0 != ( $min_or_max_cart_total_quantity = $this->get_min_max_cart_total_allvar_qty( $min_or_max ) ) ) {
			if (
				( 'max' === $min_or_max && $cart_total_quantity > $min_or_max_cart_total_quantity ) ||
				( 'min' === $min_or_max && $cart_total_quantity < $min_or_max_cart_total_quantity )
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * check_product_min_max.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_product_min_max( $product_id, $min_or_max, $quantity ) {
		if( $this->disable_product_id_by_url_option( $product_id ) ) {
			return true;
		}
		if ( 0 != ( $product_min_max = $this->get_product_qty_min_max( $product_id, 0, $min_or_max ) ) ) {
			if (
				( 'max' === $min_or_max && $quantity > $product_min_max ) ||
				( 'min' === $min_or_max && $quantity < $product_min_max )
			) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * check_product_min_max_allvar.
	 *
	 * @version 1.4.0
	 * @since   1.4.0
	 */
	function check_product_min_max_allvar( $product_id, $min_or_max, $quantity ) {
		if ( 0 != ( $product_min_max = $this->get_product_qty_min_max_allvar( $product_id, 0, $min_or_max ) ) ) {
			if (
				( 'max' === $min_or_max && $quantity > $product_min_max ) ||
				( 'min' === $min_or_max && $quantity < $product_min_max )
			) {
				return false;
			}
		}
		return true;
	}

	/**
	 * check_min_max.
	 *
	 * @version 1.7.0
	 * @since   1.0.0
	 */
	function check_min_max( $min_or_max, $cart_item_quantities, $cart_total_quantity, $_is_cart, $_return ) {
		// Cart total quantity
		if ( ! $this->check_min_max_cart_total_qty( $min_or_max, $cart_total_quantity ) ) {
			if ( $_return ) {
				return false;
			} else {
				$this->messenger->print_message( $min_or_max . '_cart_total_quantity', $_is_cart, $this->get_min_max_cart_total_qty( $min_or_max ), $cart_total_quantity );
			}
		}
		
		// Per category quantity
		if ( 'yes' === get_option( 'alg_wc_pq_' .$min_or_max. '_section_enabled', 'no' ) && 'yes' === get_option( 'alg_wc_pq_' . $min_or_max . '_per_cat_item_quantity_per_product' , 'no' ) ) {
			$cartitem_by_category = $this->get_cartitem_by_category();
			if(isset($cartitem_by_category) && !empty($cartitem_by_category) && count($cartitem_by_category) > 0)
			{
				foreach($cartitem_by_category as $category_id=>$count)
				{
					$term_meta = get_option( "taxonomy_product_cat_$category_id" );
					if (!empty($term_meta) && is_array($term_meta))
					{
						$alg_wc_pq_min_or_max = 'alg_wc_pq_'.$min_or_max;
						$cat_quantity = (int) $term_meta[$alg_wc_pq_min_or_max];
						if ($cat_quantity > 0)
						{
							$trm = get_term_by( 'id', $category_id, 'product_cat' );
							if ($min_or_max=='max')
							{
								if($cat_quantity < $count)
								{
									$message_template = get_option( $alg_wc_pq_min_or_max.'_cat_message',
										__( 'Maximum allowed quantity for category '.$trm->name.' is '.$cat_quantity.'. Your current quantity for this category is '.$count.'.', 'product-quantity-for-woocommerce' ) );
									$_notice = str_replace(array('%category_title%','%max_per_item_quantity%','%item_quantity%'),array($trm->name,$cat_quantity,$count),$message_template);
									wc_add_notice( $_notice, 'error' );
									return false;
								}
							}
							if ($min_or_max=='min')
							{
								if($cat_quantity > $count)
								{
									$message_template = get_option( $alg_wc_pq_min_or_max.'_cat_message',
										__( 'Minimum allowed quantity for category '.$trm->name.' is '.$cat_quantity.'. Your current quantity is '.$count.'.', 'product-quantity-for-woocommerce' ) );
									$_notice = str_replace(array('%category_title%','%max_per_item_quantity%','%item_quantity%'),array($trm->name,$cat_quantity,$count),$message_template);
									wc_add_notice( $_notice, 'error' );
									return false;
								}
							}
						}
					}
				}
			}
		}
		
		
		// Per item quantity
		foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {

			if ( ! $this->check_product_min_max( $product_id, $min_or_max, $cart_item_quantity ) ) {
				if ( $_return ) {
					return false;
				} else {
					$this->messenger->print_message( $min_or_max . '_per_item_quantity', $_is_cart, $this->get_product_qty_min_max( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
				}
			}
		}
		
		
		// Per item quantity for all variation
		$cart_item_quantities = $this->get_cartitem_groupby_parent_id();
		if ($cart_item_quantities && count($cart_item_quantities)) {
			foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
				if ( ! $this->check_product_min_max_allvar( $product_id, $min_or_max, $cart_item_quantity ) ) {
					if ( $_return ) {
						return false;
					} else {
						$this->messenger->print_message( $min_or_max . '_per_item_quantity', $_is_cart, $this->get_product_qty_min_max_allvar( $product_id, 0, $min_or_max ), $cart_item_quantity, $product_id );
					}
				}
			}
		}

		// Passed
		if ( $_return ) {
			return true;
		}
	}

	/**
	 * get_product_exact_qty.
	 *
	 * @version 1.8.0
	 * @since   1.5.0
	 * @todo    [feature] (maybe) total qty of item in cart
	 * @todo    [feature] (maybe) total items in cart
	 */
	function get_product_exact_qty( $product_id, $allowed_or_disallowed, $default_exact_qty = '', $variation_id = 0 ) {
		$per_product_id = $product_id;
		if( $variation_id > 0 ) {
			$per_product_id = $variation_id;
		}
		if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) ) {
			if (
				'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product', 'no', $allowed_or_disallowed ) &&
				'' !== ( $exact_qty_per_product = apply_filters( 'alg_wc_pq_exact_qty_per_product_value', '', $per_product_id, $allowed_or_disallowed ) )
			) {
				return $exact_qty_per_product;
			} else if (
				'yes' === apply_filters( 'alg_wc_pq_exact_qty_per_product_cat', 'no', $allowed_or_disallowed ) &&
				'' !== ( $exact_qty_per_product = apply_filters( 'alg_wc_pq_exact_qty_per_product_cat_value', '', $product_id, $allowed_or_disallowed ) )
			) {
				return $exact_qty_per_product;
			} else {
				return ( '' !== ( $exact_qty_all_products = get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed, '' ) ) ? $exact_qty_all_products : $default_exact_qty );
			}
		} else {
			return $default_exact_qty;
		}
	}

	/**
	 * process_exact_qty_option.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] (important) qty range in `print_message()`
	 * @todo    [dev] qty range: power of X (i.e. instead of adding range step)
	 */
	function process_exact_qty_option( $qty_option ) {
		$_qty = array_map( 'trim', explode( ',', $qty_option ) );
		$qty  = array();
		foreach ( $_qty as $value ) {
			if ( false !== strpos( $value, '[' ) ) {
				if ( 0 === strpos( $value, '[' ) && ( ( strlen( $value ) - 1 ) == strpos( $value, ']' ) ) ) {
					$value = substr( $value, 1, ( strlen( $value ) - 2 ) );
					$value = array_map( 'trim', explode( '|', $value ) );
					if ( 2 === count( $value ) ) {
						$range = explode( '-', $value[0] );
						if ( 2 === count( $range ) ) {
							for ( $i = $range[0]; $i <= $range[1]; $i += $value[1] ) {
								$qty[] = $i;
							}
						} // else skipping the value (wrong format)
					} // else skipping the value (wrong format)
				} // else skipping the value (wrong format)
			} else {
				$qty[] = $value;
			}
		}
		return $qty;
	}

	/**
	 * check_product_exact_qty.
	 *
	 * @version 1.7.0
	 * @since   1.5.0
	 * @todo    [dev] (important) rethink qty correction on `disallowed`
	 * @todo    [dev] (important) check if all `$product_exact_qty` elements are `is_numeric()`
	 * @todo    [dev] (important) check if possible float and int comparison works properly in `abs( $quantity - $closest ) > abs( $item - $quantity )`
	 */
	function check_product_exact_qty( $product_id, $allowed_or_disallowed, $quantity, $cart_item_quantity, $do_fix = false, $variation_id = 0 ) {
		if( $this->disable_product_id_by_url_option( $product_id ) ) {
			return true;
		}
		$product_exact_qty = $this->get_product_exact_qty( $product_id, $allowed_or_disallowed, '', $variation_id );

		if ( '' != $product_exact_qty ) {
			$product_exact_qty = $this->process_exact_qty_option( $product_exact_qty );
			sort( $product_exact_qty );
			$is_valid = ( 'allowed' === $allowed_or_disallowed ? in_array( $cart_item_quantity, $product_exact_qty ) : ! in_array( $cart_item_quantity, $product_exact_qty ) );
			if ( ! $do_fix ) {
				return $is_valid;
			} elseif ( ! $is_valid ) {
				if ( 'allowed' === $allowed_or_disallowed ) {
					$closest = null;
					foreach ( $product_exact_qty as $item ) {
						if ( $closest === null || abs( $cart_item_quantity - $closest ) > abs( $item - $cart_item_quantity ) ) {
							$closest = $item;
						}
					}
					return ( null !== $closest ? ( $closest - ( $cart_item_quantity - $quantity ) ) : $quantity );
				} else { // 'disallowed'
					$_cart_item_quantity = $cart_item_quantity;
					while ( true ) {
						$_cart_item_quantity++;
						if ( ! in_array( $_cart_item_quantity, $product_exact_qty ) ) {
							return ( $_cart_item_quantity - ( $cart_item_quantity - $quantity ) );
						}
					}
				}
			}
		}
		return ( ! $do_fix ? true : $quantity );
	}

	/**
	 * check_exact_qty.
	 *
	 * @version 1.7.0
	 * @since   1.5.0
	 */
	function check_exact_qty( $allowed_or_disallowed, $cart_item_quantities, $_is_cart, $_return ) {
		// Per category quantity
		if ( 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_section_enabled', 'no' ) && 'yes' === get_option( 'alg_wc_pq_exact_qty_' . $allowed_or_disallowed . '_per_cat_item_quantity_per_product' , 'no' ) ) {
			$cartitem_by_category = $this->get_cartitem_by_category();
			
			if(isset($cartitem_by_category) && !empty($cartitem_by_category) && count($cartitem_by_category) > 0)
			{
				foreach($cartitem_by_category as $category_id=>$count)
				{
					$term_meta = get_option( "taxonomy_product_cat_$category_id" );
					if (!empty($term_meta) && is_array($term_meta))
					{
						$alg_wc_pq_allowed_or_disallowed = 'alg_wc_pq_exact_qty_'.$allowed_or_disallowed;
						$cat_quantity = $term_meta[$alg_wc_pq_allowed_or_disallowed];
						if ( '' != $cat_quantity ) 
						{
							
							$cat_quantity = $this->process_exact_qty_option( $cat_quantity );
							sort( $cat_quantity );
							$is_valid = ( 'allowed' === $allowed_or_disallowed ? in_array( $count, $cat_quantity ) : ! in_array( $count, $cat_quantity ) );
							if(!$is_valid)
							{
								$trm = get_term_by( 'id', $category_id, 'product_cat' );
								if ($allowed_or_disallowed=='allowed')
								{
									$message_template = get_option( $alg_wc_pq_allowed_or_disallowed . '_cat_message',
										__( 'Allowed quantity for category ' . $trm->name . ' is ' . implode(',',$cat_quantity) . '. Your current quantity is ' . $count , 'product-quantity-for-woocommerce' ) );
									$_notice = str_replace(array('%category_title%','%allowed_quantity%','%quantity%'),array($trm->name,implode(',',$cat_quantity),$count),$message_template);
									wc_add_notice( $_notice, 'error' );
									return false;
								}
							}
						}
					}
				}
			}
		}

		foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
			
			if ( ! $this->check_product_exact_qty( $product_id, $allowed_or_disallowed, $cart_item_quantity, $cart_item_quantity ) ) {
				if ( $_return ) {
					return false;
				} else {
					$this->messenger->print_message( 'exact_qty_' . $allowed_or_disallowed, $_is_cart, $this->get_product_exact_qty( $product_id, $allowed_or_disallowed ), $cart_item_quantity, $product_id );
				}
			}
		}
		// Passed
		if ( $_return ) {
			return true;
		}
	}

	/**
	 * check_product_step.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] `$multiplier` should be calculated automatically according to the `$qty_step_settings` value (same in `force_js_check_step()`)
	 */
	function check_product_step( $product_id, $quantity, $do_fix = false ) {
		$product_qty_step = $this->get_product_qty_step( $product_id );
		if ( 0 != $product_qty_step ) {
			$min_value = $this->get_product_qty_min_max( $product_id, 0, 'min' );
			if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
				$multiplier         = floatval( 1000000 );
				$_min_value         = intval( round( floatval( $min_value )        * $multiplier ) );
				$_quantity          = intval( round( floatval( $quantity )         * $multiplier ) );
				$_product_qty_step  = intval( round( floatval( $product_qty_step ) * $multiplier ) );
			} else {
				$_min_value         = $min_value;
				$_quantity          = $quantity;
				$_product_qty_step  = $product_qty_step;
			}
			$_quantity = $_quantity - $_min_value;
			$_reminder = $_quantity % $_product_qty_step;
			$is_valid  = ( 0 == $_reminder );
			if ( ! $do_fix ) {
				return $is_valid;
			} elseif ( ! $is_valid ) {
				$step_auto_correct = get_option( 'alg_wc_pq_add_to_cart_validation_step_auto_correct', 'round' );
				$extra_qty = ( 'round_down' != $step_auto_correct && ( 'round_up' == $step_auto_correct || $_reminder * 2 >= $_product_qty_step ) ?
					$_product_qty_step : 0 );
				$quantity = $_quantity + $extra_qty - $_reminder + $_min_value;
				if ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) ) {
					$quantity = $quantity / $multiplier;
				}
				return $quantity;
			}
		}
		return ( ! $do_fix ? true : $quantity );
	}

	/**
	 * get_step_cart_total_qty.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 */
	function get_step_cart_total_qty() {
		return get_option( 'alg_wc_pq_step_cart_total_quantity', 0 );
	}

	/**
	 * check_step_cart_total_qty.
	 *
	 * @version 1.7.0
	 * @since   1.7.0
	 * @todo    [dev] (important) (maybe) code refactoring (merge with `check_product_step()`)
	 */
	function check_step_cart_total_qty( $cart_total_quantity, $do_fix = false, $product_qty = 0 ) {
		if ( 0 != ( $step_cart_total_quantity = $this->get_step_cart_total_qty() ) ) {
			$is_decimal = ( 'yes' === get_option( 'alg_wc_pq_decimal_quantities_enabled', 'no' ) );
			if ( $is_decimal ) {
				$multiplier                 = floatval( 1000000 );
				$_cart_total_quantity       = intval( round( floatval( $cart_total_quantity )      * $multiplier ) );
				$_step_cart_total_quantity  = intval( round( floatval( $step_cart_total_quantity ) * $multiplier ) );
			} else {
				$_cart_total_quantity       = $cart_total_quantity;
				$_step_cart_total_quantity  = $step_cart_total_quantity;
			}
			$_reminder = $_cart_total_quantity % $_step_cart_total_quantity;
			$is_valid  = ( 0 == $_reminder );
			if ( ! $do_fix ) {
				return $is_valid;
			} elseif ( ! $is_valid ) {
				if ( $is_decimal ) {
					$product_qty = intval( round( floatval( $product_qty ) * $multiplier ) );
				}
				$step_auto_correct = get_option( 'alg_wc_pq_add_to_cart_validation_step_auto_correct', 'round' );
				$extra_qty = ( 'round_down' != $step_auto_correct && ( 'round_up' == $step_auto_correct || $_reminder * 2 >= $_step_cart_total_quantity ) ?
					$_step_cart_total_quantity : 0 );
				$product_qty = $product_qty + $extra_qty - $_reminder;
				if ( $is_decimal ) {
					$product_qty = $product_qty / $multiplier;
				}
				return $product_qty;
			}
		}
		return ( ! $do_fix ? true : $product_qty );
	}

	/**
	 * check_step.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] (maybe) force `min` in cart to `1` (as it may be zero now)
	 */
	function check_step( $cart_item_quantities, $cart_total_quantity, $_is_cart, $_return ) {
		// Cart total quantity
		if ( ! $this->check_step_cart_total_qty( $cart_total_quantity ) ) {
			if ( $_return ) {
				return false;
			} else {
				$this->messenger->print_message( 'step_cart_total_quantity', $_is_cart, $this->get_step_cart_total_qty(), $cart_total_quantity );
			}
		}
		// Per item step
		foreach ( $cart_item_quantities as $product_id => $cart_item_quantity ) {
			if ( ! $this->check_product_step( $product_id, $cart_item_quantity ) ) {
				if ( $_return ) {
					return false;
				} else {
					$this->messenger->print_message( 'step_quantity', $_is_cart, $this->get_product_qty_step( $product_id ), $cart_item_quantity, $product_id );
				}
			}
		}
		// Passed
		if ( $_return ) {
			return true;
		}
	}
	
	/**
	 * check_step.
	 *
	 * @version 1.7.0
	 * @since   1.4.0
	 * @todo    [dev] (maybe) force `min` in cart to `1` (as it may be zero now)
	 */
	function get_quantity_with_sep( $qty ){
		/* ('yes' === apply_filters( 'alg_wc_pq_qty_dropdown_thousand_separator', 'no') ) */
		
		if ('yes' === get_option( 'alg_wc_pq_qty_dropdown_thousand_separator_enabled', 'no' ) ) {
			$sep = get_option( 'alg_wc_pq_qty_dropdown_thousand_separator', ',' );
			if( !empty($sep) && !empty($qty) )
			{
				return number_format($qty, 0, '', $sep);
			}
		}
		return $qty;
	}

}

endif;

return new Alg_WC_PQ_Core();
