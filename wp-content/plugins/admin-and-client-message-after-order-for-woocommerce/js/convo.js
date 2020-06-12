/*
 * NOTE: all actions are prefixed by plugin shortnam_action_name
 */

jQuery(function($) {

	$(document).ready(function(e) {

		$(".wooconvo_refresh_loader").hide();
		$('#wooconvo-hole-code').show();



		jQuery('.woo-cus-box-hide').hide();
		jQuery('.cus-upload').hide();
		jQuery('.woo-header').hide();
		jQuery('.woo-cancel-revise-btn').hide();

		$("div.vorder-info-top-left").css({ 'width': '100%' });
		$("div.vorder-info-top-left").children().css({ 'width': '100%' });

		$("ul.wcmp-order-vendor").find('li').css({ 'display': 'inline-block' });


	});

	// stylings
	$('#wooconvo-send').find('textarea').css({
		'width': '100%'
	});


	//showing/hiding convos
	$(".nm-wooconvo-subject").click(function() {

		var _convo_item = $(this).parent();
		_convo_item.find(".nm-wooconvo-message, .nm-wooconvo-files").slideToggle(500);
	});

	//expand all message
	$("a.nm-wooconvo-expand-all").click(function() {

		if (wooconvo_vars.collapse_all === $(this).html()) {
			$(this).html(wooconvo_vars.expand_all);
			$(".nm-wooconvo-message, .nm-wooconvo-files").slideUp(500);
		}
		else {
			$(this).html(wooconvo_vars.collapse_all);
			$(".nm-wooconvo-message, .nm-wooconvo-files").slideDown(500);
		}


	});

	jQuery('.woo-revise-btn').on('click', function(e) {
		e.preventDefault();

		jQuery('.woo-cus-box-hide').show();
		jQuery('.woo-header').show();
		jQuery('.cus-upload').show();
		jQuery('.woo-cancel-revise-btn').show();
		jQuery('.wooconvo-cus-btn').hide();
	});

	jQuery('.woo-cancel-revise-btn').on('click', function(e) {
		e.preventDefault();

		jQuery('.woo-cus-box-hide').hide();
		jQuery('.cus-upload').hide();
		jQuery('.woo-cancel-revise-btn').hide();
		jQuery('.woo-header').hide();
		jQuery('.wooconvo-cus-btn').show();
	});


});

function send_order_message_onfrontend() {

	var message = '';
	var resp_msg = '';
	var _wrapper = jQuery("#wooconvo-send");

	jQuery('#sending-order-message').hide();

	jQuery.blockUI({
		message: 'Please Wait',
		css: {
			border: 'none',
			padding: '15px',
			backgroundColor: '#000',
			'-webkit-border-radius': '10px',
			'-moz-border-radius': '10px',
			opacity: .5,
			color: '#fff'
		}

	});

	// jQuery('.woo-approve-btn').val('Please Wait...');

	if (jQuery('.wooconvo-cus-btn').length) {

		if (jQuery('.woo-approve-btn').data('val') == 'Approve') {
			message = wooconvo_vars.app_text;
			resp_msg = wooconvo_vars.app_alert_text;
		}
	}

	show_working('sending-order-message', false);

	_wrapper.find('.wooconvo-textarea').css({ 'border': '' });

	var files_attached = Array();
	jQuery('input[name^="thefile_wooconvo_file"]').each(function(i, item) {
		files_attached.push(jQuery(item).val());
	});

	if (wooconvo_vars.file_field_required == 'yes' && files_attached == '') {
		alert('File field required');
	}

	if (message != '' || files_attached != '') {

		var data = {
			is_admin: _wrapper.find('input[name="is_admin"]').val(),
			message: message,
			existing_convo_id: jQuery('input[name="existing_convo_id"]').val(),
			order_id: _wrapper.find('input[name="order_id"]').val(),
			wooconvo_nonce: jQuery('input[name="wooconvo_nonce"]').val(),
			files: files_attached,
			action: 'wooconvo_send_message'
		};


		jQuery.post(wooconvo_vars.ajaxurl, data, function(resp) {
			if (resp.status == 'error') {
				jQuery('#sending-order-message').html(resp.message);
			}
			else {
				jQuery('#sending-order-message').html(resp.message);

				jQuery(".wooconvo-first-message").remove();

				var last_msg = resp.last_message;
				jQuery('ol.chat').append(resp.last_message);
				_wrapper.find('.wooconvo-textarea').val('');

				jQuery.unblockUI();

				if (resp_msg != '') {
					alert(resp_msg);
				}
			}

			jQuery('#filelist-wooconvo_file').empty();

		});

	}
	else {

		_wrapper.find('.wooconvo-textarea').effect('shake');
		// _wrapper.find('.wooconvo-textarea').css({'border':'1px solid red'});
		show_working('sending-order-message', true);
	}

	return false;
}

function send_order_message() {



	var _wrapper = jQuery("#wooconvo-send");
	var message = _wrapper.find('.wooconvo-textarea').val();

	show_working('sending-order-message', false);
	_wrapper.find('.wooconvo-textarea').css({ 'border': '' });

	var files_attached = Array();
	jQuery('input[name^="thefile_wooconvo_file"]').each(function(i, item) {
		files_attached.push(jQuery(item).val());
	});


	if (wooconvo_vars.file_field_required == 'yes' && files_attached == '') {
		alert('please upload the file');
	}
	else {
		if (message != '' || files_attached != '') {

			var data = {
				is_admin: _wrapper.find('input[name="is_admin"]').val(),
				message: message,
				existing_convo_id: jQuery('input[name="existing_convo_id"]').val(),
				order_id: _wrapper.find('input[name="order_id"]').val(),
				wooconvo_nonce: jQuery('input[name="wooconvo_nonce"]').val(),
				files: files_attached,
				action: 'wooconvo_send_message'
			};


			jQuery.post(wooconvo_vars.ajaxurl, data, function(resp) {
				if (resp.status == 'error') {
					jQuery('#sending-order-message').html(resp.message);
				}
				else {
					jQuery('#sending-order-message').html(resp.message);

					jQuery(".wooconvo-first-message").remove();

					var last_msg = resp.last_message;
					jQuery('ol.chat').append(resp.last_message);
					_wrapper.find('.wooconvo-textarea').val('');

				}

				jQuery('#filelist-wooconvo_file').empty();

			});

		}
		else {

			_wrapper.find('.wooconvo-textarea').effect('shake');
			// _wrapper.find('.wooconvo-textarea').css({'border':'1px solid red'});
			show_working('sending-order-message', true);
		}
	}
	return false;

}

function get_option(key) {

	/*
	 * TODO: change plugin shortname
	 */
	var keyprefix = 'wooconvo';

	key = keyprefix + key;

	var req_option = '';

	jQuery.each(wooconvo_vars.settings, function(k, option) {

		// console.log(k);

		if (k == key)
			req_option = option;
	});

	// console.log(req_option);
	return req_option;

}

// a function showing working gif
function show_working(element, off) {

	var _html = '';
	if (off == false) {
		var _html = '<img src="' + wooconvo_vars.plugin_url +
			'/images/loading.gif">';
	}

	jQuery('#' + element).html(_html);
}

jQuery(document).ready(function($) {
	//add modal to all conversations
	// $(".modal-convo").iziModal({
	// 	width: 900,
	// 	padding: 30,
	// 	top: 60,
	// 	zindex: 10000,
	// 	closeButton: true,
	// });
	// console.log($(".modal-convo"));
	$('a[class*="convo"]').each(function(index, val) {
		var modal_id = $(this).attr('class').split(' ').pop();
		// get order id by spliting the convo-904 ( convo-order_id ) class
		var order_id = modal_id.split('convo-');

		// console.log( order_id[1] );
		modal_id = '#modal-' + modal_id;
		var data = {
			'action': 'get_wooconvo',
			'order_id': order_id[1]
		}
		var target = modal_id + " .iziModal-content";
		$(modal_id).iziModal({
			width: 900,
			padding: 30,
			top: 60,
			zindex: 10000,
			closeButton: true,
			onOpening: function(modal) {

				modal.startLoading();

				$.post(wooconvo_vars.ajaxurl, data, function(data) {
					$(target).html(data);

					modal.stopLoading();
				});
				$('.modal-convo').append('<button data-izimodal-close="modal-convo-' + order_id[1] + '" data-izimodal-transitionout="bounceOutDown" class="button conversation-btn">x</button>');
			},
		});
	});

	$('a[class*="convo"]').on('click', function(event) {
		event.preventDefault();

		var modal_id = $(this).attr('class').split(' ').pop();
		modal_id = '#modal-' + modal_id;

		$(modal_id).iziModal('open');
	});


	$(document).on("click", ".naccs .menu div", function() {
		var numberIndex = $(this).index();

		console.log('sdg');

		if (!$(this).is("active")) {
			$(".naccs .menu div").removeClass("active");
			$(".naccs ul li").removeClass("active");

			$(this).addClass("active");
			$(".naccs ul").find("li:eq(" + numberIndex + ")").addClass("active");

			var listItemHeight = $(".naccs ul")
				.find("li:eq(" + numberIndex + ")")
				.innerHeight();
			$(".naccs ul").height(listItemHeight + "px");
		}
	});

});
