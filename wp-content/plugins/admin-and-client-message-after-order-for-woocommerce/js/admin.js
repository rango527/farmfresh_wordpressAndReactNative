jQuery(function($){

	$('#tab-container').tabs();
	
});

function updateOptions(options){
	
	var opt = jQuery.parseJSON(options);
	

	/*
	 * getting action from object
	 */
	
	
	/*
	 * extractElementData
	 * defined in nm-globals.js
	 */
	 
	var data = extractElementData(opt);
	
	
	if (data.bug) {
		//jQuery("#reply_err").html('Red are required');
		alert('bug here');
	} else {

		/*
		 * [1]
		 * TODO: change action name below with prefix plugin shortname_action_name
		 */
		data.action = 'wooconvo_save_settings';

		jQuery.post(ajaxurl, data, function(resp) {

			//jQuery("#reply_err").html(resp);
			alert(resp);
			window.location.reload(true);

		});
	}
	
	/*jQuery.each(res, function(i, item){
		
		alert(i);
		
	});*/
}