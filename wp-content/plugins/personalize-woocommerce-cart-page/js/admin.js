function resetTabs(){
    jQuery("#nm-content > div").hide(); //Hide all nm-content
    jQuery("#nm-tabs a").attr("id",""); //Reset id's      
}

var myUrl = window.location.href; //get URL
var myUrlTab = myUrl.substring(myUrl.indexOf("#")); // For mywebsite.com/tabs.html#tab2, myUrlTab = #tab2     
var myUrlTabName = myUrlTab.substring(0,4); // For the above example, myUrlTabName = #tab

jQuery(document).ready(function() {
    jQuery("#nm-content > div").hide(); // Initially hide all nm-content
    jQuery("#nm-tabs li:first a").attr("id","current"); // Activate first tab
    jQuery("#nm-content > div:first").fadeIn(); // Show first tab nm-content
    
    jQuery("#nm-tabs a").on("click",function(e) {
        e.preventDefault();
        if (jQuery(this).attr("id") == "current"){ //detection for current tab
         return       
        }
        else{             
        resetTabs();
        jQuery(this).attr("id","current"); // Activate this
        jQuery(jQuery(this).attr('name')).fadeIn(); // Show nm-content for current tab
        }
    }); 

    jQuery("#nm-tabs-mobile").on("change",function(e) {
    	e.preventDefault();
    	jQuery(jQuery(this).val()).siblings().fadeOut();
        jQuery(jQuery(this).val()).fadeIn(); // Show nm-content for current tab
    });

    for (i = 1; i <= jQuery("#nm-tabs li").length; i++) {
      if (myUrlTab == myUrlTabName + i) {
          resetTabs();
          jQuery("a[name='"+myUrlTab+"']").attr("id","current"); // Activate url tab
          jQuery(myUrlTab).fadeIn(); // Show url tab nm-content        
      }
    }
});

// jQuery(function(){

// 	jQuery('#filemanager-tabs').easytabs();
// });

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
		data.action = 'nm_todolist_save_settings';

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

function update_options(options) {

	var opt = jQuery.parseJSON(options);


	jQuery(".woostore-settigns-saving").html('<img src="'+nm_woostore_vars.doing+'" />');
	/*
	 * extractElementData defined in nm-globals.js
	 */
	var data = extractElementData(opt);

	if (data.bug) {
		// jQuery("#reply_err").html('Red are required');
		alert('bug here');
	} else {

		console.log('dfd');

		/*
		 * [1]
		 */
		data.action = 'nm_woostore_save_settings';

		console.log(data);

		jQuery.post(ajaxurl, data, function(resp) {

			jQuery(".woostore-settigns-saving").html(resp.message);

			if (resp.status == 'success') {
				window.location.reload(true);
			}
			// window.location.reload(true);

		});
	}

}