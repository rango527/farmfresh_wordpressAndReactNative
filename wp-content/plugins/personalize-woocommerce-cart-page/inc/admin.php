<?php 
/*
** Woo Hero Admin side Functionality
*/

/* == Direct access not allowed ==*/
if( ! defined('ABSPATH' ) ){ exit; }



function wooh_wpml_translate($field_value, $domain='wooh') {
		
	$field_name = $domain . ' - ' . sanitize_key($field_value);
	
	
	//WMPL
    /**
     * register strings for translation
     * source: https://wpml.org/wpml-hook/wpml_translate_single_string/
     */
    
    $field_value = stripslashes($field_value);
    
	return apply_filters('wpml_translate_single_string', $field_value, $domain, $field_name );

}

/* == render field in product tabs ==*/
function render_the_row($contents, $label_counter){
   
    echo '<div class="meta-row first-element">
        <table cellspacing="20">
          <tr>
            <td class="clone-me"><img src="'.WOOH_URL.'/images/plus.png" alt="Add"></td>
            <td class="remove-me"><img src=" '.WOOH_URL.'/images/minus.png" alt="Remove"></td>
            <td>
            <label style="font-weight:bold !important" for="default_tab">Tab Title</label></br>
              <input name="tab_title" type="text" placeholder="Tab Name Here..." value=" '.$contents['title'].'">
            </td>
            <td>
              <label style="font-weight:bold !important" for="default_desc">Default Description</label>
              <textarea name="default_desc" id="default_desc" rows="2">'.$contents['default_desc'].'</textarea>
            </td>
            </td>
          </tr>
        </table>
      </div>';
}

/* == render field in product tabs default ==*/
function render_the_default(){

	echo '<div class="meta-row first-element">
	    <table cellspacing="20">
	      <tr>
	        <td class="clone-me"><img src="'.WOOH_URL.'/images/plus.png" alt="Add"></td>
	        <td class="remove-me"><img src="'.WOOH_URL.'/images/minus.png" alt="Remove"></td>
	        <td>
	          <input name="tab_title" type="text" placeholder="Tab Name Here..." value="">
	        </td>
	        <td>
	          <label for="default_desc">Default Description</label>
	          <textarea name="default_desc" id="default_desc" rows="2"></textarea>
	        </td>
	        </td>
	      </tr>
	    </table>
	  </div>';
}

/* == Wooh settings ==*/
function wooh_admin_settings_array(){

	$setting = array('label-page' => array( 'name'		=> __('Button Labels', 'wooh'),
											'type'	=> 'tab',
											'desc'	=> __('Here you can personalized all 
																buttons labels. <a href="http://
																www.najeebmedia.com/personalized-woostore-guide/#buttons"
																target="_blank">How it works!</a>'
																, 'woohero'),
											'meat'	=> array('add-to-cart-text-simple' => array(
													'label' => __('Add to cart', 'woohero'),
					 								'desc'	=> __('It will replace button label
					 								               for products in <b>Shop</b>', 'woohero'),
					 								'id'			=> 'wooh_'.'addtocartsimple',
						 							'type'			=> 'text',
						 							'default'		=> '',
						 							'help'			=> __('', 'wooh')
					 							),

											'add-to-cart-text-single'	=> array(	'label'		=> __('Single product add to cart', 'wooh'),
						 							'desc'		=> __('It will replace default add to cart button label on <b>Single Product Page</b>', 'wooh'),
						 							'id'			=> 'wooh_'.'addtocartsingle',
						 							'type'			=> 'text',
						 							'default'		=> '',
						 							'help'			=> __('', 'wooh')
						 							),

										'add-to-cart-text-grouped'	=> array(	'label'		=> __('View products', 'wooh'),
					 							'desc'		=> __('It will replace default button label for <b>Grouped Products in Shop</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'addtocartgrouped',
					 							'type'			=> 'text',
					 							'default'		=> '',
					 							'help'			=> __('', 'wooh')
					 							),

										'add-to-cart-text-variable'	=> array(	'label'		=> __('Select options', 'wooh'),
					 							'desc'		=> __('It will replace default button label for <b>Variable Products in Shop</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'addtocartvariable',
					 							'type'			=> 'text',
					 							'default'		=> '',
					 							'help'			=> __('', 'wooh')
					 							),

										'add-to-cart-text-external'	=> array(	'label'		=> __('Buy product', 'wooh'),
					 							'desc'		=> __('It will replace default button label for <b>Eternal Products in Shop</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'addtocartexternal',
					 							'type'			=> 'text',
					 							'default'		=> '',
					 							'help'			=> __('', 'wooh')
					 							),

										'add-to-cart-text-out'	=> array(	'label'		=> __('Single product out of stock', 'wooh'),
					 							'desc'		=> __('It will replace default out of stock text on <b>Single Product Page</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'addtocartout',
					 							'type'			=> 'text',
					 							'default'		=> '',
					 							'help'			=> __('', 'wooh')
					 							),

										'order-button-text'	=> array(	'label'		=> __('Order button text', 'wooh'),
					 							'desc'		=> __('It will replace default order button label on <b>Checkout Page</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'orderbuttontext',
					 							'type'			=> 'text',
					 							'default'		=> '',
					 							'help'			=> __('', 'wooh')
					 							),
					 					),
									),

							
																				
				'cart-page'	=> array(	'name'	=> __('Cart Page', 'wooh'),
														'type'	=> 'tab',
														'desc'	=> __('Here you can personalized cart page areas. <a href="http://www.najeebmedia.com/personalized-woostore-guide/#cart" target="_blank">How it works!</a>', 'wooh'),
														'meat'	=> array(
					'before-cart-table'	=> array(	'label'		=> __('Before cart table', 'wooh'),
					 							'desc'		=> __('It will show text <b>Before cart TABLE</b> on Cart Page', 'wooh'),
					 							'id'			=> 'wooh_'.'beforecarttable',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'before-cart-contents'	=> array(	'label'		=> __('Before cart contents', 'wooh'),
					 							'desc'		=> __('It will show text <b>Before cart CONTENTS</b> on Cart Page', 'wooh'),
					 							'id'			=> 'wooh_'.'beforecartcontents',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('Write between &lt;tr&gt;&lt;td&gt; &lt;/td&gt;&lt;/tr&gt;', 'wooh')
					 							),

					'cart-contents'	=> array(	'label'		=> __('After products', 'wooh'),
					 							'desc'		=> __('It will show text <b>After cart ITEMS</b> on Cart Page', 'wooh'),
					 							'id'			=> 'wooh_'.'cartcontents',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('Write between &lt;tr&gt;&lt;td&gt; &lt;/td&gt;&lt;/tr&gt;', 'wooh')
					 							),
					
					'after-cart-contents'	=> array(	'label'		=> __('After cart contents', 'wooh'),
					 							'desc'		=> __('It will show text below PROCEED TO CHECKOUT button on cart page', 'wooh'),
					 							'id'			=> 'wooh_'.'aftercartcontents',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('Write between &lt;tr&gt;&lt;td&gt; &lt;/td&gt;&lt;/tr&gt;', 'wooh')
					 							),

					'after-cart-table'	=> array(	'label'		=> __('After cart table', 'wooh'),
					 							'desc'		=> __('It will show text <b>After cart TABLE</b> on Cart Page', 'wooh'),
					 							'id'			=> 'wooh_'.'aftercarttable',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'after-cart'	=> array(	'label'		=> __('At bottom of page', 'wooh'),
					 							'desc'		=> __('It will show text at the <b>Bottom</b> on Cart Page', 'wooh'),
					 							'id'			=> 'wooh_'.'aftercart',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'proceed-to-checkout'	=> array(	'label'		=> __('After proceed to checkout button', 'wooh'),
					 							'desc'		=> __('It will show text <b>After PROCEED TO CHECKOUT</b> button on Cart Page', 'wooh'),
					 							'id'			=> 'wooh_'.'proceedtocheckout',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'cart-coupon'	=> array(	'label'		=> __('After coupon button', 'wooh'),
					 							'desc'		=> __('It will show text <b>After COUPON button on Cart Page</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'cartcoupon',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'before-cart-totals'	=> array(	'label'		=> __('Before cart totals', 'wooh'),
					 							'desc'		=> __('It will show text <b>Before CART TOTALS</b> on Cart Page', 'wooh'),
					 							'id'			=> 'wooh_'.'beforecarttotals',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'after-cart-totals'	=> array(	'label'		=> __('After cart totals', 'wooh'),
					 							'desc'		=> __('It will show text <b>After CART TOTALS</b> on cart page', 'wooh'),
					 							'id'			=> 'wooh_'.'aftercarttotals',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'cart-is-empty'	=> array(	'label'		=> __('When cart empty', 'wooh'),
					 							'desc'		=> __('It will show text when <b>Cart is Empty</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'cartisempty',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'before-mini-cart'	=> array(	'label'		=> __('Before mini cart', 'wooh'),
					 							'desc'		=> __('It will show text <b>Before MINI CART</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'beforeminicart',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'widget-shopping-cart-before-buttons'	=> array(	'label'		=> __('Widget cart before buttons', 'wooh'),
					 							'desc'		=> __('It will show text before button of <b>Widget Shopping</b> cart', 'wooh'),
					 							'id'			=> 'wooh_'.'widgetshoppingcartbeforebuttons',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'after-mini-cart'	=> array(	'label'		=> __('After mini cart', 'wooh'),
					 							'desc'		=> __('It will show text <b>After Mini Cart</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'afterminicart',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),

					'cart-totals-before-shipping'	=> array(	'label'		=> __('Cart totals before shipping', 'wooh'),
					 							'desc'		=> __('It will show text <b>Before Shipping</b> cart totals', 'wooh'),
					 							'id'			=> 'wooh_'.'carttotalsbeforeshipping',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('Write between &lt;tr&gt;&lt;td&gt; &lt;/td&gt;&lt;/tr&gt;', 'wooh')
					 							),

					'cart-totals-after-shipping'	=> array(	'label'		=> __('Cart totals after shipping', 'wooh'),
					 							'desc'		=> __('It will show text <b>After Shipping</b> cart totals', 'wooh'),
					 							'id'			=> 'wooh_'.'carttotalsaftershipping',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('Write between &lt;tr&gt;&lt;td&gt; &lt;/td&gt;&lt;/tr&gt;', 'wooh')
					 							),

					'cart-totals-before-order-total'	=> array(	'label'		=> __('Before order total', 'wooh'),
					 							'desc'		=> __('It will show text <b>Before Order Totals</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'carttotalsbeforeordertotal',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('Write between &lt;tr&gt;&lt;td&gt; &lt;/td&gt;&lt;/tr&gt;', 'wooh')
					 							),
					
					'cart-totals-after-order-total'	=> array(	'label'		=> __('After order total', 'wooh'),
					 							'desc'		=> __('It will show text <b>After Order Totals</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'carttotalsafterordertotal',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('Write between &lt;tr&gt;&lt;td&gt; &lt;/td&gt;&lt;/tr&gt;', 'wooh')
					 							),
					
					'before-shipping-calculator'	=> array(	'label'		=> __('Before shipping calculator', 'wooh'),
					 							'desc'		=> __('It will show text <b>Before Shipping Calculator</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'beforeshippingcalculator',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),
					
					'after-shipping-calculator'	=> array(	'label'		=> __('After shipping calculator', 'wooh'),
					 							'desc'		=> __('It will show text <b>After Shipping Calculator</b>', 'wooh'),
					 							'id'			=> 'wooh_'.'aftershippingcalculator',
					 							'type'			=> 'textarea',
					 							'default'		=> '',
					 							'help'			=> __('You can use HTML tags', 'wooh')
					 							),
					
				),
			),

			'get-pro'		=> array(	'name'		=> __('Pro Features', 'wooh'),
															'type'	=> 'tab',
															'desc'	=> __('Get PRO version and enjoy following features', 'wooh'),
															'meat'	=> array('file-meta'	=> array(	
									'desc'		=> '',
									'type'		=> 'file',
									'id'		=> 'get-pro.php',
									),
								),
														),
			'get-ppom'		=> array(	'name'		=> __('Product Addon', 'wooh'),
															'type'	=> 'tab',
															'desc'	=> __('WooCommerce Product Addon', 'wooh'),
															'meat'	=> array('file-meta'	=> array(	
									'desc'		=> '',
									'type'		=> 'file',
									'id'		=> 'get-ppom.php',
									),
								),
														
														),
	

		);


			return apply_filters( 'wooh_options_settings', $setting);



}