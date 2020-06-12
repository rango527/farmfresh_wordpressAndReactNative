"use strict";
jQuery(function($){
    
    $(document).ready(function(e){
        
        $(".wooh_refresh_loader").hide();
        $('#wooh-hole-code').show();
    });
    
    
    /*=== Settings saving ajax ====*/
    $('#wooh_store_settings_save').on('submit',function(e){
        e.preventDefault();
        
        var btn_val = $('input[type="submit"]').val('Please Wait...');
        btn_val.attr('disabled','disabled');

        var data = $(this).serialize();
        
        $.post(wooh_vars.ajaxurl, data, function(resp){
            
            swal({
              title: 'All options are updated',
              timer: 2000,
              showCancelButton: false,
              showConfirmButton: false
            });

            if(resp.status == 'success'){
                window.location.reload();
            }
        });
    });


    /*=== Product tab added ====*/
    var row_counter = 1;
      $('.clone-me').on('click', function() {
        var new_row = $(this).parents('.meta-row').clone(true).removeClass('first-element').appendTo('#container');
        row_counter++;
      });

      //removing
    $('.remove-me').on('click', function() {

        if($(this).parents('.meta-row').hasClass('first-element')) {
            $.blockUI({ 
                theme:     true, 
                title:    '',
                message:  'You can not remove this', 
                timeout:   3000 
            });
        } else {
            $(this).parents(".meta-row").remove();
        }
    });

    $("#container div:not(:first)").removeClass('first-element');


    $('#save-all').click(function(e) {
        e.preventDefault();
    
        $("#saving-tabs").html('Please wait ...');
        $("#save-all").html('Please Wait ...');
        $("#save-all").prop('disabled', true);
            var tabs = Array();

        $('#container div').each(function() {
            var one_tab = {
            title: $(this).find('input:text').val(),
            default_desc: $(this).find('#default_desc').val(),
          }

          tabs.push(one_tab);
          
        });

        var data = {action: 'wooh_save_tabs', tabs: tabs};
    

        $.post(wooh_vars.ajaxurl, data, function(resp){
            
            $("#saving-tabs").html('Saved');
            $("#save-all").html('Save tabs');
            $("#save-all").prop('disabled', false);
        });

    });

    $( "#container" ).sortable({
        placeholder: "ui-state-highlight"
    });

    $('#reset-all').click(function(e) {
        e.preventDefault();
        swal({
          title: "Are you sure?",
          text: "t will remove all above tabs",
          type: "warning",
          showCancelButton: true,
          confirmButtonClass: "btn-danger",
          confirmButtonText: "Remove",
          closeOnConfirm: false
        },
        function(){
          swal("Removed!", "Your tabs has been removed.", "success");
          var tabs = '';
          var data = {action: 'wooh_save_tabs', tabs: tabs};

          $.post(wooh_vars.ajaxurl, data, function(resp){
            
            location.reload();
          });      
          
        });
        // if (confirm('Are you sure? It will remove all above tabs')) {
          
        // };
    });
    
});