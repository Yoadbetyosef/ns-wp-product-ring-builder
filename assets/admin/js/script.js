jQuery(document).ready(function($){
  
  if($('.bbwp_engine_items input.bbwp_flipswitch').length >= 1){
    $('.bbwp_engine_items input.bbwp_flipswitch').on('change', function (e) {

      var object = $(this);
      var checked = $(this).is(':checked');
      var name = $(this).attr('name');
      
      $.ajax({
          type: "POST",
          url: bbwp_engine.ajax_url,
          data: {action: "bbwp_engine_update_module", module: name, status: checked, bbwp_engine: bbwp_engine.bbwp_engine},
          success: function (response) {
            if(response.success == true){
              if(checked)
                object.parents('li').addClass('active');
              else
                object.parents('li').removeClass('active');
            }
          }
      });
    });
  }


  // postboxes.save_state = function(){
  //   return;
  // };
  // postboxes.save_order = function(){
  //     return;
  // };
  // postboxes.add_postbox_toggles();

});