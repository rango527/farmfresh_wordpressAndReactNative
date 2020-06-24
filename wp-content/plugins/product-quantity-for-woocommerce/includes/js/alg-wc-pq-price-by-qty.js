/**
 * alg-wc-pq-price-by-qty.js
 *
 * @version 1.7.3
 * @since   1.6.1
 */
 
function alg_wc_pq_update_price_by_qty( e, qty = null, attribute = null ) {
var selected_val = 0;
if(jQuery(this).hasClass("qty"))
{
	selected_val = jQuery('.variations select option:selected').val();
}
else
{
	if(e.type == 'change')
	{
		selected_val = jQuery(this).val();
	}
	else
	{
		selected_val = jQuery('.variations select option:selected').val();
	}
}

var quantity_fetch = jQuery('.qty').val();
//var attribute_fetch = jQuery(this).data('attribute_name');
var attribute_fetch = get_attributes_alg_wc_pq();
	var data = {
		'action'        : 'alg_wc_pq_update_price_by_qty',
		'alg_wc_pq_qty' : ( null !== qty ? qty : quantity_fetch ),
		'alg_wc_pq_id'  : alg_wc_pq_update_price_by_qty_object.product_id,
		'selected_val' : selected_val,
		'quantity_fetch' : quantity_fetch,
		'attribute' : ( null !== attribute ? attribute : attribute_fetch ),
	};
	
	jQuery.ajax( {
		type   : 'POST',
		url    : alg_wc_pq_update_price_by_qty_object.ajax_url,
		data   : data,
		success: function( response ) {
			if ( 'instead' == alg_wc_pq_update_price_by_qty_object.position ) {
				jQuery( 'p.price' ).html( response );
			} else {
				jQuery( 'p.alg-wc-pq-price-display-by-qty' ).html( response );
			}
		},
	} );
}
/*function alg_wc_pq_update_price_by_qty_variable( e, qty = null ) {
	var selected_val = jQuery(this).val();
	var quantity_fetch = jQuery('.qty').val();
	var data = {
		'action'        : 'alg_wc_pq_update_price_by_qty',
		'alg_wc_pq_qty' : ( null !== qty ? qty : jQuery( this ).val() ),
		'alg_wc_pq_id'  : alg_wc_pq_update_price_by_qty_object.product_id,
		'selected_val' : selected_val,
		'quantity_fetch' : quantity_fetch,

	};
	
	jQuery.ajax( {
		type   : 'POST',
		url    : alg_wc_pq_update_price_by_qty_object.ajax_url,
		data   : data,

		success: function( response ) {
			if ( 'instead' == alg_wc_pq_update_price_by_qty_object.position ) {
				jQuery( 'p.price' ).html( response );
			} else {
				jQuery( 'p.alg-wc-pq-price-display-by-qty' ).html( response );
			}
		},
	} );
}*/

jQuery( document ).ready( function() {
	if ( 'instead' != alg_wc_pq_update_price_by_qty_object.position ) {
		var price_display_by_qty_element = '<p class="alg-wc-pq-price-display-by-qty"></p>';
		switch ( alg_wc_pq_update_price_by_qty_object.position ) {
			case 'before':
				jQuery( 'p.price' ).before( price_display_by_qty_element );
				break;
			case 'after':
				jQuery( 'p.price' ).after( price_display_by_qty_element );
				break;
			case 'before_add_to_cart':
				jQuery( 'button.single_add_to_cart_button' ).closest('form').before( price_display_by_qty_element );
				break;
			case 'after_add_to_cart':
				jQuery( 'button.single_add_to_cart_button' ).closest('form').after( price_display_by_qty_element );
				break;
		}
	}
	var $attribute = '';
	var attribute = jQuery('select').data('attribute_name');
	if ( 'undefined' !== typeof attribute ) {
		$attribute = get_attributes_alg_wc_pq();
	}
	var $qty_val = jQuery( '[name="quantity"]' ).val();
	if ( 'undefined' !== typeof $qty_val ) {
		alg_wc_pq_update_price_by_qty( false, $qty_val, $attribute );
	}
	jQuery( '[name="quantity"]' ).on( 'change', alg_wc_pq_update_price_by_qty );
	jQuery( '.variations select' ).on( 'change', alg_wc_pq_update_price_by_qty );
} ); 

function get_attributes_alg_wc_pq()
{
	var jsonObj = [];
	jQuery( 'select' ).each(function() {
		var attribute_name = jQuery( this ).data('attribute_name');
		var attribute_value = jQuery( this ).val();
		if ( 'undefined' !== typeof attribute_name &&  'undefined' !== typeof attribute_value ) {
		item = {}
        item [attribute_name] = attribute_value;
        jsonObj.push(item);
		}
	});
	return JSON.stringify(jsonObj);
}