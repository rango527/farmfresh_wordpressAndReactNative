/*
 * NOTE: all actions are prefixed by plugin shortnam_action_name
 */

jQuery(function(){
	

});



function get_option(key){
	
	/*
	 * TODO: change plugin shortname
	 */
	var keyprefix = 'nm_wooconvo';
	
	key = keyprefix + key;
	
	var req_option = '';
	
	jQuery.each(nm_wooconvo_vars.settings, function(k, option){
		
		//console.log(k);
		
		if (k == key)
			req_option = option;		
	});
	
	//console.log(req_option);
	return req_option;
	
}

/* new message validation */
function validateCompose(){
		error=0;
		if(jQuery("#orderno").val()==""){
			 jQuery("#orderno").css("border-color", "red");
			jQuery("#orderno_err").show().fadeOut(10000);
			error=1;
		} 

		if(jQuery("#customeremail").val()==""){
			jQuery("#subject").css("border-color", "red");
			jQuery("#customeremail_err").show().fadeOut(11000);
			error=1;
		} 
		
		if(jQuery("#message").val()==""){
			jQuery("#message").css("border-color", "red");
			jQuery("#message_err").show().fadeOut(12000);
			error=1;
		} 
		
		if(error != 1) 
			nm_sendNewConvo();			
		
		return false;
		
}


function nm_sendNewConvo(){
	var orderno = jQuery("#orderno").val();
	var customeremail = jQuery("#customeremail").val();
	var customermessage = jQuery("#message").val();
	//alert(customeremail);
	var data = {action: 'nm_wooconvo_sendWooConvo', order_no: orderno, customer_email: customeremail, customer_message: customermessage};
	jQuery.post(nm_wooconvo_vars.ajaxurl, data, function(resp){
		console.log(resp);
	});
}

/* reply validation */
function validateReply(convoid){
		error=0;
		
		if(jQuery("#nm-reply-" + convoid).val()==""){
			jQuery("#nm-reply-" + convoid).css("border-color", "red");
			jQuery("#reply_err-" + convoid).show().fadeOut(12000);
			error=1;
		} 
		
		if(error != 1) 
			nm_replyWooConvo(convoid);			
		
		return false;		
}

function nm_replyWooConvo(convoid){
	//var orderno = jQuery("#orderno").val();
	//var replycid = jQuery("#reply-c-id").val();
	var replycid = convoid;
	var replymessage = jQuery("#nm-reply-" + convoid).val();
	//alert(replycid);
	//alert(replymessage);
	var data = {action: 'nm_wooconvo_replyWooConvo', reply_c_id: replycid, reply_message: replymessage};
	jQuery.post(nm_wooconvo_vars.ajaxurl, data, function(resp){
		console.log(resp);
		jQuery("#convo-history-panel-" + convoid).hide();
	});
}

/* reply validation */
function validateLogin(){
		error=0;
		var useremail = jQuery("#loginemail").val();
		if(jQuery("#loginemail").val()==""){
			jQuery("#loginemail").css("border-color", "red");
			jQuery("#loginemail_err").show().fadeOut(12000);
			error=1;
		} 
		
		if(error != 1) 
		{
			var data = {action: 'nm_wooconvo_login_validate', user_email: useremail};
			jQuery.post(nm_wooconvo_vars.ajaxurl, data, function(resp){
			if(resp != 0)
			{
			//window.location.reload;
				nm_showInbox();			
				//alert(resp);
			}	
			else
			{
				alert('Email not found...!');
			}
			});
		}
		return false;		
}

function nm_showInbox(){

	var useremail = jQuery("#loginemail").val();
	//alert(useremail);
	//alert(replymessage);
	var data = {action: 'nm_wooconvo_get_user_convos', user_email: useremail};
	jQuery.post(nm_wooconvo_vars.ajaxurl, data, function(resp){
		console.log(resp);
		window.location = '/wooconvo/?user_email='+useremail;
		jQuery("#wooconvo-inbox").show();
	});
}

/*
 * This is loading message history
 */
function loadConvoHistory(c_id) {
	// alert(url_convo_detail);
	// hiding other stuff
	jQuery("#inbox-panel, #compose-panel").hide();

	// but showing me
	jQuery("#convo-history-panel-" + c_id).show();

	// setting title of pop up
	var t = jQuery("#convo-" + c_id).find("li.title").html();

	// binding convo id value to reply form hidden id field
	jQuery("#reply-c-id").val(c_id);

	//jQuery("#convo-detail-container").load(url_convo_detail, {
	//	dirpath : dir_path,
	//	cid : c_id
	//});
	jQuery("#history-heading").html(t);

	// mark as read
	//markAsRead(c_id);
}

/* mark convo as read */
function markAsRead(cid) {
	var data = {
		action : 'nm_wooconvo_convo_action',
		convo_token : nm_wooconvo_vars.convo_token,
		convo_id : cid
	};

	// since 2.8 ajaxurl is always defined in the admin header and points to
	// admin-ajax.php
	jQuery.post(nm_wooconvo_vars.ajaxurl, data, function(response) {
		// alert('Got this from the server: ' + response);
	});

}

/* hiding convo */
function loadConvoCurrentPage(cid) 
{
	jQuery("#convo-history-panel-" + cid).hide();
}

function doShowCompose()
{
	jQuery("#compose-convo").toggle();
}